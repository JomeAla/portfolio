<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\Lead;
use App\Models\EmailSequence;
use App\Models\EmailQueue;
use App\Traits\HandlesMailConfig;
use App\Services\AffiliateCommissionService;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use HandlesMailConfig;

    public function checkout(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $paystackKey = Setting::get('paystack_public_key') ?? 'pk_live_xxxxxxxxxxxx';
        return view('front.order.checkout', compact('product', 'paystackKey'));
    }

    private function getLeadIdFromEmail($email)
    {
        if (!$email) return null;
        $lead = Lead::where('email', $email)->first();
        return $lead?->id;
    }

    public function validateCoupon(Request $request)
    {
        $code = strtoupper($request->get('code'));
        $productId = $request->get('product_id');

        if (!$code) {
            return response()->json(['valid' => false, 'message' => 'Please enter a coupon code']);
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'Invalid coupon code']);
        }

        if (!$coupon->isValid()) {
            return response()->json(['valid' => false, 'message' => 'Coupon has expired or reached usage limit']);
        }

        // Check if coupon applies to this product
        if ($coupon->product_id && $coupon->product_id != $productId) {
            return response()->json(['valid' => false, 'message' => 'This coupon is not valid for this product']);
        }

        $product = Product::find($productId);
        $originalPrice = $product->sale_price ?? $product->price;
        $discount = $coupon->calculateDiscount($originalPrice);
        $finalAmount = $originalPrice - $discount;

        return response()->json([
            'valid' => true,
            'discount' => $discount,
            'originalAmount' => $originalPrice,
            'finalAmount' => $finalAmount,
            'message' => 'Coupon applied successfully!'
        ]);
    }

    public function ecomInitPayment(Request $request)
    {
        Log::info('ecomInitPayment called', ['slug' => $request->product_slug, 'email' => $request->email]);
        try {
            $email = filter_var($request->email ?? '', FILTER_VALIDATE_EMAIL);
            $name = trim($request->name ?? '');
            $phone = trim($request->phone ?? '');
            $couponCode = trim($request->coupon_code ?? '');
            $productSlug = trim($request->product_slug ?? 'ecommerce-starter-kit');

            if (!$email || !$name || !$phone) {
                return response()->json(['error' => 'Please fill in all required fields.'], 422);
            }

            $product = Product::where('slug', $productSlug)->first();
            Log::info('ecomInitPayment product query', ['slug' => $productSlug, 'found' => $product ? $product->id : null]);
            if (!$product) {
                $product = Product::where('slug', str_replace('-', '_', $productSlug))->first();
                Log::info('ecomInitPayment fallback 1', ['slug' => str_replace('-', '_', $productSlug), 'found' => $product ? $product->id : null]);
            }
            if (!$product) {
                $altSlug = preg_replace('/^ecommerce-/', 'e-commerce-', $productSlug);
                if ($altSlug !== $productSlug) {
                    $product = Product::where('slug', $altSlug)->first();
                    Log::info('ecomInitPayment fallback 2', ['slug' => $altSlug, 'found' => $product ? $product->id : null]);
                }
            }
            if (!$product) {
                Log::info('ecomInitPayment ALL products', ['products' => Product::select(['id','title','slug'])->limit(5)->get()->toArray()]);
                return response()->json(['error' => 'Product not found.', 'slug_tried' => $productSlug], 404);
            }

            $amount = (float)($product->sale_price ?? $product->price);
            $discount = 0;

            if ($couponCode) {
                $coupon = Coupon::where('code', strtoupper($couponCode))->first();
                if ($coupon && $coupon->isValid()) {
                    if (!$coupon->min_order_amount || $amount >= (float)$coupon->min_order_amount) {
                        $discount = $coupon->calculateDiscount($amount);
                    }
                }
            }

            $finalAmount = max(0, $amount - $discount);

            $lead = Lead::firstOrCreate(['email' => $email], [
                'name' => $name,
                'phone' => $phone,
                'source' => 'ecom_sales_page',
            ]);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'product_id' => $product->id,
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $phone,
                'amount' => $amount,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'coupon_code' => $couponCode ?: null,
                'payment_status' => 'pending',
                'download_token' => Order::generateDownloadToken(),
                'download_expires_at' => now()->addHours(24),
                'cart_started_at' => now(),
                'checkout_started_at' => now(),
                'lead_id' => $lead->id ?? null,
                'campaign_id' => session('campaign_id'),
            ]);

            if ($order->coupon_code) {
                Coupon::where('code', $order->coupon_code)->increment('used_count');
            }

            $paystackPublicKey = Setting::get('paystack_public_key') ?? Setting::get('paystack_public');

            return response()->json([
                'order_id' => $order->id,
                'reference' => $order->order_number,
                'paystack_public_key' => $paystackPublicKey,
                'amount' => (int) ($finalAmount * 100),
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function initiatePayment(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string',
                'coupon_code' => 'nullable|string',
            ]);

            $product = Product::findOrFail($request->product_id);
            $amount = $product->sale_price ?? $product->price;
            $discount = 0;

            if ($request->coupon_code) {
                $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
                if ($coupon && $coupon->isValid()) {
                    $discount = $coupon->calculateDiscount($amount);
                }
            }

            $finalAmount = $amount - $discount;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'product_id' => $product->id,
                'customer_name' => $request->name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                'amount' => $amount,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'coupon_code' => $request->coupon_code,
                'payment_status' => 'pending',
                'download_token' => Order::generateDownloadToken(),
                'download_expires_at' => now()->addHours(24),
                'cart_started_at' => now(),
                'checkout_started_at' => now(),
                'lead_id' => $this->getLeadIdFromEmail($request->email),
                'campaign_id' => session('campaign_id'),
            ]);

            if ($order->lead_id) {
                session(['last_order_id' => $order->id]);
            }

            $this->enrollInCartAbandonmentSequence($order);

            $paystackPublicKey = Setting::get('paystack_public_key') ?? Setting::get('paystack_public');
            
            return response()->json([
                'order' => $order,
                'paystack_public_key' => $paystackPublicKey,
                'amount' => (int) ($finalAmount * 100),
                'email' => $request->email,
                'reference' => $order->order_number,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

public function success(Request $request)
    {
        $order = Order::where('order_number', $request->reference)->first();
        
        if ($order && $order->payment_status === 'pending') {
            $order->update([
                'payment_status' => 'success',
                'payment_reference' => $request->trxref,
            ]);

            $coupon = Coupon::where('code', $order->coupon_code)->first();
            if ($coupon) {
                $coupon->increment('used_count');
            }

            // Cancel cart abandonment sequence when purchase completes
            $this->cancelCartAbandonmentSequence($order);

            $this->sendDownloadEmail($order);
            $this->enrollInPostPurchaseSequence($order);
            
            // Create notification for customer
            $this->createOrderNotification($order);
            
            // Process affiliate commission if referral code was used
            try {
                if ($order->coupon_code) {
                    $pdo = DB::connection()->getPdo();
                    $refCheck = $pdo->prepare("SELECT id, affiliate_id, credit_earned FROM referral_uses WHERE order_id = ?");
                    $refCheck->execute([$order->id]);
                    $referralUse = $refCheck->fetch();
                    
                    if (!$referralUse) {
                        $affiliateCheck = $pdo->prepare("SELECT a.id FROM affiliates a WHERE a.referral_code = ?");
                        $affiliateCheck->execute([$order->coupon_code]);
                        $affiliate = $affiliateCheck->fetch();
                        
                        if ($affiliate) {
                            app(AffiliateCommissionService::class)->processReferral($order->id, $affiliate['id']);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to process affiliate commission: ' . $e->getMessage());
            }
            
            // Check and award achievements
            try {
                app(AchievementService::class)->onOrderSuccess($order->customer_email, $order->id);
            } catch (\Exception $e) {
                Log::error('Failed to check achievements: ' . $e->getMessage());
            }
        }

        return view('front.order.success', compact('order'));
    }
    
    protected function createOrderNotification($order)
    {
        try {
            $pdo = DB::connection()->getPdo();
            $productTitle = $order->product->title ?? 'Your purchase';
            
            $message = "Your order #{$order->order_number} for {$productTitle} has been confirmed. You can now download your files.";
            $link = '/customer/orders';
            
            $stmt = $pdo->prepare("INSERT INTO customer_notifications (customer_email, type, title, message, link, created_at) VALUES (?, 'order', ?, ?, ?, NOW())");
            $stmt->execute([$order->customer_email, 'Order Confirmed', $message, $link]);
            
            // Also create achievement notification for first order
            $checkFirst = $pdo->prepare("SELECT COUNT(*) as cnt FROM orders WHERE customer_email = ? AND payment_status = 'success'");
            $checkFirst->execute([$order->customer_email]);
            if ($checkFirst->fetch()['cnt'] == 1) {
                $stmt = $pdo->prepare("INSERT INTO customer_notifications (customer_email, type, title, message, link, created_at) VALUES (?, 'achievement', ?, ?, ?, NOW())");
                $stmt->execute([$order->customer_email, 'First Purchase!', 'Congratulations! You made your first purchase. Check your achievements for rewards.', '/customer/achievements']);
            }
            
            // Check for referral bonus
            if (!empty($order->coupon_code)) {
                $checkRef = $pdo->prepare("SELECT a.* FROM affiliates a JOIN customer_referrals cr ON a.referral_code = cr.referral_code WHERE cr.referral_code = ?");
                $checkRef->execute([$order->coupon_code]);
                $referrer = $checkRef->fetch();
                if ($referrer) {
                    // Notify referrer
                    $stmt = $pdo->prepare("INSERT INTO customer_notifications (customer_email, type, title, message, link, created_at) VALUES (?, 'referral', ?, ?, ?, NOW())");
                    $stmt->execute([$referrer['email'], 'Referral Bonus!', 'Someone used your referral code! Check your affiliate dashboard for details.', '/customer/affiliate']);
                }
            }
            
            // Check if ordered product is a course - create enrollment
            $product = $order->product ?? null;
            if ($product && !empty($product->id)) {
                // Check if this is a course
                $courseCheck = $pdo->prepare("SELECT id FROM courses WHERE id = ? OR title LIKE ?");
                $courseCheck->execute([$product->id, '%' . $product->title . '%']);
                $course = $courseCheck->fetch();
                
                if ($course) {
                    // Create course enrollment
                    $enrollCheck = $pdo->prepare("SELECT id FROM course_enrollments WHERE customer_email = ? AND course_id = ?");
                    $enrollCheck->execute([$order->customer_email, $course['id']]);
                    if (!$enrollCheck->fetch()) {
                        $enrollStmt = $pdo->prepare("INSERT INTO course_enrollments (customer_email, course_id, order_id, enrolled_at) VALUES (?, ?, ?, NOW())");
                        $enrollStmt->execute([$order->customer_email, $course['id'], $order->id]);
                        
                        // Notify about course enrollment
                        $stmt = $pdo->prepare("INSERT INTO customer_notifications (customer_email, type, title, message, link, created_at) VALUES (?, 'course', ?, ?, ?, NOW())");
                        $stmt->execute([$order->customer_email, 'Course Enrolled!', 'You have been enrolled in ' . $product->title . '. Start learning now!', '/customer/courses']);
                    }
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage());
            return false;
        }
    }

    public function download(Request $request, $token)
    {
        $order = Order::where('download_token', $token)->firstOrFail();

        if (!$order->canDownload()) {
            abort(403, 'Download link expired or invalid.');
        }

        try {
            DB::table('customer_notifications')->insert([
                'customer_email' => $order->customer_email,
                'type' => 'order',
                'title' => 'File Downloaded',
                'message' => 'You downloaded ' . ($order->product->title ?? 'a file') . '. Thank you!',
                'link' => '/customer/downloads',
                'is_read' => false,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {}

        return response()->download(
            storage_path('app/public/' . $order->product->file_path),
            $order->product->title . '.' . pathinfo($order->product->file_path, PATHINFO_EXTENSION)
        );
    }

    public function resendEmail(Request $request)
    {
        $request->validate([
            'order_number' => 'required|exists:orders,order_number',
        ]);

        $order = Order::where('order_number', $request->order_number)->firstOrFail();

        if ($order->payment_status !== 'success') {
            return back()->with('error', 'Order payment not completed.');
        }

        $this->sendDownloadEmail($order);

        return back()->with('success', 'Download link resent to email!');
    }

    protected function sendDownloadEmail($order)
    {
        try {
            // Apply dynamic mail settings
            $this->applyMailConfig();

            $downloadUrl = route('order.download', $order->download_token);

            Mail::send('emails.order_purchase', [
                'order' => $order,
                'downloadUrl' => $downloadUrl
            ], function ($message) use ($order) {
                $message->to($order->customer_email, $order->customer_name)
                        ->subject('Your Purchase - ' . $order->product->title . ' - Download Link');
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send purchase email: ' . $e->getMessage());
            return false;
        }
    }

    protected function enrollInPostPurchaseSequence($order)
    {
        try {
            $productTitle = $order->product->title ?? '';
            
            $sequence = EmailSequence::where('name', 'LIKE', '%Post Purchase%')
                ->orWhere('name', 'LIKE', '%' . $productTitle . '%')
                ->orWhere('name', 'LIKE', '%Email Templates Pack%')
                ->orWhere('name', 'LIKE', '%Premium Bundle%')
                ->orWhere('name', 'LIKE', '%Done-For-You%')
                ->orWhere('name', 'LIKE', '%WhatsApp%')
                ->orWhere('name', 'LIKE', '%Course Creator%')
                ->orWhere('name', 'LIKE', '%Local Business%')
                ->orWhere('name', 'LIKE', '%SaaS Starter%')
                ->orWhere('name', 'LIKE', '%Website Audit%')
                ->where('is_active', true)
                ->first();
            
            if (!$sequence) {
                Log::info('No post-purchase sequence found for order: ' . $order->order_number);
                return false;
            }
            
            $lead = Lead::firstOrCreate(
                ['email' => $order->customer_email],
                [
                    'name' => $order->customer_name,
                    'source' => 'product_purchase',
                    'score' => 10,
                ]
            );
            
            $lead->tags()->firstOrCreate(['name' => 'Customer']);
            $lead->tags()->firstOrCreate(['name' => 'Purchased: ' . $productTitle]);
            
            $steps = $sequence->steps()->orderBy('step_order')->get();
            
            foreach ($steps as $step) {
                EmailQueue::create([
                    'lead_id' => $lead->id,
                    'sequence_step_id' => $step->id,
                    'scheduled_send_time' => now()->addDays($step->delay_days),
                    'status' => 'pending',
                ]);
            }
            
            Log::info('Enrolled in post-purchase sequence: ' . $sequence->name . ' for lead: ' . $lead->email);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to enroll in post-purchase sequence: ' . $e->getMessage());
            return false;
        }
    }

    protected function enrollInCartAbandonmentSequence($order)
    {
        try {
            $sequence = EmailSequence::where('trigger_type', 'cart_abandonment')
                ->where('is_active', true)
                ->first();
            
            if (!$sequence) {
                Log::info('No cart abandonment sequence found');
                return false;
            }
            
            $lead = Lead::firstOrCreate(
                ['email' => $order->customer_email],
                [
                    'name' => $order->customer_name,
                    'source' => 'cart_abandonment',
                ]
            );
            
            $lead->tags()->firstOrCreate(['name' => 'Cart Abandonment']);
            
            $steps = $sequence->steps()->orderBy('step_order')->get();
            
            foreach ($steps as $step) {
                EmailQueue::create([
                    'lead_id' => $lead->id,
                    'sequence_step_id' => $step->id,
                    'scheduled_send_time' => now()->addHours($step->delay_days * 24 + ($step->step_order == 1 ? 1 : 0)),
                    'status' => 'pending',
                ]);
            }
            
            Log::info('Enrolled in cart abandonment sequence for order: ' . $order->order_number);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to enroll in cart abandonment sequence: ' . $e->getMessage());
            return false;
        }
    }

    protected function cancelCartAbandonmentSequence($order)
    {
        try {
            $lead = Lead::where('email', $order->customer_email)->first();
            
            if (!$lead) {
                return false;
            }
            
            EmailQueue::where('lead_id', $lead->id)
                ->where('status', 'pending')
                ->delete();
            
            Log::info('Cancelled cart abandonment sequence for order: ' . $order->order_number);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to cancel cart abandonment sequence: ' . $e->getMessage());
            return false;
        }
    }
}
