<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AchievementService;
use App\Services\AffiliateCommissionService;
use App\Services\PaystackSubscriptionService;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;

class CustomerController extends Controller
{
    private function getPdo()
    {
        $pdo = db_pdo();
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $pdo;
    }

    private function getCustomerFromToken()
    {
        // Use Laravel session instead of cookie
        if (!session()->has('customer_id')) {
            return null;
        }
        
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM customer_accounts WHERE id = ?");
        $stmt->execute([session('customer_id')]);
        return $stmt->fetch();
    }

    private function requireCustomer()
    {
        $customer = $this->getCustomerFromToken();
        if (!$customer) return redirect('/customer/login');
        return $customer;
    }

    public function showLogin() {
        if (session()->has('customer_id')) {
            return redirect('/customer/dashboard');
        }
        return view('front.customer.login');
    }
    
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        
        // Debug - write to log file
        \Illuminate\Support\Facades\Log::info("Login attempt for: " . $email);
        
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM customer_accounts WHERE email = ?");
            $stmt->execute([$email]);
            $customer = $stmt->fetch();
            
            \Illuminate\Support\Facades\Log::info("Customer found: " . ($customer ? 'yes' : 'no'));
            
            if ($customer && password_verify($password, $customer['password'])) {
                \Illuminate\Support\Facades\Log::info("Password verified, creating session");
                
                // Use Laravel session instead of custom cookie
                session(['customer_id' => $customer['id'], 'customer_email' => $customer['email']]);
                
                return new \Illuminate\Http\RedirectResponse('https://joala.com.ng/customer/my-courses');
            }
            \Illuminate\Support\Facades\Log::info("Login failed - invalid credentials");
                $response = new \Illuminate\Http\RedirectResponse('https://joala.com.ng/customer/login');
                $response->with('error', 'Invalid email or password');
                return $response;
        } catch (\Exception $e) { 
            \Illuminate\Support\Facades\Log::error("Login error: " . $e->getMessage());
            $response = new \Illuminate\Http\RedirectResponse('https://joala.com.ng/customer/login');
            $response->with('error', 'Error: ' . $e->getMessage());
            return $response;
        }
    }

    public function showRegister() { return view('front.customer.register'); }

    public function register(Request $request)
    {
        $email = $request->input('email');
        $password = password_hash($request->input('password') ?: 'password123', PASSWORD_DEFAULT);
        $name = $request->input('name') ?: 'Customer';
        try {
            $pdo = $this->getPdo();
            $check = $pdo->prepare("SELECT id FROM customer_accounts WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) return back()->with('error', 'Email already registered');
            $ins = $pdo->prepare("INSERT INTO customer_accounts (email, password, name, created_at) VALUES (?, ?, ?, NOW())");
            $ins->execute([$email, $password, $name]);
            $customerId = $pdo->lastInsertId();
            
            // Set session for customer portal auth
            session(['customer_id' => $customerId, 'customer_email' => $email]);
            
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
            $ins = $pdo->prepare("INSERT INTO customer_sessions (customer_id, token, expires_at) VALUES (?, ?, ?)");
            $ins->execute([$customerId, $token, $expires]);
            setcookie('customer_token', $token, time() + (7 * 24 * 60 * 60), '/');
            
            // Send welcome notification
            CustomerController::createNotification($email, 'welcome', 'Welcome to Joala!', 'Thank you for registering. Explore our courses and subscription plans!', '/customer/subscriptions');
            
            return redirect('/customer/subscriptions');
        } catch (\Exception $e) { return back()->with('error', 'Error: ' . $e->getMessage()); }
    }

    public function logout()
    {
        $token = $_COOKIE['customer_token'] ?? '';
        if ($token) {
            try {
                $pdo = $this->getPdo();
                $pdo->exec("DELETE FROM customer_sessions WHERE token = '$token'");
            } catch (\Exception $e) {}
            setcookie('customer_token', '', time() - 3600, '/');
        }
        return redirect('/customer/login');
    }

    public function dashboard()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            $statsStmt = $pdo->prepare("SELECT COUNT(*) as total_orders, COALESCE(SUM(final_amount), 0) as total_spent FROM orders WHERE customer_email = ? AND payment_status = 'paid'");
            $statsStmt->execute([$customer['email']]);
            $stats = $statsStmt->fetch();
            
            $recentStmt = $pdo->prepare("SELECT * FROM orders WHERE customer_email = ? ORDER BY created_at DESC LIMIT 5");
            $recentStmt->execute([$customer['email']]);
            $recentOrders = $recentStmt->fetchAll();
            
            $downloadStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM orders WHERE customer_email = ? AND payment_status = 'paid'");
            $downloadStmt->execute([$customer['email']]);
            $downloadCount = $downloadStmt->fetch()['cnt'] ?? 0;
            
            $referralStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM customer_referrals WHERE customer_email = ?");
            $referralStmt->execute([$customer['email']]);
            $referralCount = $referralStmt->fetch()['cnt'] ?? 0;
            
            return view('front.customer.dashboard', compact('customer', 'stats', 'recentOrders', 'downloadCount', 'referralCount'));
        } catch (\Exception $e) { return view('front.customer.dashboard', compact('customer'))->with('error', $e->getMessage()); }
    }

    public function orders()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_email = ? ORDER BY created_at DESC");
            $stmt->execute([$customer['email']]);
            $orders = $stmt->fetchAll();
            return view('front.customer.orders', compact('customer', 'orders'));
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function downloads()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare("SELECT o.*, p.title as product_name, p.file_path FROM orders o LEFT JOIN products p ON o.product_id = p.id WHERE o.customer_email = ? AND o.payment_status = 'paid' ORDER BY o.created_at DESC");
            $stmt->execute([$customer['email']]);
            $downloads = $stmt->fetchAll();
            return view('front.customer.downloads', compact('customer', 'downloads'));
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function settings() { 
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        return view('front.customer.settings', ['customer' => $customer]); 
    }

    public function updateSettings(Request $request)
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare("UPDATE customer_accounts SET name = ? WHERE id = ?");
            $stmt->execute([$request->input('name'), $customer['id']]);
            return back()->with('success', 'Profile updated!');
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function subscriptions()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            
            $plans = SubscriptionPlan::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('price')
                ->get();
            
            foreach ($plans as $plan) {
                if (!$plan->paystack_plan_code) {
                    try {
                        app(PaystackSubscriptionService::class)->createPlan($plan);
                        $plan->refresh();
                    } catch (\Exception $e) {}
                }
            }
            
            $plans = $plans->fresh();
            
            $activeSubscription = Subscription::getActiveForEmail($customer['email']);
            
            $subscriptionHistory = Subscription::where('customer_email', $customer['email'])
                ->with('plan')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return view('front.customer.subscriptions', compact('customer', 'plans', 'activeSubscription', 'subscriptionHistory'));
        } catch (\Exception $e) { 
            $plans = SubscriptionPlan::where('is_active', true)->orderBy('price')->get();
            $activeSubscription = null;
            $subscriptionHistory = [];
            return view('front.customer.subscriptions', compact('customer', 'plans', 'activeSubscription', 'subscriptionHistory'));
        }
    }

    public function subscribeToPlan(Request $request, $planId)
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        
        $plan = SubscriptionPlan::findOrFail($planId);
        $subscriptionService = app(PaystackSubscriptionService::class);
        
        $activeSubscription = Subscription::getActiveForEmail($customer['email']);
        if ($activeSubscription) {
            return back()->with('error', 'You already have an active subscription.');
        }
        
        $authorizeUrl = $subscriptionService->getAuthorizationUrl(
            $customer['email'],
            $plan,
            $customer['name'] ?? null
        );
        
        if ($authorizeUrl) {
            return redirect($authorizeUrl);
        }
        
        return back()->with('error', 'Could not initialize subscription. Please try again.');
    }

    public function subscriptionCallback(Request $request)
    {
        $reference = $request->get('reference');
        $subscriptionService = app(PaystackSubscriptionService::class);
        
        try {
            $secretKey = \App\Models\Setting::get('paystack_secret_key');
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
            ])->get('https://api.paystack.co/transaction/verify/' . $reference);
            
            if ($response->successful() && $response->json('data.status') === 'success') {
                $data = $response->json('data');
                $email = $data['customer']['email'] ?? '';
                $planCode = $data['plan'] ?? null;
                
                if ($email) {
                    return redirect('/customer/subscriptions')->with('success', 'Subscription activated successfully!');
                }
            }
            
            return redirect('/customer/subscriptions')->with('error', 'Subscription verification failed.');
        } catch (\Exception $e) {
            return redirect('/customer/subscriptions')->with('error', 'Error processing subscription.');
        }
    }

    public function cancelSubscription()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        
        $activeSubscription = Subscription::getActiveForEmail($customer['email']);
        
        if (!$activeSubscription) {
            return back()->with('error', 'No active subscription found.');
        }
        
        $subscriptionService = app(PaystackSubscriptionService::class);
        
        if ($activeSubscription->paystack_subscription_code) {
            $cancelled = $subscriptionService->cancelSubscription($activeSubscription->paystack_subscription_code);
            
            if (!$cancelled) {
                return back()->with('error', 'Could not cancel subscription with payment provider. Please try again.');
            }
        } else {
            $activeSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }
        
        return redirect('/customer/subscriptions')->with('success', 'Subscription cancelled. You will retain access until the end of your billing period.');
    }

    public function referrals()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            
            $stmt = $pdo->prepare("SELECT * FROM customer_referrals WHERE customer_email = ?");
            $stmt->execute([$customer['email']]);
            $referral = $stmt->fetch();
            
            if (!$referral) {
                $code = strtoupper(substr($customer['email'], 0, 4)) . strtoupper(bin2hex(random_bytes(3)));
                $creditPerReferral = 1000.00;
                $ins = $pdo->prepare("INSERT INTO customer_referrals (customer_email, referral_code, credit_per_referral, created_at) VALUES (?, ?, ?, NOW())");
                $ins->execute([$customer['email'], $code, $creditPerReferral]);
                $referral = ['id' => $pdo->lastInsertId(), 'referral_code' => $code, 'customer_email' => $customer['email'], 'total_referrals' => 0, 'total_credits' => 0.00, 'credit_per_referral' => $creditPerReferral];
            }
            
            $referralCode = $referral['referral_code'];
            
            $referralUses = $pdo->prepare("SELECT ru.*, o.order_number, o.final_amount FROM referral_uses ru LEFT JOIN orders o ON ru.order_id = o.id WHERE ru.referral_code = ? ORDER BY ru.created_at DESC LIMIT 20");
            $referralUses->execute([$referralCode]);
            $referrals = $referralUses->fetchAll();
            
            $totalEarned = array_sum(array_column($referrals, 'credit_earned'));
            $completedReferrals = count(array_filter($referrals, fn($r) => $r['status'] === 'completed'));
            $pendingReferrals = count(array_filter($referrals, fn($r) => $r['status'] === 'pending'));
            
            return view('front.customer.referrals', compact('customer', 'referral', 'referrals', 'totalEarned', 'completedReferrals', 'pendingReferrals'));
        } catch (\Exception $e) { 
            $referral = null;
            $referrals = [];
            $totalEarned = 0;
            $completedReferrals = 0;
            $pendingReferrals = 0;
            return view('front.customer.referrals', compact('customer', 'referral', 'referrals', 'totalEarned', 'completedReferrals', 'pendingReferrals'));
        }
    }

    public function achievements()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        
        try {
            $achievementService = app(AchievementService::class);
            
            $achievements = [];
            $totalPoints = 0;
            $progressData = [];
            
            $achievedIds = $achievementService->checkAndAward($customer['email']);
            $achievements = $achievementService->getAchievementsForCustomer($customer['email']);
            $totalPoints = $achievementService->getTotalPoints($customer['email']);
            
            foreach ($achievements as $a) {
                if (!$a['is_awarded'] && $a['trigger_type']) {
                    try {
                        $progressData[$a['id']] = $achievementService->getProgressForTrigger($customer['email'], $a['trigger_type']);
                    } catch (\Throwable $e) {
                        $progressData[$a['id']] = ['current' => 0, 'unit' => ''];
                    }
                }
            }
            
            $html = view('front.customer.achievements', compact('customer', 'achievements', 'totalPoints', 'progressData'))->render();
            return response($html);
        } catch (\Throwable $e) {
            $msg = 'Achievements error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
            \Illuminate\Support\Facades\Log::error($msg);
            return response('<h2 style="color:red;font-family:sans-serif;">' . nl2br(htmlspecialchars($msg)) . '</h2>', 500);
        }
    }

    public function affiliate()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        return view('front.customer.affiliate', compact('customer'));
    }

    public function refund()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_email = ? AND payment_status = 'paid' ORDER BY created_at DESC");
            $stmt->execute([$customer['email']]);
            $orders = $stmt->fetchAll();
            if (request()->isMethod('POST')) {
                $orderId = request()->input('order_id');
                $reason = request()->input('reason');
                $details = request()->input('details');
                
                $ins = $pdo->prepare("INSERT INTO refund_requests (order_id, customer_email, reason, details, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
                $ins->execute([$orderId, $customer['email'], $reason, $details]);
                
                return back()->with('success', 'Your refund request has been submitted. We will review it within 24-48 hours.');
            }
            return view('front.customer.refund', compact('customer', 'orders'));
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function notifications()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM customer_notifications WHERE customer_email = ? ORDER BY created_at DESC LIMIT 50");
            $stmt->execute([$customer['email']]);
            $notifications = $stmt->fetchAll();
            return view('front.customer.notifications', compact('customer', 'notifications'));
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function markNotificationRead($id)
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare("UPDATE customer_notifications SET is_read = 1 WHERE id = ? AND customer_email = ?");
            $stmt->execute([$id, $customer['email']]);
            return back();
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function getUnreadCount()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return 0;
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM customer_notifications WHERE customer_email = ? AND is_read = 0");
            $stmt->execute([$customer['email']]);
            return $stmt->fetch()['cnt'] ?? 0;
        } catch (\Exception $e) { return 0; }
    }

    public static function createNotification($email, $type, $title, $message = '', $link = '')
    {
        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("INSERT INTO customer_notifications (customer_email, type, title, message, link, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$email, $type, $title, $message, $link]);
            
            // Send email notification
            try {
                $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
                $fromEmail = $settings['email_from'] ?? 'noreply@joala.com.ng';
                $siteName = $settings['site_name'] ?? 'Joala Ventures';
                
                $subject = 'New Notification - ' . $title;
                $body = "<html><body style='font-family: system-ui, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #1e293b;'>Hello!</h2>
                    <p style='color: #475569;'>You have a new notification:</p>
                    <div style='background: #f8fafc; padding: 20px; border-radius: 12px; margin: 20px 0;'>
                        <h3 style='color: #1e293b; margin: 0 0 10px;'>$title</h3>
                        <p style='color: #475569; margin: 0;'>$message</p>
                    </div>
                    <a href='" . url($link) . "' style='display: inline-block; background: #3b82f6; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none;'>View Details</a>
                    <p style='color: #94a3b8; font-size: 14px; margin-top: 30px;'>- $siteName</p>
                </body></html>";
                
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: $siteName <$fromEmail>\r\n";
                
                @mail($email, $subject, $body, $headers);
            } catch (\Exception $e) {}
            
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function myCourses()
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare("
                SELECT c.*, ce.progress, ce.enrolled_at, ce.completed_at,
                    (SELECT COUNT(*) FROM course_lessons WHERE course_id = c.id AND is_published = 1) as lessons_count,
                    (SELECT COUNT(*) FROM lesson_progress lp WHERE lp.course_id = c.id AND lp.customer_email = ? AND lp.is_completed = 1) as completed_lessons
                FROM course_enrollments ce
                JOIN courses c ON ce.course_id = c.id
                WHERE ce.customer_email = ?
                ORDER BY ce.enrolled_at DESC
            ");
            $stmt->execute([$customer['email'], $customer['email']]);
            $courses = $stmt->fetchAll();
            
            foreach ($courses as &$course) {
                if ((int)$course['lessons_count'] > 0) {
                    $course['progress'] = round(((int)$course['completed_lessons'] / (int)$course['lessons_count']) * 100);
                }
            }
            
            return view('front.customer.courses', compact('customer', 'courses'));
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function viewCourse($id)
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            
            $checkEnroll = $pdo->prepare("SELECT * FROM course_enrollments WHERE customer_email = ? AND course_id = ?");
            $checkEnroll->execute([$customer['email'], $id]);
            $enrollment = $checkEnroll->fetch();
            
            if (!$enrollment) {
                return back()->with('error', 'You are not enrolled in this course.');
            }
            
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
            $stmt->execute([$id]);
            $course = $stmt->fetch();
            
            if (!$course) {
                return back()->with('error', 'Course not found.');
            }
            
            $lessonsStmt = $pdo->prepare("SELECT cl.*, lp.is_completed, lp.completed_at as lesson_completed_at FROM course_lessons cl LEFT JOIN lesson_progress lp ON cl.id = lp.lesson_id AND lp.customer_email = ? WHERE cl.course_id = ? AND cl.is_published = 1 ORDER BY cl.lesson_order");
            $lessonsStmt->execute([$customer['email'], $id]);
            $lessons = $lessonsStmt->fetchAll();
            
            $totalLessons = count($lessons);
            $completedLessons = count(array_filter($lessons, fn($l) => $l['is_completed']));
            $realProgress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
            
            $pdo->prepare("UPDATE course_enrollments SET progress = ?, last_accessed_at = NOW() WHERE customer_email = ? AND course_id = ?")
                ->execute([$realProgress, $customer['email'], $id]);
            
            $enrollment['progress'] = $realProgress;
            
            return view('front.customer.course-view', compact('customer', 'course', 'enrollment', 'lessons', 'totalLessons', 'completedLessons'));
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function updateProgress(Request $request)
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            $courseId = $request->input('course_id');
            $lessonId = $request->input('lesson_id');
            $progress = $request->input('progress', 0);
            
            if ($lessonId) {
                $pdo->prepare("INSERT INTO lesson_progress (customer_email, course_id, lesson_id, is_completed, progress_percent, completed_at, created_at, updated_at) VALUES (?, ?, ?, 1, 100, NOW(), NOW(), NOW()) ON DUPLICATE KEY UPDATE is_completed = 1, progress_percent = 100, completed_at = NOW(), updated_at = NOW()")
                    ->execute([$customer['email'], $courseId, $lessonId]);
                
                $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM course_lessons WHERE course_id = ? AND is_published = 1");
                $totalStmt->execute([$courseId]);
                $total = $totalStmt->fetchColumn();
                
                $compStmt = $pdo->prepare("SELECT COUNT(*) FROM lesson_progress WHERE course_id = ? AND customer_email = ? AND is_completed = 1");
                $compStmt->execute([$courseId, $customer['email']]);
                $completed = $compStmt->fetchColumn();
                
                $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
            }
            
            $stmt = $pdo->prepare("UPDATE course_enrollments SET progress = ?, last_accessed_at = NOW() WHERE customer_email = ? AND course_id = ?");
            $stmt->execute([$progress, $customer['email'], $courseId]);
            
            if ($progress >= 100) {
                $courseStmt = $pdo->prepare("SELECT title, id FROM courses WHERE id = ?");
                $courseStmt->execute([$courseId]);
                $course = $courseStmt->fetch();
                
                $pdo->prepare("UPDATE course_enrollments SET completed_at = NOW() WHERE customer_email = ? AND course_id = ?")
                    ->execute([$customer['email'], $courseId]);
                
                CustomerController::createNotification(
                    $customer['email'],
                    'course_complete',
                    'Course Completed!',
                    'Congratulations! You completed ' . ($course['title'] ?? 'the course') . '. Check your achievements!',
                    '/customer/achievements'
                );
                
                try {
                    app(\App\Services\AchievementService::class)->onCourseComplete($customer['email'], $courseId);
                } catch (\Exception $e) {}
            }
            
            return response()->json(['success' => true, 'progress' => $progress]);
        } catch (\Exception $e) { 
            return response()->json(['error' => $e->getMessage()], 500); 
        }
    }

    public function viewLesson($courseId, $lessonId)
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            
            $stmt = $pdo->prepare("SELECT * FROM course_lessons WHERE id = ? AND course_id = ?");
            $stmt->execute([$lessonId, $courseId]);
            $lesson = $stmt->fetch();
            
            if (!$lesson) {
                return back()->with('error', 'Lesson not found.');
            }
            
            $courseStmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
            $courseStmt->execute([$courseId]);
            $course = $courseStmt->fetch();
            
            $checkEnroll = $pdo->prepare("SELECT * FROM course_enrollments WHERE customer_email = ? AND course_id = ?");
            $checkEnroll->execute([$customer['email'], $courseId]);
            $enrollment = $checkEnroll->fetch();
            
            if (!$enrollment && !$lesson['is_free_preview']) {
                return back()->with('error', 'You are not enrolled in this course.');
            }
            
            $allLessonsStmt = $pdo->prepare("SELECT cl.*, lp.is_completed, lp.completed_at as lesson_completed_at FROM course_lessons cl LEFT JOIN lesson_progress lp ON cl.id = lp.lesson_id AND lp.customer_email = ? WHERE cl.course_id = ? AND cl.is_published = 1 ORDER BY cl.lesson_order");
            $allLessonsStmt->execute([$customer['email'], $courseId]);
            $lessons = $allLessonsStmt->fetchAll();
            
            $progressStmt = $pdo->prepare("SELECT * FROM lesson_progress WHERE customer_email = ? AND lesson_id = ?");
            $progressStmt->execute([$customer['email'], $lessonId]);
            $lessonProgress = $progressStmt->fetch();
            
            return view('front.customer.lesson-view', compact('customer', 'course', 'lesson', 'lessons', 'enrollment', 'lessonProgress'));
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function viewCertificate($courseId)
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            
            $stmt = $pdo->prepare("SELECT * FROM course_enrollments WHERE customer_email = ? AND course_id = ? AND progress >= 100");
            $stmt->execute([$customer['email'], $courseId]);
            $enrollment = $stmt->fetch();
            
            if (!$enrollment) {
                return back()->with('error', 'You must complete the course to get a certificate.');
            }
            
            $courseStmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
            $courseStmt->execute([$courseId]);
            $course = $courseStmt->fetch();
            
            return view('front.customer.certificate', compact('customer', 'course', 'enrollment'));
        } catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function markLessonComplete(Request $request)
    {
        $customer = $this->requireCustomer();
        if (is_a($customer, '\Illuminate\Http\RedirectResponse')) return $customer;
        try {
            $pdo = $this->getPdo();
            $courseId = $request->input('course_id');
            $lessonId = $request->input('lesson_id');
            
            $pdo->prepare("INSERT INTO lesson_progress (customer_email, course_id, lesson_id, is_completed, progress_percent, completed_at, created_at, updated_at) VALUES (?, ?, ?, 1, 100, NOW(), NOW(), NOW()) ON DUPLICATE KEY UPDATE is_completed = 1, progress_percent = 100, completed_at = NOW(), updated_at = NOW()")
                ->execute([$customer['email'], $courseId, $lessonId]);
            
            $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM course_lessons WHERE course_id = ? AND is_published = 1");
            $totalStmt->execute([$courseId]);
            $total = $totalStmt->fetchColumn();
            
            $compStmt = $pdo->prepare("SELECT COUNT(*) FROM lesson_progress WHERE course_id = ? AND customer_email = ? AND is_completed = 1");
            $compStmt->execute([$courseId, $customer['email']]);
            $completed = $compStmt->fetchColumn();
            
            $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
            
            $pdo->prepare("UPDATE course_enrollments SET progress = ?, last_accessed_at = NOW() WHERE customer_email = ? AND course_id = ?")
                ->execute([$progress, $customer['email'], $courseId]);
            
            $isCourseComplete = $progress >= 100;
            
            if ($isCourseComplete) {
                $courseStmt = $pdo->prepare("SELECT title FROM courses WHERE id = ?");
                $courseStmt->execute([$courseId]);
                $course = $courseStmt->fetch();
                
                $pdo->prepare("UPDATE course_enrollments SET completed_at = NOW() WHERE customer_email = ? AND course_id = ?")
                    ->execute([$customer['email'], $courseId]);
                
                CustomerController::createNotification(
                    $customer['email'],
                    'course_complete',
                    'Course Completed!',
                    'Congratulations! You completed ' . ($course['title'] ?? 'the course') . '. Check your achievements!',
                    '/customer/achievements'
                );
                
                try { app(\App\Services\AchievementService::class)->onCourseComplete($customer['email'], $courseId); } catch (\Exception $e) {}
            }
            
            $nextLessonStmt = $pdo->prepare("SELECT id FROM course_lessons WHERE course_id = ? AND is_published = 1 AND lesson_order > (SELECT lesson_order FROM course_lessons WHERE id = ?) ORDER BY lesson_order LIMIT 1");
            $nextLessonStmt->execute([$courseId, $lessonId]);
            $nextLessonId = $nextLessonStmt->fetchColumn();
            
            return response()->json([
                'success' => true,
                'progress' => $progress,
                'completed' => $completed,
                'total' => $total,
                'is_course_complete' => $isCourseComplete,
                'next_lesson_id' => $nextLessonId ?: null,
            ]);
        } catch (\Exception $e) { 
            return response()->json(['error' => $e->getMessage()], 500); 
        }
    }
}