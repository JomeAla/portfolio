<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'coupon_code' => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);
        $amount = $product->getCurrentPrice();
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
        ]);

        $paystackPublicKey = Setting::get('paystack_public_key');
        
        return response()->json([
            'order' => $order,
            'paystack_public_key' => $paystackPublicKey,
            'amount' => $finalAmount * 100,
            'email' => $request->email,
            'reference' => $order->order_number,
        ]);
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

            $this->sendDownloadEmail($order);
        }

        return view('front.order.success', compact('order'));
    }

    public function download(Request $request, $token)
    {
        $order = Order::where('download_token', $token)->firstOrFail();

        if (!$order->canDownload()) {
            abort(403, 'Download link expired or invalid.');
        }

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
            $fromAddress = Setting::get('mail_from_address', 'support@joala.com.ng');
            $fromName = Setting::get('mail_from_name', 'JoAla Support');

            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);

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
}
