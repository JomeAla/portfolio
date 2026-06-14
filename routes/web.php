<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\BriefController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\SetupController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\PortfolioController;
use App\Http\Controllers\Front\ServiceController as FrontServiceController;
use App\Http\Controllers\Front\AboutController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\BriefController as FrontBriefController;
use App\Http\Controllers\Front\StoreController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Front\InvoiceController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\MarketingController;
use App\Http\Controllers\Admin\FunnelDeployController;

function marketing_pdo() {
    $host = config('database.connections.mysql.host');
    $dbname = config('database.connections.mysql.database');
    $user = config('database.connections.mysql.username');
    $pass = config('database.connections.mysql.password');
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
use App\Http\Controllers\Front\BlogController;
use App\Http\Controllers\Front\CustomerController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\EmailCampaignController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\ProcessController;

// Internal process routes (called from footer)
Route::get('/process-emails', [ProcessController::class, 'processEmails'])->name('process.emails');
Route::get('/process-automation', [ProcessController::class, 'processAutomation'])->name('process.automation');
Route::get('/email-queue-status', [ProcessController::class, 'emailQueueStatus'])->name('email.queue.status');

Route::get('/free-website-mistakes', function() {
    return redirect('/free-website-mistakes.html');
});

Route::get('/free-wordpress-checklist', function() {
    return redirect('/free-wordpress-checklist.html');
});

Route::get('/free-shopify-checklist', function() {
    return redirect('/free-shopify-checklist.html');
});

Route::get('/free-freelancer-guide', function() {
    return redirect('/free-freelancer-guide.html');
});

 
// WordPress Starter Kit
Route::get('/wordpress-starter-kit', function() {
    return view('front.wordpress-starter-kit');
})->name('wordpress.starter.kit');

Route::get('/wordpress-starter-kit.php', function() {
    return view('front.wordpress-starter-kit');
});

Route::get('/wordpress-checkout.php', function() {
    $product = \App\Models\Product::where('slug', 'wordpress-starter-kit')->where('is_active', true)->firstOrFail();
    $paystackKey = \App\Models\Setting::get('paystack_public_key') ?? 'pk_live_xxxxxxxxxxxx';
    return view('front.order.checkout', compact('product', 'paystackKey'));
});

// Coupon validation for standalone pages
Route::get('/validate-coupon', function() {
    $code = strtoupper(request('code'));
    $coupon = \App\Models\Coupon::where('code', $code)->first();
    if (!$coupon || !$coupon->isValid()) return response()->json(['valid' => false, 'message' => 'Invalid coupon code']);
    $amount = (float)(request('amount', 12000));
    if ($coupon->min_order_amount && $amount < (float)$coupon->min_order_amount) {
        return response()->json(['valid' => false, 'message' => 'Minimum order amount not met for this coupon']);
    }
    $discount = $coupon->discount_type === 'percentage'
        ? $amount * ((float)$coupon->discount_value / 100)
        : (float)$coupon->discount_value;
    if ($coupon->max_discount) {
        $discount = min($discount, (float)$coupon->max_discount);
    }
    return response()->json([
        'valid' => true,
        'discount' => round($discount, 2),
        'finalAmount' => round(max(0, $amount - $discount), 2),
    ]);
});

Route::get('/test', function() {
    return 'Server is working!';
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
Route::get('/services', [FrontServiceController::class, 'index'])->name('services');
Route::get('/about', [AboutController::class, 'index'])->name('about');

// Legal Pages
Route::get('/terms', function() {
    return view('front.terms');
})->name('terms');

Route::get('/privacy', function() {
    return view('front.privacy');
})->name('privacy');

Route::get('/refund', function() {
    return view('front.refund');
})->name('refund');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/brief', [FrontBriefController::class, 'create'])->name('brief.create');
Route::post('/brief', [FrontBriefController::class, 'store'])->name('brief.store');

// Blog Routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Store Routes
Route::get('/store', [StoreController::class, 'index'])->name('store');
Route::get('/store/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::get('/buy/{slug}', [OrderController::class, 'checkout'])->name('order.checkout');

// Lead Magnet Landing Page
Route::get('/free-email-checklist', function() {
    return view('front.free-email-checklist');
});

// Email Templates Pack Landing Page
Route::get('/email-templates', function() {
    return view('front.email-sequence-templates-pack');
})->name('email.templates.pack');

// Premium Bundle Landing Page
Route::get('/premium-bundle', function() {
    return view('front.premium-bundle');
})->name('premium.bundle');

// Done-For-You Service Landing Page
Route::get('/done-for-you', function() {
    return view('front.done-for-you');
})->name('done.for.you');

// WhatsApp Marketing Bundle Landing Page
Route::get('/whatsapp-marketing-bundle', function() {
    return view('front.whatsapp-marketing-bundle');
})->name('whatsapp.marketing.bundle');

// Course Creator Kit Landing Page
Route::get('/course-creator-kit', function() {
    return view('front.course-creator-kit');
})->name('course.creator.kit');

// Local Business Digital Kit Landing Page
Route::get('/local-business-digital-kit', function() {
    return view('front.local-business-digital-kit');
})->name('local.business.kit');

// SaaS Starter Kit Landing Page
Route::get('/saas-starter-kit', function() {
    return view('front.saas-starter-kit');
})->name('saas.starter.kit');

// Website Audit Kit Landing Page
Route::get('/website-audit-kit', function() {
    return view('front.website-audit-kit');
})->name('website.audit.kit');

// Website Audit Kit Product
Route::get('/setup-audit-kit', [SetupController::class, 'setupAuditKit']);

// Website Audit post-purchase sequence
Route::get('/setup-audit-sequence', [SetupController::class, 'setupAuditSequence']);

// Setup WhatsApp post-purchase sequence
Route::get('/setup-whatsapp-sequence', [SetupController::class, 'setupWhatsAppSequence']);

// Add WhatsApp upsell to Email Templates sequence
Route::get('/add-whatsapp-upsell', [SetupController::class, 'addWhatsAppUpsell']);
Route::post('/store/validate-coupon', [StoreController::class, 'validateCoupon'])->name('store.coupon');
Route::get('/store/validate-coupon', [StoreController::class, 'validateCoupon'])->name('store.coupon.get');
Route::post('/store/initiate-payment', [OrderController::class, 'initiatePayment'])->name('order.initiate');
Route::post('/ecom-init-payment', [OrderController::class, 'ecomInitPayment'])->name('ecom.init.payment');
Route::get('/order/success', [OrderController::class, 'success'])->name('order.success');
Route::get('/order/download/{token}', [OrderController::class, 'download'])->name('order.download');
Route::post('/order/resend', [OrderController::class, 'resendEmail'])->name('order.resend');

// Support Route
Route::get('/support', [ContactController::class, 'support'])->name('support');
Route::post('/support', [ContactController::class, 'submitSupport'])->name('support.submit');

// Invoice Routes
Route::get('/invoices/{invoiceNumber}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::post('/invoices/{invoiceNumber}/initiate', [InvoiceController::class, 'initiatePayment'])->name('invoices.initiate');
Route::get('/invoices/{invoiceNumber}/callback', [InvoiceController::class, 'callback'])->name('invoices.callback');

// Public Funnel Routes (must be outside /admin prefix)
    Route::get('/funnel-overview', [MarketingController::class, 'funnelOverview'])->name('funnel.overview');
    Route::get('/debug-analytics/{id}', function($id) {
        return 'debug-analytics reached, id=' . $id;
    });
    Route::get('/f/{funnel}', [MarketingController::class, 'showFunnel'])->name('funnel.show');
    Route::get('/funnel/{funnel}/stage/{stage}', [MarketingController::class, 'trackFunnelStage'])->name('funnel.track');
    Route::get('/funnel/{funnel}/convert', [MarketingController::class, 'trackFunnelConversion'])->name('funnel.convert');
    Route::get('/funnel/{funnel}/thank-you', [MarketingController::class, 'showFunnelThankYou'])->name('funnel.thankyou');
    Route::get('/funnel/{funnel}/upsell', [MarketingController::class, 'showUpsell'])->name('funnel.upsell');
    Route::post('/funnel/{funnel}/upsell/accept', [MarketingController::class, 'acceptUpsell'])->name('funnel.upsell.accept');
    Route::get('/funnel/{funnel}/pixel', [MarketingController::class, 'getFunnelPixels'])->name('funnel.pixel');

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    
    Route::middleware(['admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
        
        Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings');
        Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('admin.settings.general');
        Route::post('/settings/appearance', [SettingsController::class, 'updateAppearance'])->name('admin.settings.appearance');
        Route::post('/settings/payment', [SettingsController::class, 'updatePayment'])->name('admin.settings.payment');
        Route::post('/settings/github', [SettingsController::class, 'updateGithub'])->name('admin.settings.github');
        Route::post('/settings/email', [SettingsController::class, 'updateEmail'])->name('admin.settings.email');
        
        Route::resource('projects', ProjectController::class)->except(['show']);
        Route::get('/projects/{project}/delete', [ProjectController::class, 'destroy'])->name('projects.delete');
        
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::resource('testimonials', TestimonialController::class)->except(['show']);
        Route::resource('pages', PageController::class)->only(['index', 'edit', 'update']);
        
        Route::resource('products', ProductController::class)->except(['show']);
        Route::get('/products/{product}/delete', [ProductController::class, 'destroy'])->name('products.delete');
        Route::get('/products/generate/{slug}', function ($slug) {
            $gen = new \App\Services\ProductFileGenerator;
            return $gen->generate($slug);
        })->name('admin.products.generate');
        
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
        Route::post('/orders/{order}/resend', [AdminOrderController::class, 'resendEmail'])->name('admin.orders.resend');
        
        Route::resource('coupons', CouponController::class);
        Route::resource('banners', BannerController::class);
        
        // Membership Routes
        Route::get('/membership/tiers', [MembershipController::class, 'tiers'])->name('admin.membership.tiers');
        Route::get('/membership/tiers/create', [MembershipController::class, 'createTier'])->name('admin.membership.tiers.create');
        Route::post('/membership/tiers', [MembershipController::class, 'storeTier'])->name('admin.membership.tiers.store');
        Route::get('/membership/tiers/{id}/edit', [MembershipController::class, 'editTier'])->name('admin.membership.tiers.edit');
        Route::put('/membership/tiers/{id}', [MembershipController::class, 'updateTier'])->name('admin.membership.tiers.update');
        Route::delete('/membership/tiers/{id}', [MembershipController::class, 'destroyTier'])->name('admin.membership.tiers.destroy');
        
        // Course/LMS Routes
        Route::get('/courses', [MembershipController::class, 'courses'])->name('admin.courses');
        Route::get('/courses/create', [MembershipController::class, 'createCourse'])->name('admin.courses.create');
        Route::post('/courses', [MembershipController::class, 'storeCourse'])->name('admin.courses.store');
        Route::get('/courses/{id}/edit', [MembershipController::class, 'editCourse'])->name('admin.courses.edit');
        Route::put('/courses/{id}', [MembershipController::class, 'updateCourse'])->name('admin.courses.update');
        Route::delete('/courses/{id}', [MembershipController::class, 'destroyCourse'])->name('admin.courses.destroy');
        Route::get('/courses/{id}', [MembershipController::class, 'showCourse'])->name('admin.courses.show');
        
        // Course Lessons
        Route::post('/courses/lesson/add', [MembershipController::class, 'addLesson'])->name('admin.courses.lesson.add');
        Route::delete('/courses/lesson/{id}/delete', [MembershipController::class, 'deleteLesson'])->name('admin.courses.lesson.delete');
        
        // Customer Notifications Admin Route
        Route::get('/notifications', [MembershipController::class, 'notifications'])->name('admin.notifications');
        Route::post('/notifications/send', [MembershipController::class, 'sendNotification'])->name('admin.notifications.send');
        
        // Advanced Analytics Route
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');

        // Dashboard Stats API
        Route::get('/stats', function () {
            $db = marketing_pdo();

            $visitorsDaily = (int)($db->query("SELECT COUNT(DISTINCT ip_address) FROM page_visits WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)")->fetchColumn() ?: 0);
            $visitorsWeekly = (int)($db->query("SELECT COUNT(DISTINCT ip_address) FROM page_visits WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn() ?: 0);
            $visitorsMonthly = (int)($db->query("SELECT COUNT(DISTINCT ip_address) FROM page_visits WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn() ?: 0);
            $visitorsYearly = (int)($db->query("SELECT COUNT(DISTINCT ip_address) FROM page_visits WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 365 DAY)")->fetchColumn() ?: 0);

            return response()->json([
                'leads' => (int)($db->query("SELECT COUNT(*) FROM leads")->fetchColumn() ?: 0),
                'deals' => (int)($db->query("SELECT COUNT(*) FROM deals WHERE stage NOT IN ('won','lost')")->fetchColumn() ?: 0),
                'orders' => (int)($db->query("SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn() ?: 0),
                'revenue' => (float)($db->query("SELECT COALESCE(SUM(final_amount),0) FROM orders WHERE payment_status='success' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn() ?: 0),
                'visitors_daily' => $visitorsDaily,
                'visitors_weekly' => $visitorsWeekly,
                'visitors_monthly' => $visitorsMonthly,
                'visitors_yearly' => $visitorsYearly,
            ]);
        })->name('admin.stats');

        Route::get('/chart-data', function () {
            $db = marketing_pdo();
            $months = [];
            $leadsData = [];
            $revenueData = [];
            for ($i = 5; $i >= 0; $i--) {
                $m = date('M', strtotime("-$i months"));
                $ym = date('Y-m', strtotime("-$i months"));
                $months[] = $m;
                $leadsData[] = (int)($db->prepare("SELECT COUNT(*) FROM leads WHERE DATE_FORMAT(created_at,'%Y-%m') = ?")->execute([$ym]) ? $db->query("SELECT COUNT(*) FROM leads WHERE DATE_FORMAT(created_at,'%Y-%m') = '$ym'")->fetchColumn() : 0);
            }
            $result = $db->query("SELECT DATE_FORMAT(created_at,'%Y-%m') as ym, COALESCE(SUM(final_amount),0) as total FROM orders WHERE payment_status='success' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY ym ORDER BY ym");
            $revMap = [];
            while ($row = $result->fetch()) { $revMap[$row['ym']] = (float)$row['total']; }
            foreach ($months as $idx => $ym) {
                $ymKey = date('Y-m', strtotime("-" . (5-$idx) . " months"));
                $revenueData[] = $revMap[$ymKey] ?? 0;
            }
            return response()->json([
                'leads_labels' => $months,
                'leads_data' => $leadsData,
                'revenue_labels' => $months,
                'revenue_data' => $revenueData,
            ]);
        })->name('admin.chart-data');
        
        // Email Campaigns (Broadcasts)
        Route::get('/email/campaigns', [EmailCampaignController::class, 'index'])->name('admin.email.campaigns');
        Route::get('/email/campaigns/create', [EmailCampaignController::class, 'create'])->name('admin.email.campaigns.create');
        Route::post('/email/campaigns', [EmailCampaignController::class, 'store'])->name('admin.email.campaigns.store');
        
        Route::get('/support', [SupportController::class, 'index'])->name('admin.support');
        Route::get('/support/{ticket}', [SupportController::class, 'show'])->name('admin.support.show');
        Route::put('/support/{ticket}', [SupportController::class, 'update'])->name('admin.support.update');
        Route::delete('/support/{ticket}', [SupportController::class, 'destroy'])->name('admin.support.destroy');
        
        Route::get('/briefs', [BriefController::class, 'index'])->name('admin.briefs');
        Route::get('/briefs/unread-count', [BriefController::class, 'unreadCountJson'])->name('admin.briefs.unread');
        Route::get('/briefs/{brief}', [BriefController::class, 'show'])->name('admin.briefs.show');
        Route::put('/briefs/{brief}', [BriefController::class, 'update'])->name('admin.briefs.update');
        Route::delete('/briefs/{brief}', [BriefController::class, 'destroy'])->name('admin.briefs.destroy');
        
        // Invoice Routes
        Route::resource('invoices', AdminInvoiceController::class)->names('admin.invoices');
        Route::post('/invoices/{invoice}/send', [AdminInvoiceController::class, 'sendInvoice'])->name('admin.invoices.send');
        Route::post('/invoices/{invoice}/payment-link', [AdminInvoiceController::class, 'generatePaymentLink'])->name('admin.invoices.payment-link');
        Route::post('/invoices/{invoice}/mark-paid', [AdminInvoiceController::class, 'markAsPaid'])->name('admin.invoices.mark-paid');
        
        // Marketing Module Routes
        Route::get('/marketing', [MarketingController::class, 'dashboard'])->name('admin.marketing');
        
        // Blog Posts
        Route::get('/marketing/blog', [MarketingController::class, 'blogIndex'])->name('admin.marketing.blog');
        Route::get('/marketing/blog/create', [MarketingController::class, 'blogCreate'])->name('admin.marketing.blog.create');
        Route::post('/marketing/blog', [MarketingController::class, 'blogStore'])->name('admin.marketing.blog.store');
        Route::get('/marketing/blog/{blog}/edit', [MarketingController::class, 'blogEdit'])->name('admin.marketing.blog.edit');
        Route::put('/marketing/blog/{blog}', [MarketingController::class, 'blogUpdate'])->name('admin.marketing.blog.update');
        Route::delete('/marketing/blog/{blog}', [MarketingController::class, 'blogDestroy'])->name('admin.marketing.blog.destroy');
        
        // Tweets
        Route::get('/marketing/tweets', [MarketingController::class, 'tweetsIndex'])->name('admin.marketing.tweets');
        Route::get('/marketing/tweets/create', [MarketingController::class, 'tweetsCreate'])->name('admin.marketing.tweets.create');
        Route::post('/marketing/tweets', [MarketingController::class, 'tweetsStore'])->name('admin.marketing.tweets.store');
        Route::get('/marketing/tweets/{tweet}/edit', [MarketingController::class, 'tweetsEdit'])->name('admin.marketing.tweets.edit');
        Route::put('/marketing/tweets/{tweet}', [MarketingController::class, 'tweetsUpdate'])->name('admin.marketing.tweets.update');
        Route::delete('/marketing/tweets/{tweet}', [MarketingController::class, 'tweetsDestroy'])->name('admin.marketing.tweets.destroy');
        Route::post('/marketing/tweets/{tweet}/send', [MarketingController::class, 'tweetsSendNow'])->name('admin.marketing.tweets.send');
        
        // Landing Pages
        Route::get('/marketing/landing-pages', [MarketingController::class, 'landingPagesIndex'])->name('admin.marketing.landing-pages');
        Route::get('/marketing/landing-pages/create', [MarketingController::class, 'landingPagesCreate'])->name('admin.marketing.landing-pages.create');
        Route::post('/marketing/landing-pages', [MarketingController::class, 'landingPagesStore'])->name('admin.marketing.landing-pages.store');
        Route::get('/marketing/landing-pages/{landingPage}/edit', [MarketingController::class, 'landingPagesEdit'])->name('admin.marketing.landing-pages.edit');
        Route::put('/marketing/landing-pages/{landingPage}', [MarketingController::class, 'landingPagesUpdate'])->name('admin.marketing.landing-pages.update');
        Route::delete('/marketing/landing-pages/{landingPage}', [MarketingController::class, 'landingPagesDestroy'])->name('admin.marketing.landing-pages.destroy');
        Route::get('/marketing/landing-pages/{landingPage}/embed', [MarketingController::class, 'getEmbedCode'])->name('admin.marketing.landing-pages.embed');
        
        // Leads
        Route::get('/marketing/leads', [MarketingController::class, 'leadsIndex'])->name('admin.marketing.leads');
        Route::get('/marketing/leads/create', [MarketingController::class, 'leadsCreate'])->name('admin.marketing.leads.create');
        Route::post('/marketing/leads', [MarketingController::class, 'leadsStore'])->name('admin.marketing.leads.store');
        Route::put('/marketing/leads/{lead}', [MarketingController::class, 'leadsUpdate'])->name('admin.marketing.leads.update');
        Route::delete('/marketing/leads/{lead}', [MarketingController::class, 'leadsDestroy'])->name('admin.marketing.leads.destroy');
        Route::get('/marketing/leads/export', [MarketingController::class, 'leadsExport'])->name('admin.marketing.leads.export');
        Route::post('/marketing/leads/import', [MarketingController::class, 'leadsImport'])->name('admin.marketing.leads.import');
        
        // Email Sequences
        Route::get('/marketing/sequences', [MarketingController::class, 'sequencesIndex'])->name('admin.marketing.sequences');
        Route::get('/marketing/sequences/create', [MarketingController::class, 'sequencesCreate'])->name('admin.marketing.sequences.create');
        Route::post('/marketing/sequences', [MarketingController::class, 'sequencesStore'])->name('admin.marketing.sequences.store');
        Route::get('/marketing/sequences/{sequence}/edit', [MarketingController::class, 'sequencesEdit'])->name('admin.marketing.sequences.edit');
        Route::put('/marketing/sequences/{sequence}', [MarketingController::class, 'sequencesUpdate'])->name('admin.marketing.sequences.update');
        Route::delete('/marketing/sequences/{sequence}', [MarketingController::class, 'sequencesDestroy'])->name('admin.marketing.sequences.destroy');
        Route::post('/marketing/sequences/{sequence}/steps', [MarketingController::class, 'stepsStore'])->name('admin.marketing.steps.store');
        Route::get('/marketing/steps/{sequenceStep}/edit', [MarketingController::class, 'stepsEdit'])->name('admin.marketing.steps.edit');
        Route::put('/marketing/steps/{sequenceStep}', [MarketingController::class, 'stepsUpdate'])->name('admin.marketing.steps.update');
        Route::delete('/marketing/steps/{sequenceStep}', [MarketingController::class, 'stepsDestroy'])->name('admin.marketing.steps.destroy');
        
        // Email Queue
        Route::get('/marketing/email-queue', [MarketingController::class, 'emailQueueIndex'])->name('admin.marketing.email-queue');
        Route::post('/marketing/email-queue/{emailQueue}/retry', [MarketingController::class, 'emailQueueRetry'])->name('admin.marketing.email-queue.retry');
        
        // Twitter Settings
        Route::get('/marketing/settings', [MarketingController::class, 'twitterSettings'])->name('admin.marketing.settings');
        
        // Tags Management
        Route::get('/marketing/tags', [MarketingController::class, 'tagsIndex'])->name('admin.marketing.tags');
        Route::post('/marketing/tags', [MarketingController::class, 'tagsStore'])->name('admin.marketing.tags.store');
        Route::put('/marketing/tags/{tag}', [MarketingController::class, 'tagsUpdate'])->name('admin.marketing.tags.update');
        Route::delete('/marketing/tags/{tag}', [MarketingController::class, 'tagsDestroy'])->name('admin.marketing.tags.destroy');
        Route::post('/marketing/leads/{lead}/tags', [MarketingController::class, 'leadTagsUpdate'])->name('admin.marketing.leads.tags');
        
        // Campaigns
        Route::get('/marketing/campaigns', [MarketingController::class, 'campaignsIndex'])->name('admin.marketing.campaigns');
        Route::get('/marketing/campaigns/create', [MarketingController::class, 'campaignsCreate'])->name('admin.marketing.campaigns.create');
        Route::post('/marketing/campaigns', [MarketingController::class, 'campaignsStore'])->name('admin.marketing.campaigns.store');
        Route::get('/marketing/campaigns/{campaign}/edit', [MarketingController::class, 'campaignsEdit'])->name('admin.marketing.campaigns.edit');
        Route::put('/marketing/campaigns/{campaign}', [MarketingController::class, 'campaignsUpdate'])->name('admin.marketing.campaigns.update');
        Route::delete('/marketing/campaigns/{campaign}', [MarketingController::class, 'campaignsDestroy'])->name('admin.marketing.campaigns.destroy');
        Route::post('/marketing/campaigns/{campaign}/enroll', [MarketingController::class, 'campaignsEnroll'])->name('admin.marketing.campaigns.enroll');
        
        // Newsletter Management
        Route::get('/marketing/newsletter', [MarketingController::class, 'newsletterIndex'])->name('admin.marketing.newsletter');
        Route::post('/marketing/newsletter/send', [MarketingController::class, 'newsletterSend'])->name('admin.marketing.newsletter.send');
        Route::get('/marketing/newsletter/export', [MarketingController::class, 'newsletterExport'])->name('admin.marketing.newsletter.export');
        Route::delete('/marketing/newsletter/{lead}', [MarketingController::class, 'newsletterDestroy'])->name('admin.marketing.newsletter.destroy');
        Route::post('/marketing/settings', [MarketingController::class, 'twitterSettingsUpdate'])->name('admin.marketing.settings.update');
        Route::get('/marketing/twitter/auth', [MarketingController::class, 'twitterAuth'])->name('admin.marketing.twitter.auth');
        Route::get('/marketing/twitter/callback', [MarketingController::class, 'twitterCallback'])->name('admin.marketing.twitter.callback');
        
        // Automation Rules
        Route::get('/marketing/automation', [MarketingController::class, 'automationIndex'])->name('admin.marketing.automation');
        Route::post('/marketing/automation', [MarketingController::class, 'automationStore'])->name('admin.marketing.automation.store');
        Route::put('/marketing/automation/{rule}', [MarketingController::class, 'automationUpdate'])->name('admin.marketing.automation.update');
        Route::delete('/marketing/automation/{rule}', [MarketingController::class, 'automationDestroy'])->name('admin.marketing.automation.destroy');
        Route::post('/marketing/automation/{rule}/toggle', [MarketingController::class, 'automationToggle'])->name('admin.marketing.automation.toggle');
        
        // A/B Testing
        Route::get('/marketing/ab-tests', [MarketingController::class, 'abTestsIndex'])->name('admin.marketing.ab-tests');
        Route::post('/marketing/ab-tests', [MarketingController::class, 'abTestsStore'])->name('admin.marketing.ab-tests.store');
        Route::post('/marketing/ab-tests/{test}/start', [MarketingController::class, 'abTestsStart'])->name('admin.marketing.ab-tests.start');
        Route::post('/marketing/ab-tests/{test}/stop', [MarketingController::class, 'abTestsStop'])->name('admin.marketing.ab-tests.stop');
        Route::delete('/marketing/ab-tests/{test}', [MarketingController::class, 'abTestsDestroy'])->name('admin.marketing.ab-tests.destroy');
        
        // Webhooks
        Route::get('/marketing/webhooks', [MarketingController::class, 'webhooksIndex'])->name('admin.marketing.webhooks');
        Route::post('/marketing/webhooks', [MarketingController::class, 'webhooksStore'])->name('admin.marketing.webhooks.store');
        Route::put('/marketing/webhooks/{webhook}', [MarketingController::class, 'webhooksUpdate'])->name('admin.marketing.webhooks.update');
        Route::delete('/marketing/webhooks/{webhook}', [MarketingController::class, 'webhooksDestroy'])->name('admin.marketing.webhooks.destroy');
        Route::post('/marketing/webhooks/{webhook}/toggle', [MarketingController::class, 'webhooksToggle'])->name('admin.marketing.webhooks.toggle');
        Route::post('/marketing/webhooks/{webhook}/test', [MarketingController::class, 'webhooksTest'])->name('admin.marketing.webhooks.test');
        
        // Webhook History
        Route::get('/marketing/webhooks/history', function() {
            $history = \App\Models\WebhookFiringHistory::with(['automationRule', 'lead'])->latest()->paginate(20);
            return view('admin.marketing.webhooks.history', compact('history'));
        })->name('admin.marketing.webhooks.history');
        
        // Email Templates
        Route::get('/marketing/email-templates', [MarketingController::class, 'emailTemplatesIndex'])->name('admin.marketing.email-templates');
        Route::get('/marketing/email-templates/create', [MarketingController::class, 'emailTemplatesCreate'])->name('admin.marketing.email-templates.create');
        Route::post('/marketing/email-templates', [MarketingController::class, 'emailTemplatesStore'])->name('admin.marketing.email-templates.store');
        Route::get('/marketing/email-templates/{template}/edit', [MarketingController::class, 'emailTemplatesEdit'])->name('admin.marketing.email-templates.edit');
        Route::put('/marketing/email-templates/{template}', [MarketingController::class, 'emailTemplatesUpdate'])->name('admin.marketing.email-templates.update');
        Route::delete('/marketing/email-templates/{template}', [MarketingController::class, 'emailTemplatesDestroy'])->name('admin.marketing.email-templates.destroy');
        Route::post('/marketing/email-templates/{template}/toggle', [MarketingController::class, 'emailTemplatesToggle'])->name('admin.marketing.email-templates.toggle');
        Route::post('/marketing/email-templates/{template}/duplicate', [MarketingController::class, 'emailTemplatesDuplicate'])->name('admin.marketing.email-templates.duplicate');
        Route::get('/marketing/email-templates/{template}/preview', [MarketingController::class, 'emailTemplatesPreview'])->name('admin.marketing.email-templates.preview');
        
        // Visual Email Builder
        Route::get('/marketing/email-builder', [MarketingController::class, 'emailBuilderIndex'])->name('admin.marketing.email-builder');
        Route::get('/marketing/email-builder/create', [MarketingController::class, 'emailBuilderCreate'])->name('admin.marketing.email-builder.create');
        Route::post('/marketing/email-builder', [MarketingController::class, 'emailBuilderStore'])->name('admin.marketing.email-builder.store');
        Route::post('/marketing/email-builder/preview', [MarketingController::class, 'emailBuilderPreview'])->name('admin.marketing.email-builder.preview');
        
        // Lead Scoring
        Route::get('/marketing/lead-scoring', [MarketingController::class, 'leadScoringIndex'])->name('admin.marketing.lead-scoring');
        Route::post('/marketing/lead-scoring/recalculate', [MarketingController::class, 'leadScoringRecalculate'])->name('admin.marketing.lead-scoring.recalculate');
        
        // Segments
        Route::get('/marketing/segments', [MarketingController::class, 'segmentsIndex'])->name('admin.marketing.segments');
        Route::get('/marketing/segments/create', [MarketingController::class, 'segmentsCreate'])->name('admin.marketing.segments.create');
        Route::post('/marketing/segments', [MarketingController::class, 'segmentsStore'])->name('admin.marketing.segments.store');
        Route::get('/marketing/segments/{segment}/edit', [MarketingController::class, 'segmentsEdit'])->name('admin.marketing.segments.edit');
        Route::put('/marketing/segments/{segment}', [MarketingController::class, 'segmentsUpdate'])->name('admin.marketing.segments.update');
        Route::delete('/marketing/segments/{segment}', [MarketingController::class, 'segmentsDestroy'])->name('admin.marketing.segments.destroy');
        Route::post('/marketing/segments/{segment}/sync', [MarketingController::class, 'segmentsSync'])->name('admin.marketing.segments.sync');
        
        // Analytics
        Route::get('/marketing/analytics', [MarketingController::class, 'analyticsIndex'])->name('admin.marketing.analytics');
        Route::get('/marketing/analytics/funnel', [MarketingController::class, 'analyticsFunnel'])->name('admin.marketing.analytics.funnel');
        Route::get('/marketing/analytics/revenue', [MarketingController::class, 'analyticsRevenue'])->name('admin.marketing.analytics.revenue');
        Route::get('/marketing/analytics/campaigns', [MarketingController::class, 'analyticsCampaigns'])->name('admin.marketing.analytics.campaigns');
        
        // CRM - Lead Timeline
        Route::get('/marketing/leads/{lead}/timeline', [MarketingController::class, 'leadTimeline'])->name('admin.marketing.leads.timeline');
        Route::post('/marketing/leads/{lead}/activity', [MarketingController::class, 'leadActivityStore'])->name('admin.marketing.leads.activity.store');
        
        // CRM - Deals Pipeline
        Route::get('/marketing/deals', [MarketingController::class, 'dealsIndex'])->name('admin.marketing.deals');
        Route::post('/marketing/deals', [MarketingController::class, 'dealsStore'])->name('admin.marketing.deals.store');
        Route::put('/marketing/deals/{deal}', [MarketingController::class, 'dealsUpdate'])->name('admin.marketing.deals.update');
        Route::delete('/marketing/deals/{deal}', [MarketingController::class, 'dealsDestroy'])->name('admin.marketing.deals.destroy');
        
        // CRM - Tasks
        Route::get('/marketing/tasks', [MarketingController::class, 'tasksIndex'])->name('admin.marketing.tasks');
        Route::post('/marketing/tasks', [MarketingController::class, 'tasksStore'])->name('admin.marketing.tasks.store');
        Route::put('/marketing/tasks/{task}', [MarketingController::class, 'tasksUpdate'])->name('admin.marketing.tasks.update');
        Route::delete('/marketing/tasks/{task}', [MarketingController::class, 'tasksDestroy'])->name('admin.marketing.tasks.destroy');
        
        // NotebookLM Content Generation
        Route::get('/marketing/notebooklm', [MarketingController::class, 'notebookLmIndex'])->name('admin.marketing.notebooklm');
        Route::post('/marketing/notebooklm/generate', [MarketingController::class, 'notebookLmGenerate'])->name('admin.marketing.notebooklm.generate');
        Route::post('/marketing/notebooklm/tweets', [MarketingController::class, 'notebookLmGenerateTweets'])->name('admin.marketing.notebooklm.tweets');
        Route::post('/marketing/notebooklm/sequence', [MarketingController::class, 'notebookLmGenerateSequence'])->name('admin.marketing.notebooklm.sequence');
        Route::post('/marketing/notebooklm/chat', [MarketingController::class, 'notebookLmChat'])->name('admin.marketing.notebooklm.chat');
        
        // Sales Funnels
        Route::get('/marketing/funnels', [MarketingController::class, 'funnelsIndex'])->name('admin.marketing.funnels');
        Route::get('/marketing/funnels/create', [MarketingController::class, 'funnelsCreate'])->name('admin.marketing.funnels.create');
        Route::post('/marketing/funnels', [MarketingController::class, 'funnelsStore'])->name('admin.marketing.funnels.store');
        Route::post('/marketing/funnels/health-all', [MarketingController::class, 'funnelsHealthAll'])->name('admin.marketing.funnels.health-all');
        Route::get('/marketing/funnels/{funnel}/ab-test', [MarketingController::class, 'funnelAbTest'])->name('admin.marketing.funnels.ab-test');
        Route::post('/marketing/funnels/{funnel}/ab-test', [MarketingController::class, 'funnelAbTestStore'])->name('admin.marketing.funnels.ab-test.store');
        Route::get('/marketing/funnels/{funnel}/edit', [MarketingController::class, 'funnelsEdit'])->name('admin.marketing.funnels.edit');
        Route::put('/marketing/funnels/{funnel}', [MarketingController::class, 'funnelsUpdate'])->name('admin.marketing.funnels.update');
        Route::delete('/marketing/funnels/{funnel}', [MarketingController::class, 'funnelsDestroy'])->name('admin.marketing.funnels.destroy');
        Route::post('/marketing/funnels/{funnel}/stages', [MarketingController::class, 'funnelStagesStore'])->name('admin.marketing.funnels.stages');
        Route::post('/marketing/funnels/{funnel}/clone', [MarketingController::class, 'funnelsClone'])->name('admin.marketing.funnels.clone');
        Route::post('/marketing/funnels/{funnel}/product', [MarketingController::class, 'updateFunnelProduct'])->name('admin.marketing.funnels.product');
        Route::get('/marketing/funnels/{funnel}/analytics', [MarketingController::class, 'getFunnelAnalytics'])->name('admin.marketing.funnels.analytics');
        Route::get('/marketing/funnels/{funnel}/leads', [MarketingController::class, 'getFunnelLeads'])->name('admin.marketing.funnels.leads');
        Route::get('/marketing/funnels/{funnel}/export', [FunnelDeployController::class, 'exportFunnelData'])->name('admin.marketing.funnels.export');
        Route::get('/marketing/funnels/{funnel}/deploy', [FunnelDeployController::class, 'showDeployForm'])->name('admin.marketing.funnels.deploy');
        Route::post('/marketing/funnels/{funnel}/deploy', [FunnelDeployController::class, 'deployToProduction'])->name('admin.marketing.funnels.deploy');
        Route::get('/marketing/funnels/import', [FunnelDeployController::class, 'importForm'])->name('admin.marketing.funnels.import');
        Route::post('/marketing/funnels/import', [FunnelDeployController::class, 'import'])->name('admin.marketing.funnels.import');
        
// Funnel overview (legacy .php URL) - handled by public/funnel-overview.php directly

    // Funnel Templates (at /admin/marketing/templates)
        Route::get('/marketing/templates', function() {
            try {
                $funnels = \App\Models\Funnel::where('is_active', 1)->latest()->limit(50)->get(['id', 'name', 'description', 'funnel_type', 'created_at']);
                
                $templateCategories = [
                    'lead_magnet' => 'Lead Magnet',
                    'tripwire' => 'Tripwire',
                    'webinar' => 'Webinar',
                    'launch' => 'Product Launch',
                    'affiliate' => 'Affiliate Promo',
                ];
                
                $category = request()->get('category');
                
                $templatesArray = [
                    ['id' => 1, 'name' => 'Lead Magnet Funnel', 'template_category' => 'lead_magnet', 'stages' => collect(['Lead Page', 'Thank You', 'Follow Up 1', 'Follow Up 2']), 'description' => 'Capture leads with a free download and nurture them to a purchase'],
                    ['id' => 2, 'name' => 'Tripwire Funnel', 'template_category' => 'tripwire', 'stages' => collect(['Sales Page', 'Upsell', 'Downsell']), 'description' => 'Low-cost entry point product to attract customers'],
                    ['id' => 3, 'name' => 'Webinar Funnel', 'template_category' => 'webinar', 'stages' => collect(['Registration', 'Confirmation', 'Webinar', 'Follow Up 1', 'Follow Up 2']), 'description' => 'Webinar registration, follow-up, and pitch sequence'],
                    ['id' => 4, 'name' => 'Product Launch Funnel', 'template_category' => 'launch', 'stages' => collect(['Pre-launch', 'Launch', 'Bonus', 'Cart Close', 'Last Chance', 'Thank You']), 'description' => 'Pre-launch, launch, and cart close sequence'],
                    ['id' => 5, 'name' => 'Affiliate Promo Funnel', 'template_category' => 'affiliate', 'stages' => collect(['Review Page', 'Bonus Page', 'Affiliate Link']), 'description' => 'Promote affiliate products with bonus content'],
                ];
                
                $templates = collect($templatesArray);
                
                if ($category) {
                    $templates = $templates->filter(function($t) use ($category) {
                        return $t['template_category'] === $category;
                    });
                }
                
                return view('admin.marketing.funnels.templates', compact('funnels', 'templates', 'templateCategories'));
            } catch (\Exception $e) {
                return "Error: " . $e->getMessage();
            }
        })->name('admin.marketing.templates');
        
        // Use template POST route
        Route::post('/marketing/templates/{id}/use', function($id) {
            try {
                $templates = [
                    1 => ['name' => 'Lead Magnet Funnel', 'type' => 'lead-magnet'],
                    2 => ['name' => 'Tripwire Funnel', 'type' => 'tripwire'],
                    3 => ['name' => 'Webinar Funnel', 'type' => 'webinar'],
                    4 => ['name' => 'Product Launch Funnel', 'type' => 'launch'],
                    5 => ['name' => 'Affiliate Promo Funnel', 'type' => 'affiliate'],
                ];
                
                if (!isset($templates[$id])) {
                    return back()->with('error', 'Template not found');
                }
                
                $template = $templates[$id];
                $name = $template['name'];
                $slug = \Illuminate\Support\Str::slug($name) . '-' . time();
                
                $funnel = \App\Models\Funnel::create([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => 'Created from ' . $template['type'] . ' template',
                    'funnel_type' => 'sales',
                    'is_active' => true,
                ]);

                return redirect('/admin/marketing/funnels')->with('success', 'Funnel created from template!');
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        })->name('admin.marketing.templates.use');
        
        // Automation Builder
        Route::get('/marketing/automation/builder', function() {
            try {
                $pdo = marketing_pdo();
                $funnelData = $pdo->query("SELECT id, name FROM funnels LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                if (!$funnelData) {
                    $funnelData = ['id' => 0, 'name' => 'No Funnel - Create one first'];
                }
                $funnel = (object) $funnelData;
            } catch (\Exception $e) {
                $funnel = (object) ['id' => 0, 'name' => 'Demo Funnel'];
            }
            return view('admin.marketing.automation.builder', compact('funnel'));
        })->name('admin.marketing.automation.builder');
        
        // Quick migration runner - TEMPORARY
        Route::get('/run-order-bumps', function() {
            try {
                $pdo = marketing_pdo();
                
                // Check if columns exist
                $columns = $pdo->query("SHOW COLUMNS FROM orders LIKE 'order_bumps'")->fetchAll();
                if (count($columns) > 0) {
                    return "✅ order_bumps column already exists!";
                }
                
                // Add columns
                $pdo->exec("ALTER TABLE orders ADD COLUMN order_bumps JSON NULL AFTER checkout_abandoned_at");
                $pdo->exec("ALTER TABLE orders ADD COLUMN order_bumps_total DECIMAL(10,2) NULL AFTER order_bumps");
                
                return "✅ SUCCESS! Order Bumps columns added to orders table!";
            } catch (\Exception $e) {
                return "❌ ERROR: " . $e->getMessage();
            }
        });
        
        // Funnel Tracking (Public) - moved to top level above admin routes
        // Seed Product (Protected - Admin only)
        Route::post('/admin/seed-product', [MarketingController::class, 'seedProduct'])->name('admin.seed.product');
        
        // Refund Management Routes
        Route::get('/refunds', function() {
            try {
                $pdo = marketing_pdo();
                $refunds = $pdo->query("SELECT * FROM refund_requests ORDER BY created_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
                return view('admin.refunds.index', compact('refunds'));
            } catch (\Exception $e) {
                return "Error: " . $e->getMessage();
            }
        })->name('admin.refunds');
        
        Route::post('/refunds/{id}/approve', function($id) {
            try {
                $pdo = marketing_pdo();
                $pdo->exec("UPDATE refund_requests SET status = 'approved', processed_at = NOW() WHERE id = $id");
                return back()->with('success', 'Refund approved!');
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        })->name('admin.refunds.approve');
        
        Route::post('/refunds/{id}/reject', function($id) {
            try {
                $pdo = marketing_pdo();
                $pdo->exec("UPDATE refund_requests SET status = 'rejected', processed_at = NOW() WHERE id = $id");
                return back()->with('success', 'Refund rejected!');
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        })->name('admin.refunds.reject');
        
        // Affiliate Management Routes
        Route::get('/affiliates', function() {
            try {
                $pdo = marketing_pdo();
                $affiliates = $pdo->query("SELECT * FROM affiliates ORDER BY created_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
                return view('admin.affiliates.index', compact('affiliates'));
            } catch (\Exception $e) {
                return "Error: " . $e->getMessage();
            }
        })->name('admin.affiliates');
        
        Route::delete('/affiliates/{id}', function($id) {
            try {
                $pdo = marketing_pdo();
                $stmt = $pdo->prepare("DELETE FROM affiliates WHERE id = ?");
                $stmt->execute([$id]);
                return redirect()->route('admin.affiliates')->with('success', 'Affiliate deleted successfully');
            } catch (\Exception $e) {
                return "Error: " . $e->getMessage();
            }
        })->name('admin.affiliates.delete');
    });

    // Run Migrations (Public)
    Route::get('/run-migrations', [MarketingController::class, 'runMigrations'])->name('admin.run.migrations');
    Route::post('/run-migrations', [MarketingController::class, 'runMigrations'])->name('admin.run.migrations');
    Route::get('/create-funnels', [MarketingController::class, 'createFunnelTables'])->name('admin.create.funnels');
});

// Quick product list endpoint
Route::get('/debug-products', function () {
    $products = \Illuminate\Support\Facades\DB::table('products')->select('id', 'title', 'slug', 'price', 'sale_price', 'file_path', 'image', 'is_active')->orderBy('id')->get()->toArray();
    return "<h2>Products (" . count($products) . ")</h2><pre>" . print_r($products, true) . "</pre>";
});

// Setup new products from roadmap (with key for security)
Route::get('/update-product-paths', function () {
    $key = request('key', '');
    if ($key !== 'joala2024') { return "Invalid key"; }

    $products = [
        ['slug' => 'email-sequence-templates-pack', 'file' => 'Email Sequence Templates Pack - JoAla Ventures.zip'],
        ['slug' => 'e-commerce-starter-kit', 'file' => 'ecommerce-starter-kit-v1.1.0.zip'],
        ['slug' => 'whatsapp-marketing-bundle', 'file' => 'WhatsApp Marketing Bundle - Complete Templates.zip'],
    ];

    $baseDir = storage_path('app/public/products');
    $output = "<h2>Updating Product File Paths</h2>";

    foreach ($products as $p) {
        $filePath = $baseDir . '/' . $p['file'];
        $exists = file_exists($filePath);
        $size = $exists ? filesize($filePath) : 0;
        $relPath = 'products/' . $p['file'];

        \Illuminate\Support\Facades\DB::table('products')
            ->where('slug', $p['slug'])
            ->update(['file_path' => $relPath]);

        $output .= "<b>{$p['slug']}</b><br>";
        $output .= "&nbsp;&nbsp;File: {$p['file']}<br>";
        $output .= "&nbsp;&nbsp;Exists: " . ($exists ? "✅ ($size bytes)" : "❌ NOT FOUND") . "<br>";
        $output .= "&nbsp;&nbsp;DB path set to: {$relPath}<br><br>";
    }

    $output .= "<hr><h3>Verification</h3>";
    $products = \Illuminate\Support\Facades\DB::table('products')
        ->whereIn('slug', array_column($products, 'slug'))
        ->select('id', 'title', 'slug', 'file_path', 'is_active')
        ->get();
    $output .= "<pre>" . print_r(json_decode(json_encode($products), true), true) . "</pre>";

    $output .= "<hr><h3>Download Test</h3>";
    foreach ($products as $p) {
        $fullPath = storage_path('app/public/' . $p->file_path);
        $output .= "<b>{$p->title}:</b> " . (file_exists($fullPath) ? "✅ File found at {$fullPath}" : "❌ NOT FOUND at {$fullPath}") . "<br>";
    }

    return $output;
});

Route::get('/setup-new-products', function () {
    $key = request('key', '');
    if ($key !== 'joala2024') {
        return "Invalid key";
    }
    $gen = new \App\Services\ProductFileGenerator;
    $output = '';

    $newProducts = [
        [
            'title' => 'Financial Literacy E-Book',
            'slug' => 'financial-literacy-ebook',
            'price' => 8000,
            'sale_price' => 5000,
            'type' => 'digital',
            'order' => 20,
            'description' => 'Smart money management guide for Nigerian entrepreneurs — budgeting, saving, investing, and building wealth.',
            'selling_points' => '<h2>Master Your Money, Build Your Future</h2><p>Written for the Nigerian economy. Includes budget template + net worth tracker spreadsheets.</p><ul><li>Budgeting that works with irregular income</li><li>Where to save and invest in Nigeria</li><li>How to separate business and personal finances</li><li>Debt payoff strategies</li><li>Building multiple income streams</li></ul>'
        ],
        [
            'title' => 'Business Automation Playbook',
            'slug' => 'automation-playbook',
            'price' => 18000,
            'type' => 'digital',
            'order' => 21,
            'description' => 'Stop doing manual work. Automate your email, WhatsApp, social media, CRM, and payments.',
            'selling_points' => '<h2>Work Less, Earn More</h2><p>Nigerian business owners waste 15+ hours per week on manual tasks. This playbook automates everything.</p><ul><li>Email automation workflows</li><li>WhatsApp broadcast + chatbot</li><li>Social media scheduling</li><li>CRM and lead tracking</li><li>Automated invoicing via Paystack</li><li>Order fulfillment automation</li></ul>'
        ],
        [
            'title' => 'WordPress Setup Guide',
            'slug' => 'wp-setup-guide',
            'price' => 8000,
            'sale_price' => 5000,
            'type' => 'digital',
            'order' => 22,
            'description' => 'Launch your WordPress site in under 2 hours — no technical skills required.',
            'selling_points' => '<h2>Get Online Fast</h2><p>Domain, hosting, WordPress install, theme, plugins, content, SEO — all in plain English.</p><ul><li>Best Nigerian hosting providers compared</li><li>One-click WordPress installation</li><li>Free theme recommendations</li><li>Essential free plugins</li><li>SEO and speed optimisation</li><li>Launch checklist</li></ul>'
        ],
        [
            'title' => 'Shopify Launch Checklist',
            'slug' => 'shopify-launch-checklist',
            'price' => 8000,
            'sale_price' => 5000,
            'type' => 'digital',
            'order' => 23,
            'description' => 'Everything you need to launch a successful Shopify store in Nigeria.',
            'selling_points' => '<h2>Launch Your Store With Confidence</h2><p>Complete Shopify launch system tailored for the Nigerian market.</p><ul><li>Store setup (theme, shipping, taxes)</li><li>Paystack/Flutterwave integration</li><li>Product listing optimised for conversions</li><li>Pre-launch marketing setup</li><li>Launch day checklist</li><li>First 30-day growth plan</li></ul>'
        ],
    ];

    foreach ($newProducts as $data) {
        $exists = \Illuminate\Support\Facades\DB::table('products')->where('slug', $data['slug'])->first();
        if ($exists) {
            $output .= "Already exists: {$data['title']}<br>";
            $result = $gen->generate($data['slug']);
            $output .= "→ " . ($result['success'] ? "Regenerated" : "FAILED: " . $result['message']) . "<br>";
            continue;
        }

        \Illuminate\Support\Facades\DB::table('products')->insertGetId([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'full_description' => $data['selling_points'],
            'short_description' => $data['description'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'file_path' => 'uploads/products/files/' . $data['slug'] . '.html',
            'type' => $data['type'] ?? 'digital',
            'is_active' => 1,
            'order' => $data['order'] ?? 99,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $gen->generate($data['slug']);
        $output .= ($result['success'] ? "Created: {$data['title']}" : "FAILED: {$data['title']}") . "<br>";
        $output .= "File: " . $result['file_path'] . "<br>";
    }

    $output .= "<br>Done! Visit store to see all products.";
    return $output;
});

// Public product generator (with key for minimal security)
Route::get('/generate-product/{slug}', function ($slug) {
    $key = request('key', '');
    if ($key !== 'joala2024') {
        return "Invalid key";
    }
    $gen = new \App\Services\ProductFileGenerator;
    $result = $gen->generate($slug);
    if ($result['success']) {
        return "✅ " . $result['message'];
    }
    return "❌ " . $result['message'];
});



// Public Landing Pages (outside admin middleware)
Route::get('/l/{slug}', [MarketingController::class, 'showLandingPage'])->name('landing.page');
Route::post('/l/{slug}/submit', [MarketingController::class, 'submitLead'])->name('landing.page.submit');

// Migrations Route (with secret key for security)
Route::get('/migrate', [MarketingController::class, 'runMigrations'])->name('migrate.run');
Route::post('/migrate', [MarketingController::class, 'runMigrations'])->name('migrate.run');
Route::get('/migrate-automation', [MarketingController::class, 'migrateAutomation'])->name('migrate.automation');

// Newsletter Routes
Route::post('/newsletter/subscribe', [MarketingController::class, 'newsletterSubscribe'])->name('newsletter.subscribe');

// Affiliate Registration Routes
Route::get('/affiliate', [App\Http\Controllers\Front\AffiliateController::class, 'showRegister'])->name('affiliate.register');
Route::get('/affiliate/register', [App\Http\Controllers\Front\AffiliateController::class, 'showRegister'])->name('affiliate.register.form');
Route::post('/affiliate/register', [App\Http\Controllers\Front\AffiliateController::class, 'processRegister'])->name('affiliate.register.submit');
Route::get('/affiliate/dashboard', [App\Http\Controllers\Front\AffiliateController::class, 'dashboard'])->name('affiliate.dashboard');
Route::get('/affiliate/login', [App\Http\Controllers\Front\AffiliateController::class, 'showLogin'])->name('affiliate.login');
Route::post('/affiliate/login', [App\Http\Controllers\Front\AffiliateController::class, 'processLogin'])->name('affiliate.login.submit');
Route::get('/affiliate/logout', [App\Http\Controllers\Front\AffiliateController::class, 'logout'])->name('affiliate.logout');
Route::get('/affiliate/links', [App\Http\Controllers\Front\AffiliateController::class, 'links'])->name('affiliate.links');
Route::get('/affiliate/payouts', [App\Http\Controllers\Front\AffiliateController::class, 'payouts'])->name('affiliate.payouts');
Route::get('/affiliate/settings', [App\Http\Controllers\Front\AffiliateController::class, 'settings'])->name('affiliate.settings');

// Generate affiliate link route
Route::get('/ref/{code}', function($code) {
    try {
        $pdo = marketing_pdo();
        
        // Find affiliate by referral code
        $stmt = $pdo->prepare("SELECT id FROM affiliates WHERE referral_code = ? AND status = 'active'");
        $stmt->execute([$code]);
        $affiliate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($affiliate) {
            // Log the click
            $clickStmt = $pdo->prepare("UPDATE affiliates SET total_clicks = total_clicks + 1 WHERE id = ?");
            $clickStmt->execute([$affiliate['id']]);
            
            // Redirect to main page or landing page
            return redirect('/?ref=' . $code);
        }
        
        return redirect('/');
    } catch (\Exception $e) {
        return redirect('/');
    }
})->name('affiliate.track');

// Refund Request Route
Route::get('/refund/request/{orderId?}', function($orderId = null) {
    return view('front.refund.request', compact('orderId'));
})->name('refund.request');

Route::post('/refund/request', function(\Illuminate\Http\Request $request) {
    try {
        $pdo = marketing_pdo();
        
        // Get order amount if order_id provided
        $amount = null;
        if ($request->order_id) {
            $order = $pdo->query("SELECT final_amount FROM orders WHERE id = " . intval($request->order_id))->fetch(PDO::FETCH_ASSOC);
            if ($order) {
                $amount = $order['final_amount'];
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO refund_requests (order_id, user_email, reason, status, amount, created_at) VALUES (?, ?, ?, 'pending', ?, NOW())");
        $stmt->execute([$request->order_id ?? null, $request->email, $request->reason, $amount]);
        
        return back()->with('success', 'Your refund request has been submitted!');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage())->withInput();
    }
})->name('refund.request.submit');
Route::get('/newsletter/confirm/{token}', [MarketingController::class, 'newsletterConfirm'])->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe/{token}', [MarketingController::class, 'newsletterUnsubscribe'])->name('newsletter.unsubscribe');

// Email Open Tracking (public for tracking pixels)
Route::get('/m/{emailQueue}', [MarketingController::class, 'trackOpen'])->name('marketing.track.open');
Route::get('/mc/{emailQueue}', [MarketingController::class, 'trackClick'])->name('marketing.track.click');
Route::get('/click/{emailQueueId}', [MarketingController::class, 'trackAndRedirect'])->name('marketing.track.redirect');

// A/B Test Tracking (public)
Route::get('/ab/open/{test}/{variant}', [MarketingController::class, 'abTestsRecordOpen'])->name('ab.track.open');
Route::get('/ab/click/{test}/{variant}', [MarketingController::class, 'abTestsRecordClick'])->name('ab.track.click');

Route::get('/test-routes-works', function () { return 'TEST OK - Routes are working!'; });

Route::get('/test-email-create', function () {
    return view('admin.marketing.email_templates.create');
});

// WhatsApp Marketing Bundle Product
Route::get('/setup-whatsapp-product', function () {
    if (\Illuminate\Support\Facades\DB::table('products')->where('slug', 'whatsapp-marketing-bundle')->exists()) {
        return 'WhatsApp Marketing Bundle already exists! <a href="/store">View Store</a>';
    }
    $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'title' => 'WhatsApp Marketing Bundle',
        'slug' => 'whatsapp-marketing-bundle',
        'short_description' => '48 ready-to-send WhatsApp templates for business',
        'description' => 'WhatsApp Marketing Bundle - 48 templates including broadcast sequences, auto-replies, status templates, chatbot flows, and order fulfillment sequences.',
        'type' => 'ebook',
        'price' => 15000.00,
        'sale_price' => 8000.00,
        'file_path' => 'uploads/products/files/whatsapp-marketing-bundle.html',
        'image' => '/uploads/products/whatsapp-marketing-bundle-cover.svg',
        'is_active' => 1,
        'is_featured' => 1,
        'order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    return 'WhatsApp Marketing Bundle created! ID: ' . $id . ' <a href="/store">View Store</a>';
});

Route::get('/setup-product', function () {
    if (\Illuminate\Support\Facades\DB::table('products')->where('title', 'LIKE', '%Email Sequence Templates%')->exists()) {
        return 'Product already exists! <a href="/store">View Store</a>';
    }
    $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'title' => 'Email Sequence Templates Pack',
        'slug' => 'email-sequence-templates-pack',
        'short_description' => '6 ready-to-use email sequences with 24 templates',
        'description' => 'Email Sequence Templates Pack - 6 Email Sequences (24 Templates)',
        'type' => 'ebook',
        'price' => 15000.00,
        'sale_price' => 12000.00,
        'file_path' => 'uploads/products/files/email-sequence-templates-pack.pdf',
        'is_active' => 1,
        'is_featured' => 1,
        'order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    return 'Product created! ID: ' . $id . ' <a href="/store">View Store</a>';
});

// Fix product file path
Route::get('/fix-product-file', function () {
    $product = \Illuminate\Support\Facades\DB::table('products')
        ->where('title', 'LIKE', '%Email Sequence Templates%')
        ->first();
    if ($product) {
        \Illuminate\Support\Facades\DB::table('products')
            ->where('id', $product->id)
            ->update(['file_path' => 'uploads/products/files/email-sequence-templates-pack.pdf']);
        return 'File path fixed!';
    }
    return 'Product not found';
});

// Check product
Route::get('/check-product', function () {
    try {
        $product = \Illuminate\Support\Facades\DB::table('products')
            ->where('title', 'LIKE', '%Email Sequence Templates%')
            ->first();
        return json_encode($product);
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

Route::get('/show-product', function () {
    $p = \Illuminate\Support\Facades\DB::table('products')->where('slug', 'email-sequence-templates-pack')->first();
    return "Title: $p->title<br>File: $p->file_path<br>Price: $p->price<br>Sale: $p->sale_price";
});

Route::get('/debug-store-show', function () {
    $p = \Illuminate\Support\Facades\DB::table('products')->where('slug', 'email-sequence-templates-pack')->first();
    return "Title: $p->title<br>File: $p->file_path<br>Price: $p->price<br>Sale: $p->sale_price";
});

Route::get('/update-ecommerce-description', function () {
    $key = request('key', '');
    if ($key !== 'joala2024') { return "Invalid key"; }

    $newDescription = '<h2 class="text-2xl font-bold text-slate-900 mb-4">Launch Your Online Store in Days, Not Months</h2>
<p class="text-lg text-slate-600 mb-6">A fully-featured Laravel e-commerce platform that handles everything from product management to payment processing. Built with modern best practices and ready to customize.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Core Features</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Product Management</strong> - Unlimited products, categories, tags, and variations</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Shopping Cart</strong> - Persistent cart with wishlist functionality</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Paystack Integration</strong> - Nigerian payment gateway ready out of the box</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Order Tracking</strong> - Complete order management for admin and customers</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Email Notifications</strong> - Automated emails for orders, shipping, and more</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Coupon System</strong> - Percentage and fixed amount discounts</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Physical & Digital Products</strong> - Sell physical goods and digital downloads from one store</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Shipping Across Africa & Worldwide</strong> - Multi-zone shipping with rate calculation and tracking</span></li>
</ul>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Technical Stack</h3>
<p class="text-slate-600 mb-4">Built with Laravel 10, Tailwind CSS, and MySQL. Mobile-responsive by default.</p>

<div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg mb-6">
<p class="text-emerald-800 font-medium"><i class="fas fa-rocket mr-2"></i>Get your online store running today with minimal configuration.</p>
</div>

<div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg">
<p class="text-amber-800 font-bold mb-2"><i class="fas fa-gift mr-2"></i>BONUS: Free Deployment + 2 Free Edits</p>
<p class="text-amber-700">Purchase the E-commerce Starter Kit and we will deploy your store on your domain for free and make 2 additional customizations of your choice at no extra cost. Valued at ₦150,000.</p>
</div>';

    \Illuminate\Support\Facades\DB::table('products')
        ->where('slug', 'e-commerce-starter-kit')
        ->update(['full_description' => $newDescription]);

    $product = \Illuminate\Support\Facades\DB::table('products')->where('slug', 'e-commerce-starter-kit')->first();

    return "<h2>Updated!</h2>
            <b>Title:</b> {$product->title}<br>
            <b>Slug:</b> {$product->slug}<br>
            <b>Description updated.</b><br><br>
            <a href='/store/e-commerce-starter-kit' target='_blank'>View Store Page</a>";
});

// Create nurturing sequence for lead magnet
Route::get('/create-nurture-sequence', function () {
    try {
        $exists = \Illuminate\Support\Facades\DB::table('email_sequences')
            ->where('name', 'LIKE', '%Nurture%')
            ->first();
        
        if ($exists) {
            return response()->json(['status' => 'exists', 'id' => $exists->id]);
        }
        
        $seqId = \Illuminate\Support\Facades\DB::table('email_sequences')->insertGetId([
            'name' => 'Nurture - Email Checklist Lead Magnet',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $steps = [
            ['subject' => 'Welcome! Here is your Email Marketing Checklist', 'body' => 'Thanks for subscribing! Attached is your free PDF checklist. Print it out and start checking off each item.', 'delay_days' => 0],
            ['subject' => '3 Mistakes Killing Your Email Open Rates', 'body' => 'Most businesses make these 3 mistakes. Here\'s how to fix them...', 'delay_days' => 2],
            ['subject' => 'Real Results from This Simple Change', 'body' => 'A client implemented one change and saw 40% increase in opens. Here\'s what they did...', 'delay_days' => 5],
            ['subject' => 'Quick question about your business', 'body' => 'I wanted to check in - what\'s your biggest challenge with email marketing right now?', 'delay_days' => 8],
        ];
        
        foreach ($steps as $i => $step) {
            \Illuminate\Support\Facades\DB::table('sequence_steps')->insert([
                'sequence_id' => $seqId,
                'subject' => $step['subject'],
                'body' => $step['body'],
                'delay_days' => $step['delay_days'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        return response()->json(['status' => 'created', 'id' => $seqId, 'steps' => count($steps)]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Create post-purchase sequence
Route::get('/create-email-sequence', function () {
    try {
        // Check if sequence exists with steps
        $exists = \Illuminate\Support\Facades\DB::table('email_sequences')
            ->where('name', 'LIKE', '%Post Purchase%')
            ->first();
        
        if ($exists) {
            $steps = \Illuminate\Support\Facades\DB::table('sequence_steps')
                ->where('sequence_id', $exists->id)
                ->get();
            
            // If no steps, add them
            if ($steps->isEmpty()) {
                $stepsData = [
                    ['subject' => 'Thank you for your purchase!', 'body' => 'Thanks! Your download: [DOWNLOAD_LINK]', 'delay_days' => 0],
                    ['subject' => 'How to use templates', 'body' => 'Tips for using templates...', 'delay_days' => 1],
                    ['subject' => 'Quick question', 'body' => 'Any questions?', 'delay_days' => 3],
                    ['subject' => 'Special offer', 'body' => 'Special discount for you...', 'delay_days' => 7],
                ];
                
                foreach ($stepsData as $step) {
                    \Illuminate\Support\Facades\DB::table('sequence_steps')->insert([
                        'sequence_id' => $exists->id,
                        'subject' => $step['subject'],
                        'body' => $step['body'],
                        'delay_days' => $step['delay_days'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                return response()->json(['status' => 'steps_added', 'id' => $exists->id, 'steps_count' => count($stepsData)]);
            }
            
            return response()->json([
                'status' => 'exists',
                'id' => $exists->id,
                'name' => $exists->name,
                'steps' => $steps
            ]);
        }
        
        // Create new sequence
        $seqId = \Illuminate\Support\Facades\DB::table('email_sequences')->insertGetId([
            'name' => 'Post Purchase - Email Templates',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $stepsData = [
            ['subject' => 'Thank you for your purchase!', 'body' => 'Thanks! Download: [DOWNLOAD_LINK]', 'delay_days' => 0],
            ['subject' => 'How to use templates', 'body' => 'Tips for using templates...', 'delay_days' => 1],
            ['subject' => 'Quick question', 'body' => 'Any questions?', 'delay_days' => 3],
            ['subject' => 'Special offer', 'body' => 'Special discount for you...', 'delay_days' => 7],
        ];
        
        foreach ($stepsData as $step) {
            \Illuminate\Support\Facades\DB::table('sequence_steps')->insert([
                'sequence_id' => $seqId,
                'subject' => $step['subject'],
                'body' => $step['body'],
                'delay_days' => $step['delay_days'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        return response()->json(['status' => 'created', 'id' => $seqId, 'steps_count' => count($stepsData)]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/downloads/{file}', function ($file) {
    $path = public_path('uploads/downloads/' . $file);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->download($path);
})->where('file', '[\w\-.]+');

// Course Creator Kit Product
Route::get('/setup-course-kit', function () {
    try {
        $existing = \Illuminate\Support\Facades\DB::table('products')->where('slug', 'course-creator-kit')->first();
        if ($existing) {
            return 'Course Creator Kit already exists! ID: ' . $existing->id;
        }
        $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
            'title' => 'Course Creator Kit',
            'slug' => 'course-creator-kit',
            'short_description' => '50+ templates for course creators',
            'description' => 'Complete launch system for online course creators. Includes landing pages, 10-email launch sequence, sales pages, student onboarding, testimonial collection, and pricing calculator.',
            'type' => 'ebook',
            'price' => 35000.00,
            'sale_price' => 18000.00,
            'file_path' => 'uploads/products/files/course-creator-kit.html',
            'image' => '/uploads/products/course-creator-kit-cover.svg',
            'is_active' => 1,
            'is_featured' => 1,
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return 'Course Creator Kit created! ID: ' . $id . ' <a href="/store">View Store</a>';
    } catch (\Exception $e) {
        return 'ERROR: ' . $e->getMessage();
    }
});

// Setup Course Creator post-purchase sequence
Route::get('/setup-course-sequence', function () {
    try {
        $seq = \App\Models\EmailSequence::updateOrCreate(
            ['name' => 'Course Creator Kit'],
            ['name' => 'Course Creator Kit', 'description' => 'Post-purchase for Course Creator Kit', 'trigger_type' => 'post_purchase', 'is_active' => true]
        );
        \Illuminate\Support\Facades\DB::table('sequence_steps')->where('sequence_id', $seq->id)->delete();
        \Illuminate\Support\Facades\DB::table('sequence_steps')->insert(['sequence_id' => $seq->id, 'step_order' => 1, 'delay_days' => 0, 'subject' => 'Your Course Creator Kit is ready!', 'body' => "Hi {{name}},\n\nYour download: https://www.joala.com.ng/email-templates\n\nInside: 50+ templates for launching your course!\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()]);
        \Illuminate\Support\Facades\DB::table('sequence_steps')->insert(['sequence_id' => $seq->id, 'step_order' => 2, 'delay_days' => 3, 'subject' => 'Quick question', 'body' => "Hi {{name}},\n\nWhat course will you create first? Reply and tell me!\n\nJome", 'created_at' => now(), 'updated_at' => now()]);
        return 'Course sequence created! ID: ' . $seq->id;
    } catch (\Exception $e) { return 'ERROR: ' . $e->getMessage(); }
});

// Setup Local Business post-purchase sequence
Route::get('/setup-local-sequence', function () {
    try {
        $seq = \App\Models\EmailSequence::updateOrCreate(
            ['name' => 'Local Business Digital Kit'],
            ['name' => 'Local Business Digital Kit', 'description' => 'Post-purchase for Local Business Kit', 'trigger_type' => 'post_purchase', 'is_active' => true]
        );
        \Illuminate\Support\Facades\DB::table('sequence_steps')->where('sequence_id', $seq->id)->delete();
        \Illuminate\Support\Facades\DB::table('sequence_steps')->insert(['sequence_id' => $seq->id, 'step_order' => 1, 'delay_days' => 0, 'subject' => 'Your Local Business Kit is ready!', 'body' => "Hi {{name}},\n\nYour download: https://www.joala.com.ng/email-templates\n\n40+ templates for your business!\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()]);
        return 'Local sequence created! ID: ' . $seq->id;
    } catch (\Exception $e) { return 'ERROR: ' . $e->getMessage(); }
});

// Setup SaaS post-purchase sequence
Route::get('/setup-saas-sequence', function () {
    try {
        $seq = \App\Models\EmailSequence::updateOrCreate(
            ['name' => 'SaaS Starter Kit'],
            ['name' => 'SaaS Starter Kit', 'description' => 'Post-purchase for SaaS Starter Kit', 'trigger_type' => 'post_purchase', 'is_active' => true]
        );
        \Illuminate\Support\Facades\DB::table('sequence_steps')->where('sequence_id', $seq->id)->delete();
        \Illuminate\Support\Facades\DB::table('sequence_steps')->insert(['sequence_id' => $seq->id, 'step_order' => 1, 'delay_days' => 0, 'subject' => 'Your SaaS Starter Kit is ready!', 'body' => "Hi {{name}},\n\nYour download: https://www.joala.com.ng/email-templates\n\nComplete Laravel SaaS template!\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()]);
        return 'SaaS sequence created! ID: ' . $seq->id;
    } catch (\Exception $e) { return 'ERROR: ' . $e->getMessage(); }
});

// Local Business Digital Kit Product
Route::get('/setup-local-business-kit', function () {
    try {
        $existing = \Illuminate\Support\Facades\DB::table('products')->where('slug', 'local-business-digital-kit')->first();
        if ($existing) {
            return 'Local Business Digital Kit already exists! ID: ' . $existing->id;
        }
        $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
            'title' => 'Local Business Digital Kit',
            'slug' => 'local-business-digital-kit',
            'short_description' => 'Digital tools for Nigerian shops, clinics, salons & traders',
            'description' => '40+ templates: WhatsApp broadcasts, SMS scripts, receipt templates, Google Business setup, loyalty program, referral system.',
            'type' => 'ebook',
            'price' => 25000.00,
            'sale_price' => 12000.00,
            'file_path' => 'uploads/products/files/local-business-digital-kit.html',
            'image' => '/uploads/products/local-business-digital-kit-cover.svg',
            'is_active' => 1,
            'is_featured' => 1,
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return 'Local Business Digital Kit created! ID: ' . $id . ' <a href="/store">View Store</a>';
    } catch (\Exception $e) {
        return 'ERROR: ' . $e->getMessage();
    }
});

// SaaS Starter Kit Product
Route::get('/setup-saas-kit', function () {
    try {
        $existing = \Illuminate\Support\Facades\DB::table('products')->where('slug', 'saas-starter-kit')->first();
        if ($existing) {
            return 'SaaS Starter Kit already exists! ID: ' . $existing->id;
        }
        $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
            'title' => 'SaaS Starter Kit',
            'slug' => 'saas-starter-kit',
            'short_description' => 'Complete Laravel template to launch your SaaS',
            'description' => 'Complete Laravel SaaS template with auth, payments (Paystack), subscription billing, admin dashboard, email marketing, and API.',
            'type' => 'ebook',
            'price' => 85000.00,
            'sale_price' => 45000.00,
            'file_path' => 'uploads/products/files/saas-starter-kit.html',
            'image' => '/uploads/products/saas-starter-kit-cover.svg',
            'is_active' => 1,
            'is_featured' => 1,
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return 'SaaS Starter Kit created! ID: ' . $id . ' <a href="/store">View Store</a>';
    } catch (\Exception $e) {
        return 'ERROR: ' . $e->getMessage();
    }
});Route::get('/wordpress-starter-kit', function() {
    return view('front.wordpress-starter-kit');
})->name('wordpress.starter.kit');
Route::get('/freelancer-toolkit', function() {
    return view('front.freelancer-toolkit');
})->name('freelancer.toolkit');
Route::get('/instagram-growth-system', function() {
    return view('front.instagram-growth-system');
})->name('instagram.growth.system');
Route::get('/nigerian-business-digital-kit', function() {
    return view('front.nigerian-business-digital-kit');
})->name('nigerian.business.digital.kit');
Route::get('/restaurant-pos-kit', function() {
    return view('front.restaurant-pos-kit');
})->name('restaurant.pos.kit');
Route::get('/school-management-system', function() {
    return view('front.school-management-system');
})->name('school.management.system');
Route::get('/real-estate-property-kit', function() {
    return view('front.real-estate-property-kit');
})->name('real.estate.property.kit');
Route::get('/ecommerce-starter-kit', function() {
    return view('front.ecommerce-starter-kit');
})->name('ecommerce.starter.kit');

// WordPress Starter Kit
Route::get('/setup-wp-kit', function () {
    if (\Illuminate\Support\Facades\DB::table('products')->where('slug', 'wordpress-starter-kit')->exists()) return 'Exists';
    $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'title' => 'WordPress Starter Kit',
        'slug' => 'wordpress-starter-kit',
        'short_description' => 'Everything you need for a professional WordPress site',
        'description' => 'WordPress Starter Kit',
        'type' => 'template', 'price' => 15000, 'sale_price' => 12000,
        'file_path' => 'uploads/products/files/wordpress-starter-kit.html',
        'is_active' => 1, 'is_featured' => 1, 'created_at' => now(), 'updated_at' => now()
    ]);
    return 'WP Kit Created: ' . $id;
});

// Freelancer Toolkit
Route::get('/setup-freelancer-kit', function () {
    if (\Illuminate\Support\Facades\DB::table('products')->where('slug', 'freelancer-toolkit')->exists()) return 'Exists';
    $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'title' => 'Freelancer Toolkit',
        'slug' => 'freelancer-toolkit',
        'short_description' => 'Get high-paying clients',
        'description' => 'Freelancer Toolkit',
        'type' => 'ebook', 'price' => 25000, 'sale_price' => 15000,
        'file_path' => 'uploads/products/files/freelancer-toolkit.html',
        'is_active' => 1, 'is_featured' => 1, 'created_at' => now(), 'updated_at' => now()
    ]);
    return 'Freelancer Kit Created: ' . $id;
});

// Instagram Growth System
Route::get('/setup-ig-kit', function () {
    if (\Illuminate\Support\Facades\DB::table('products')->where('slug', 'instagram-growth-system')->exists()) return 'Exists';
    $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'title' => 'Instagram Growth System',
        'slug' => 'instagram-growth-system',
        'short_description' => 'Grow your Instagram following',
        'description' => 'Instagram Growth System',
        'type' => 'ebook', 'price' => 20000, 'sale_price' => 12000,
        'file_path' => 'uploads/products/files/instagram-growth-system.html',
        'is_active' => 1, 'is_featured' => 1, 'created_at' => now(), 'updated_at' => now()
    ]);
    return 'IG Kit Created: ' . $id;
});

// Nigerian Business Digital Kit
Route::get('/setup-ng-kit', function () {
    if (\Illuminate\Support\Facades\DB::table('products')->where('slug', 'nigerian-business-digital-kit')->exists()) return 'Exists';
    $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'title' => 'Nigerian Business Digital Kit',
        'slug' => 'nigerian-business-digital-kit',
        'short_description' => 'Digital tools for Nigerian businesses',
        'description' => 'Nigerian Business Digital Kit',
        'type' => 'ebook', 'price' => 35000, 'sale_price' => 25000,
        'file_path' => 'uploads/products/files/nigerian-business-kit.html',
        'is_active' => 1, 'is_featured' => 1, 'created_at' => now(), 'updated_at' => now()
    ]);
    return 'NG Kit Created: ' . $id;
});

// Church Website Kit
Route::get('/setup-church-kit', function () {
    if (\Illuminate\Support\Facades\DB::table('products')->where('slug', 'church-website-kit')->exists()) return 'Exists';
    $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'title' => 'Church & Organization Website Kit',
        'slug' => 'church-website-kit',
        'short_description' => 'Complete WordPress theme for churches',
        'description' => 'Church Website Kit',
        'type' => 'template', 'price' => 25000, 'sale_price' => 18000,
        'file_path' => 'uploads/products/files/church-website-kit.html',
        'is_active' => 1, 'is_featured' => 1, 'created_at' => now(), 'updated_at' => now()
    ]);
    return 'Church Kit Created: ' . $id;
});

// Restaurant POS Kit
Route::get('/setup-pos-kit', function () {
    if (\Illuminate\Support\Facades\DB::table('products')->where('slug', 'restaurant-pos-kit')->exists()) return 'Exists';
    $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'title' => 'Restaurant POS Kit',
        'slug' => 'restaurant-pos-kit',
        'short_description' => 'Complete ordering system for restaurants',
        'description' => 'Restaurant POS Kit',
        'type' => 'template', 'price' => 50000, 'sale_price' => 35000,
        'file_path' => 'uploads/products/files/restaurant-pos-kit.html',
        'is_active' => 1, 'is_featured' => 1, 'created_at' => now(), 'updated_at' => now()
    ]);
    return 'POS Kit Created: ' . $id;
});

// School Management System
Route::get('/setup-school-kit', function () {
    if (\Illuminate\Support\Facades\DB::table('products')->where('slug', 'school-management-system')->exists()) return 'Exists';
    $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'title' => 'School Management System',
        'slug' => 'school-management-system',
        'short_description' => 'Complete software for schools',
        'description' => 'School Management System',
        'type' => 'template', 'price' => 65000, 'sale_price' => 45000,
        'file_path' => 'uploads/products/files/school-management-system.html',
        'is_active' => 1, 'is_featured' => 1, 'created_at' => now(), 'updated_at' => now()
    ]);
    return 'School Kit Created: ' . $id;
});

// Real Estate Property Kit
Route::get('/setup-real-kit', function () {
    if (\Illuminate\Support\Facades\DB::table('products')->where('slug', 'real-estate-property-kit')->exists()) return 'Exists';
    $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'title' => 'Real Estate Property Kit',
        'slug' => 'real-estate-property-kit',
        'short_description' => 'Complete system for property agents',
        'description' => 'Real Estate Property Kit',
        'type' => 'template', 'price' => 50000, 'sale_price' => 35000,
        'file_path' => 'uploads/products/files/real-estate-property-kit.html',
        'is_active' => 1, 'is_featured' => 1, 'created_at' => now(), 'updated_at' => now()
    ]);
    return 'Real Estate Kit Created: ' . $id;
});

// E-commerce Starter Kit
Route::get('/setup-ecom-kit', function () {
    if (\Illuminate\Support\Facades\DB::table('products')->where('slug', 'ecommerce-starter-kit')->exists()) return 'Exists';
    $id = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'title' => 'E-commerce Starter Kit',
        'slug' => 'ecommerce-starter-kit',
        'short_description' => 'Complete Laravel e-commerce template',
        'description' => 'E-commerce Starter Kit',
        'type' => 'template', 'price' => 85000, 'sale_price' => 55000,
        'file_path' => 'uploads/products/files/ecommerce-starter-kit.html',
        'is_active' => 1, 'is_featured' => 1, 'created_at' => now(), 'updated_at' => now()
    ]);
    return 'E-com Kit Created: ' . $id;
});

// Git Pull Route
Route::get('/setup-git-pull', function () {
    chdir('/home/joalacom/public_html/portfolio');
    exec('git pull origin master 2>&1', $output);
    return implode("\n", $output);
});

// Check Email Sequences
Route::get('/check-sequences', function () {
    $seqs = DB::table('email_sequences')->orderBy('id')->get(['id','name']);
    $html = "<h1>Email Sequences</h1><ul>";
    foreach($seqs as $s) {
        $steps = DB::table('sequence_steps')->where('sequence_id', $s->id)->count();
        $html .= "<li>{$s->id}. {$s->name} - $steps steps</li>";
    }
    $html .= "</ul>Total: " . $seqs->count() . " sequences";
    return $html;
});

// Email Sequence Setup - Freelancer
Route::get('/setup-freelancer-sequence', function () {
    if (DB::table('email_sequences')->where('name','LIKE','%Freelancer%')->exists()) return 'Exists';
    $seqId = DB::table('email_sequences')->insertGetId(['name'=>'Post-Purchase - Freelancer Toolkit','description'=>'Freelancer Toolkit buyers','is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('sequence_steps')->insert(['sequence_id'=>$seqId,'step_order'=>1,'delay_days'=>0,'subject'=>'Your Freelancer Toolkit is ready!','body'=>"Hi {{name}},\n\nThank you! Get it here: https://www.joala.com.ng/email-templates\n\nJome",'created_at'=>now(),'updated_at'=>now()]);
    DB::table('sequence_steps')->insert(['sequence_id'=>$seqId,'step_order'=>2,'delay_days'=>2,'subject'=>'Quick win for today','body'=>"Hi {{name}}\n\nPost on social media today!\n\nJome",'created_at'=>now(),'updated_at'=>now()]);
    return "Freelancer sequence created: $seqId";
});
// Instagram Sequence
Route::get('/setup-ig-sequence', function () {
    if (DB::table('email_sequences')->where('name','LIKE','%Instagram%')->exists()) return 'Exists';
    $seqId = DB::table('email_sequences')->insertGetId(['name'=>'Post-Purchase - Instagram Growth System','description'=>'Instagram Growth buyers','is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('sequence_steps')->insert(['sequence_id'=>$seqId,'step_order'=>1,'delay_days'=>0,'subject'=>'Your Instagram Growth System is ready!','body'=>"Hi {{name}}\n\nDownload: https://www.joala.com.ng/email-templates\n\nJome",'created_at'=>now(),'updated_at'=>now()]);
    return "IG sequence created: $seqId";
});
// Nigerian Business Sequence
Route::get('/setup-ng-sequence', function () {
    if (DB::table('email_sequences')->where('name','LIKE','%Nigerian%')->exists()) return 'Exists';
    $seqId = DB::table('email_sequences')->insertGetId(['name'=>'Post-Purchase - Nigerian Business Kit','description'=>'Nigerian Business buyers','is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('sequence_steps')->insert(['sequence_id'=>$seqId,'step_order'=>1,'delay_days'=>0,'subject'=>'Your Nigerian Business Digital Kit is ready!','body'=>"Hi {{name}}\n\nDownload: https://www.joala.com.ng/email-templates\n\nJome",'created_at'=>now(),'updated_at'=>now()]);
    return "NG sequence created: $seqId";
});
// WordPress Sequence
Route::get('/setup-wp-sequence', function () {
    if (DB::table('email_sequences')->where('name','LIKE','%WordPress%')->exists()) return 'Exists';
    $seqId = DB::table('email_sequences')->insertGetId(['name'=>'Post-Purchase - WordPress Starter Kit','description'=>'WordPress buyers','is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('sequence_steps')->insert(['sequence_id'=>$seqId,'step_order'=>1,'delay_days'=>0,'subject'=>'Your WordPress Starter Kit is ready!','body'=>"Hi {{name}}\n\nDownload: https://www.joala.com.ng/email-templates\n\nJome",'created_at'=>now(),'updated_at'=>now()]);
    return "WP sequence created: $seqId";
});
// Church Sequence
Route::get('/setup-church-sequence', function () {
    if (DB::table('email_sequences')->where('name','LIKE','%Church%')->exists()) return 'Exists';
    $seqId = DB::table('email_sequences')->insertGetId(['name'=>'Post-Purchase - Church Website Kit','description'=>'Church Kit buyers','is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('sequence_steps')->insert(['sequence_id'=>$seqId,'step_order'=>1,'delay_days'=>0,'subject'=>'Your Church Website Kit is ready!','body'=>"Hi {{name}}\n\nDownload: https://www.joala.com.ng/email-templates\n\nJome",'created_at'=>now(),'updated_at'=>now()]);
    return "Church sequence created: $seqId";
});
// POS Sequence
Route::get('/setup-pos-sequence', function () {
    if (DB::table('email_sequences')->where('name','LIKE','%POS%')->exists()) return 'Exists';
    $seqId = DB::table('email_sequences')->insertGetId(['name'=>'Post-Purchase - Restaurant POS Kit','description'=>'POS Kit buyers','is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('sequence_steps')->insert(['sequence_id'=>$seqId,'step_order'=>1,'delay_days'=>0,'subject'=>'Your Restaurant POS Kit is ready!','body'=>"Hi {{name}}\n\nDownload: https://www.joala.com.ng/email-templates\n\nJome",'created_at'=>now(),'updated_at'=>now()]);
    return "POS sequence created: $seqId";
});
// School Sequence
Route::get('/setup-school-sequence', function () {
    if (DB::table('email_sequences')->where('name','LIKE','%School%')->exists()) return 'Exists';
    $seqId = DB::table('email_sequences')->insertGetId(['name'=>'Post-Purchase - School Management System','description'=>'School buyers','is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('sequence_steps')->insert(['sequence_id'=>$seqId,'step_order'=>1,'delay_days'=>0,'subject'=>'Your School Management System is ready!','body'=>"Hi {{name}}\n\nDownload: https://www.joala.com.ng/email-templates\n\nJome",'created_at'=>now(),'updated_at'=>now()]);
    return "School sequence created: $seqId";
});
// Real Estate Sequence
Route::get('/setup-real-sequence', function () {
    if (DB::table('email_sequences')->where('name','LIKE','%Real%')->exists()) return 'Exists';
    $seqId = DB::table('email_sequences')->insertGetId(['name'=>'Post-Purchase - Real Estate Property Kit','description'=>'Real Estate buyers','is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('sequence_steps')->insert(['sequence_id'=>$seqId,'step_order'=>1,'delay_days'=>0,'subject'=>'Your Real Estate Property Kit is ready!','body'=>"Hi {{name}}\n\nDownload: https://www.joala.com.ng/email-templates\n\nJome",'created_at'=>now(),'updated_at'=>now()]);
    return "Real Estate sequence created: $seqId";
});
// E-commerce Sequence
Route::get('/setup-ecom-sequence', function () {
    if (DB::table('email_sequences')->where('name','LIKE','%E-commerce%')->exists()) return 'Exists';
    $seqId = DB::table('email_sequences')->insertGetId(['name'=>'Post-Purchase - E-commerce Starter Kit','description'=>'E-commerce buyers','is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('sequence_steps')->insert(['sequence_id'=>$seqId,'step_order'=>1,'delay_days'=>0,'subject'=>'Your E-commerce Starter Kit is ready!','body'=>"Hi {{name}}\n\nDownload: https://www.joala.com.ng/email-templates\n\nJome",'created_at'=>now(),'updated_at'=>now()]);
    return "E-commerce sequence created: $seqId";
});

// Landing Page - Freelancer Toolkit
Route::get('/freelancer-toolkit', function () {
    return view('front.landing-page')->with(['title'=>'Freelancer Toolkit','price'=>25000,'sale_price'=>15000,'desc'=>'Get high-paying clients with ease','icon'=>'fa-briefcase']);
});
// Landing Page - Instagram Growth
Route::get('/instagram-growth-system', function () {
    return view('front.landing-page')->with(['title'=>'Instagram Growth System','price'=>20000,'sale_price'=>12000,'desc'=>'Grow your Instagram following','icon'=>'fa-instagram']);
});
// Landing Page - Nigerian Business
Route::get('/nigerian-business-digital-kit', function () {
    return view('front.landing-page')->with(['title'=>'Nigerian Business Digital Kit','price'=>35000,'sale_price'=>25000,'desc'=>'Digital tools for Nigerian businesses','icon'=>'fa-store']);
});
// Landing Page - Church
Route::get('/church-website-kit', function () {
    return view('front.landing-page')->with(['title'=>'Church Website Kit','price'=>25000,'sale_price'=>18000,'desc'=>'Complete WordPress theme for churches','icon'=>'fa-church']);
});
// Landing Page - Restaurant POS
Route::get('/restaurant-pos-kit', function () {
    return view('front.landing-page')->with(['title'=>'Restaurant POS Kit','price'=>50000,'sale_price'=>35000,'desc'=>'Complete ordering system','icon'=>'fa-utensils']);
});
// Landing Page - School
Route::get('/school-management-system', function () {
    return view('front.landing-page')->with(['title'=>'School Management System','price'=>65000,'sale_price'=>45000,'desc'=>'Complete school software','icon'=>'fa-school']);
});
// Landing Page - Real Estate
Route::get('/real-estate-property-kit', function () {
    return view('front.landing-page')->with(['title'=>'Real Estate Property Kit','price'=>50000,'sale_price'=>35000,'desc'=>'Complete property system','icon'=>'fa-home']);
});
// Landing Page - E-commerce
Route::get('/ecommerce-starter-kit', function () {
    return view('front.landing-page')->with(['title'=>'E-commerce Starter Kit','price'=>85000,'sale_price'=>55000,'desc'=>'Full Laravel e-commerce','icon'=>'fa-shopping-cart']);
});

// Setup Landing Page Content
Route::get('/setup-landing-all', function () {
    $content = \Illuminate\Support\Facades\View::make('front.landing-page', ['title'=>'Test','price'=>10000,'sale_price'=>5000,'desc'=>'Test'])->render();
    return "Landing page view exists: " . (strpos($content, 'Test') ? 'YES' : 'NO');
});

// Git Pull - Execute on live
Route::get('/gitpull', function () {
    chdir('/home/joalacom/public_html/portfolio');
    $output = shell_exec('git pull origin master 2>&1');
    return $output ?: 'No output';
});

Route::get('/create-all-sequences', function() {
    $added=0;
    $seqs = [
        ['name'=>'Post-Purchase - WordPress Starter Kit','subject'=>'Your WordPress Starter Kit is ready!'],
        ['name'=>'Post-Purchase - Freelancer Toolkit','subject'=>'Your Freelancer Toolkit is ready!'],
        ['name'=>'Post-Purchase - Instagram Growth System','subject'=>'Your Instagram Growth System is ready!'],
        ['name'=>'Post-Purchase - Nigerian Business Digital Kit','subject'=>'Your Nigerian Business Digital Kit is ready!'],
        ['name'=>'Post-Purchase - Church Website Kit','subject'=>'Your Church Website Kit is ready!'],
        ['name'=>'Post-Purchase - Restaurant POS Kit','subject'=>'Your Restaurant POS Kit is ready!'],
        ['name'=>'Post-Purchase - School Management System','subject'=>'Your School Management System is ready!'],
        ['name'=>'Post-Purchase - Real Estate Property Kit','subject'=>'Your Real Estate Property Kit is ready!'],
        ['name'=>'Post-Purchase - E-commerce Starter Kit','subject'=>'Your E-commerce Starter Kit is ready!'],
    ];
    foreach($seqs as $s) {
        if(!\DB::table('email_sequences')->where('name',$s['name'])->exists()) {
            $sid = \DB::table('email_sequences')->insertGetId(['name'=>$s['name'],'description'=>$s['name'].' buyers','is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
            \DB::table('sequence_steps')->insert(['sequence_id'=>$sid,'step_order'=>1,'delay_days'=>0,'subject'=>$s['subject'],'body'=>'Thank you! Download: https://www.joala.com.ng/email-templates','created_at'=>now(),'updated_at'=>now()]);
            $added++;
        }
    }
    return "Added $added sequences!";
});

// Debug route to check product lookup
Route::get('/debug-product', function() {
    $slug = 'wordpress-starter-kit';
    $product = \App\Models\Product::where('slug', $slug)->where('is_active', true)->first();
    if ($product) {
        return "Product ID: " . $product->id . ", Title: " . $product->title;
    }
    return "Product not found!";
});

// Clear views cache - force recompilation
Route::get('/clear-views', function() {
    try {
        $viewsPath = base_path('../storage/framework/views');
        if (is_dir($viewsPath)) {
            $files = glob($viewsPath . '/*');
            $count = 0;
            foreach ($files as $file) {
                if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    unlink($file);
                    $count++;
                }
            }
            return "Deleted $count compiled view files!";
        }
        return "Views directory not found";
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// Cache clear route
Route::get('/clear-cache', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        return 'Cache cleared!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/admin/clear-all-cache', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        return 'All cache cleared!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/run-all-jobs', function() {
    if (!request()->has('key') || request()->get('key') !== 'joala2024') {
        return 'Access denied';
    }
    $output = [];
    $commands = [
        'email:process --limit=50',
        'tweets:process',
        'automation:execute',
        'leads:score --all',
        'abtests:process',
        'funnel:process-stages --limit=500',
        'segments:sync',
        'email:cleanup --days=30',
    ];
    foreach ($commands as $artisanCmd) {
        $output[] = "=== php artisan $artisanCmd ===";
        exec('cd /home/joalacom/public_html && php artisan ' . $artisanCmd . ' 2>&1', $out);
        $output[] = implode("\n", array_slice($out, 0, 10));
        $out = [];
    }
    return implode("\n", $output);
});

Route::get('/create-automation-logs-table', function() {
    if (!request()->has('key') || request()->get('key') !== 'joala2024') {
        return 'Access denied';
    }
    try {
        $pdo = DB::connection()->getPdo();
        $pdo->exec("CREATE TABLE IF NOT EXISTS automation_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rule_id INT NOT NULL,
            lead_id INT NOT NULL,
            action VARCHAR(100),
            executed_at DATETIME,
            INDEX idx_rule (rule_id),
            INDEX idx_lead (lead_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        return 'automation_logs table created/verified';
    } catch (\Exception $e) {
        return 'Table exists or error: ' . $e->getMessage();
    }
});

// Customer Portal Routes
Route::get('/customer/login', [CustomerController::class, 'showLogin'])->name('customer.login');
Route::post('/customer/login', [CustomerController::class, 'login']);
Route::get('/customer/register', [CustomerController::class, 'showRegister'])->name('customer.register');
Route::post('/customer/register', [CustomerController::class, 'register']);
Route::get('/customer/logout', [CustomerController::class, 'logout']);
Route::get('/customer/dashboard', [CustomerController::class, 'dashboard']);
Route::get('/customer/orders', [CustomerController::class, 'orders']);
Route::get('/customer/downloads', [CustomerController::class, 'downloads']);
Route::get('/customer/settings', [CustomerController::class, 'settings']);
Route::post('/customer/settings', [CustomerController::class, 'updateSettings']);
Route::get('/customer/subscriptions', [CustomerController::class, 'subscriptions']);
Route::get('/customer/referrals', [CustomerController::class, 'referrals']);
Route::get('/customer/achievements', [CustomerController::class, 'achievements']);
Route::get('/customer/affiliate', [CustomerController::class, 'affiliate']);
Route::get('/customer/refund', [CustomerController::class, 'refund']);
Route::post('/customer/refund', [CustomerController::class, 'refund']);
Route::get('/customer/notifications', [CustomerController::class, 'notifications']);
Route::get('/customer/notifications/{id}/read', [CustomerController::class, 'markNotificationRead']);
Route::get('/customer/my-courses', [CustomerController::class, 'myCourses']);
Route::get('/customer/my-learning', [CustomerController::class, 'myCourses']);
Route::get('/customer/courses', [CustomerController::class, 'myCourses']);
Route::get('/courses/{id}', [CustomerController::class, 'viewCourse']);
Route::post('/courses/progress', [CustomerController::class, 'updateProgress']);

// Create customer tables if not exist
Route::get('/create-customer-tables', function() {
    try {
        $pdo = DB::connection()->getPdo();
        
        // Add columns to course_enrollments
        try { $pdo->exec("ALTER TABLE course_enrollments ADD COLUMN progress INT DEFAULT 0"); } catch (\Exception $e) {}
        try { $pdo->exec("ALTER TABLE course_enrollments ADD COLUMN completed_at DATETIME DEFAULT NULL"); } catch (\Exception $e) {}
        
        // Recreate course_enrollments table if needed
        try {
            $pdo->exec("DROP TABLE IF EXISTS course_enrollments");
            $pdo->exec("CREATE TABLE course_enrollments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_email VARCHAR(255) NOT NULL,
                course_id INT NOT NULL,
                order_id INT,
                progress INT DEFAULT 0,
                enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                completed_at DATETIME DEFAULT NULL,
                INDEX idx_customer (customer_email),
                UNIQUE KEY unique_enrollment (customer_email, course_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
        
        // Create course_lessons table
        try {
            $pdo->exec("DROP TABLE IF EXISTS course_lessons");
            $pdo->exec("CREATE TABLE course_lessons (
                id INT AUTO_INCREMENT PRIMARY KEY,
                course_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                video_url VARCHAR(500),
                content TEXT,
                duration_minutes INT DEFAULT 0,
                lesson_order INT DEFAULT 0,
                is_published TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_course (course_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
        
        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Course Lesson Route
Route::get('/courses/{courseId}/lesson/{lessonId}', [CustomerController::class, 'viewLesson']);

// Certificate Route
Route::get('/certificate/{courseId}', [CustomerController::class, 'viewCertificate']);

// ========================
// NEWLY IMPLEMENTED ROUTES
// ========================

// Subscription Routes
Route::get('/subscribe/{planId}', [CustomerController::class, 'subscribeToPlan'])->name('subscribe.plan');
Route::get('/subscription/callback', [CustomerController::class, 'subscriptionCallback'])->name('subscription.callback');
Route::post('/subscription/cancel', [CustomerController::class, 'cancelSubscription'])->name('subscription.cancel');
Route::get('/customer/subscribe/{planId}', [CustomerController::class, 'subscribeToPlan']);

// Paystack Subscription Webhook
Route::post('/paystack/subscription-webhook', function (\Illuminate\Http\Request $request) {
    $payload = $request->all();
    $secret = \App\Models\Setting::get('paystack_secret_key');
    
    $signature = $request->header('x-paystack-signature');
    $expected = hash_hmac('sha512', $request->getContent(), $secret);
    
    if ($signature !== $expected) {
        return response('Invalid signature', 401);
    }
    
    $service = app(\App\Services\PaystackSubscriptionService::class);
    $result = $service->handleWebhook($payload);
    
    return response()->json($result);
});

// Course Lesson - Mark Complete
Route::post('/courses/lesson/complete', [CustomerController::class, 'markLessonComplete'])->name('courses.lesson.complete');

// Affiliate Payout Request
Route::post('/affiliate/request-payout', [\App\Http\Controllers\Front\AffiliateController::class, 'requestPayout'])->name('affiliate.payout.request');
Route::post('/affiliate/update-bank', [\App\Http\Controllers\Front\AffiliateController::class, 'updateBankDetails'])->name('affiliate.bank.update');

// Admin - Refund via Paystack
Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::post('/refunds/{id}/process-paystack', function($id) {
        $service = app(\App\Services\PaystackRefundService::class);
        $result = $service->processAdminRefund($id);
        if ($result['success']) {
            return back()->with('success', $result['message']);
        }
        return back()->with('error', $result['message']);
    })->name('admin.refunds.process-paystack');
});

// Admin - Affiliate Payout Management
Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/affiliates/payouts', function() {
        try {
            $pdo = marketing_pdo();
            $payouts = $pdo->query("SELECT ap.*, a.name as affiliate_name, a.email FROM affiliate_payouts ap JOIN affiliates a ON ap.affiliate_id = a.id ORDER BY ap.created_at DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC);
            return view('admin.affiliates.payouts', compact('payouts'));
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    })->name('admin.affiliates.payouts');
    
    Route::post('/affiliates/payouts/{id}/complete', function($id) {
        $service = app(\App\Services\AffiliateCommissionService::class);
        $result = $service->completePayout($id);
        return $result ? back()->with('success', 'Payout completed!') : back()->with('error', 'Failed to complete payout');
    })->name('admin.affiliates.payouts.complete');
    
    Route::post('/affiliates/payouts/{id}/reject', function(\Illuminate\Http\Request $request, $id) {
        $service = app(\App\Services\AffiliateCommissionService::class);
        $result = $service->rejectPayout($id, $request->reason ?? '');
        return $result ? back()->with('success', 'Payout rejected') : back()->with('error', 'Failed to reject');
    })->name('admin.affiliates.payouts.reject');
});

// Admin - Cart Abandonment Stats
Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/marketing/cart-abandonment', function() {
        $service = app(\App\Services\CartAbandonmentService::class);
        $stats = $service->getAbandonmentStats();
        
        try {
            $pdo = marketing_pdo();
            $orders = $pdo->query("SELECT * FROM orders WHERE is_cart_abandoned = 1 ORDER BY cart_abandoned_at DESC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $orders = [];
        }
        
        return view('admin.marketing.cart_abandonment.index', compact('stats', 'orders'));
    })->name('admin.marketing.cart-abandonment');
    
    Route::post('/marketing/cart-abandonment/process', function() {
        $service = app(\App\Services\CartAbandonmentService::class);
        $result = $service->detectAbandonedCarts(1);
        $checkoutResult = $service->detectAbandonedCheckouts(30);
        return back()->with('success', 'Processed ' . $result['marked'] . ' abandoned carts and ' . $checkoutResult['marked'] . ' abandoned checkouts.');
    })->name('admin.marketing.cart-abandonment.process');
});

// Admin - Email Campaign Broadcast Send
Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::post('/email/campaigns/{campaign}/send', function(\Illuminate\Http\Request $request, $id) {
        $broadcastService = app(\App\Services\EmailBroadcastService::class);
        
        try {
            $campaign = \App\Models\Campaign::findOrFail($id);
            
            $subject = $request->subject ?? $campaign->name;
            $body = $request->body ?? 'Default campaign email body';
            $scope = $request->scope ?? 'all';
            
            if ($scope === 'segment' && $request->segment_id) {
                $result = $broadcastService->sendToSegment($request->segment_id, $subject, $body);
            } elseif ($scope === 'lead') {
                $leads = \App\Models\Lead::where('status', 'active')->where('confirmed', true)->pluck('email')->toArray();
                $result = $broadcastService->sendBroadcast($subject, $body, $leads);
            } elseif ($scope === 'newsletter') {
                $result = $broadcastService->sendToNewsletterSubscribers($subject, $body);
            } else {
                return back()->with('error', 'Invalid scope selected');
            }
            
            $queueMethod = $request->queue ? 'Batched (via queue)' : 'Direct (immediate)';
            
            return back()->with('success', "Broadcast sent via {$queueMethod}. Sent: {$result['sent']}, Failed: {$result['failed']}");
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    })->name('admin.email.campaigns.send');
    
    Route::post('/email/broadcast/send', function(\Illuminate\Http\Request $request) {
        $broadcastService = app(\App\Services\EmailBroadcastService::class);
        
        $subject = $request->subject;
        $body = $request->body;
        $scope = $request->scope ?? 'all';
        
        if (!$subject || !$body) {
            return back()->with('error', 'Subject and body are required');
        }
        
        if ($scope === 'segment' && $request->segment_id) {
            $result = $broadcastService->sendToSegment($request->segment_id, $subject, $body);
        } elseif ($scope === 'newsletter') {
            $result = $broadcastService->sendToNewsletterSubscribers($subject, $body);
        } elseif ($scope === 'all') {
            $emails = \App\Models\Lead::where('status', 'active')->where('confirmed', true)->pluck('email')->toArray();
            $result = $broadcastService->sendBroadcast($subject, $body, $emails);
        } else {
            return back()->with('error', 'Invalid scope');
        }
        
        return back()->with('success', "Broadcast sent to {$result['sent']} recipients ({$result['failed']} failed)");
    })->name('admin.broadcast.send');
});

// One-time setup endpoint for creating new feature tables
Route::get('/setup-new-tables', function () {
    try {
        $pdo = db_pdo();
        $out = [];

        $pdo->exec("CREATE TABLE IF NOT EXISTS page_visits (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            url TEXT DEFAULT NULL,
            referer TEXT DEFAULT NULL,
            session_id VARCHAR(255) DEFAULT NULL,
            visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_session (session_id),
            INDEX idx_visited (visited_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $out[] = "page_visits table OK";

        $check = $pdo->query("SHOW COLUMNS FROM project_briefs LIKE 'is_read'")->fetch();
        if (!$check) {
            $pdo->exec("ALTER TABLE project_briefs ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER notes");
            $out[] = "project_briefs.is_read column added";
        } else {
            $out[] = "project_briefs.is_read already exists";
        }

        return "<h2>Setup Complete</h2><pre>" . implode("\n", $out) . "</pre>";
    } catch (\Exception $e) {
        // Try marketing pdo as fallback
        try {
            $pdo = marketing_pdo();
            $pdo->exec("CREATE TABLE IF NOT EXISTS page_visits (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent TEXT DEFAULT NULL,
                url TEXT DEFAULT NULL,
                referer TEXT DEFAULT NULL,
                session_id VARCHAR(255) DEFAULT NULL,
                visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_session (session_id),
                INDEX idx_visited (visited_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $out[] = "page_visits table OK";

            try {
                $check = $pdo->query("SHOW COLUMNS FROM project_briefs LIKE 'is_read'")->fetch();
                if (!$check) {
                    $pdo->exec("ALTER TABLE project_briefs ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER notes");
                    $out[] = "project_briefs.is_read column added";
                } else {
                    $out[] = "project_briefs.is_read already exists";
                }
            } catch (\Exception $e2) {
                $out[] = "project_briefs.is_read: " . $e2->getMessage();
            }

            return "<h2>Setup Complete (marketing)</h2><pre>" . implode("\n", $out) . "</pre>";
        } catch (\Exception $e2) {
            return "<h2>Error</h2><pre>" . $e->getMessage() . "\n" . $e2->getMessage() . "</pre>";
        }
    }
});

// Setup Welcome Email Sequence
Route::get('/setup-welcome-sequence', function () {
    $key = request('key', '');
    if ($key !== 'joala2024') { return "Invalid key"; }

    try {
        $emailSeq = \App\Models\EmailSequence::where('name', 'Welcome Sequence')->first();

        if ($emailSeq) {
            // Already exists in email_sequences — check if sequences record is missing
            $seqCheck = \Illuminate\Support\Facades\DB::table('sequences')->where('id', $emailSeq->id)->exists();
            if (!$seqCheck) {
                \Illuminate\Support\Facades\DB::insert('INSERT INTO sequences (id, name, description, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())', [
                    $emailSeq->id, $emailSeq->name, $emailSeq->description, $emailSeq->is_active,
                ]);
                return "<h2>Fixed: Created missing sequences record (ID: {$emailSeq->id})</h2>
                        <p>Now try editing a lead with the Welcome Sequence.</p>";
            }
            return "Welcome Sequence already exists in both tables (ID: $emailSeq->id).";
        }

        // Must create in sequences table first (leads FK references sequences.id)
        // then create in email_sequences with matching ID so steps FK works too
        \Illuminate\Support\Facades\DB::insert('INSERT INTO sequences (name, description, is_active, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', [
            'Welcome Sequence', 'Automatically sent to new newsletter subscribers after email confirmation.', true,
        ]);
        $seqId = \Illuminate\Support\Facades\DB::getPdo()->lastInsertId();

        \App\Models\EmailSequence::create([
            'id' => $seqId,
            'name' => 'Welcome Sequence',
            'description' => 'Automatically sent to new newsletter subscribers after email confirmation.',
            'is_active' => true,
        ]);

        $steps = [
            [
                'subject' => 'Welcome to JoAla Ventures!',
                'body' => '<h2>Hey {{name}},</h2><p>Welcome aboard! We\'re thrilled to have you join the JoAla Ventures community.</p><p>Over the next few days, we\'ll share valuable resources, tips, and exclusive offers to help you grow your business with our digital solutions.</p><p>Here\'s what you can expect from us:</p><ul><li>Business growth tips and strategies</li><li>Exclusive tools and templates</li><li>Special offers and discounts</li><li>Latest updates from JoAla Ventures</li></ul><p>In the meantime, feel free to browse our <a href="https://joala.com.ng/store">digital store</a> for ready-to-use solutions.</p><p>Stay tuned for more!</p><p>Best regards,<br><strong>The JoAla Team</strong></p>',
                'delay_days' => 0,
            ],
            [
                'subject' => 'Explore Our Digital Solutions',
                'body' => '<h2>Hi {{name}},</h2><p>As a new member of our community, we wanted to introduce you to some of our most popular digital products designed to help you succeed:</p><table cellpadding="10" cellspacing="0" style="width:100%;border-collapse:collapse;"><tr><td style="background:#f8f9fa;border:1px solid #dee2e6;"><strong>Email Marketing Templates</strong><br>Professional email sequences to engage your audience.</td></tr><tr><td style="background:#fff;border:1px solid #dee2e6;"><strong>Website Kits</strong><br>Ready-to-deploy websites for churches, restaurants, real estate and more.</td></tr><tr><td style="background:#f8f9fa;border:1px solid #dee2e6;"><strong>Social Media Bundles</strong><br>Content templates to grow your social presence.</td></tr><tr><td style="background:#fff;border:1px solid #dee2e6;"><strong>Automation Tools</strong><br>Streamline your business with our automation playbooks.</td></tr></table><p><a href="https://joala.com.ng/store" style="display:inline-block;background:#6366f1;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;">Visit Our Store</a></p><p>Stay tuned for our next email where we will share success stories from businesses using JoAla solutions!</p>',
                'delay_days' => 2,
            ],
            [
                'subject' => 'Real Results from Businesses Like Yours',
                'body' => '<h2>Hey {{name}},</h2><p>Nothing speaks louder than real results. Here is what some of our clients have achieved using JoAla Ventures solutions:</p><blockquote style="border-left:4px solid #6366f1;padding:12px 20px;margin:16px 0;background:#f8f9fa;"><p>"JoAla website kit helped us launch our church website in just one day. The templates are professional and easy to customize!"</p><footer style="margin-top:8px;font-style:italic;">— Pastor Michael, Lagos</footer></blockquote><blockquote style="border-left:4px solid #6366f1;padding:12px 20px;margin:16px 0;background:#f8f9fa;"><p>"The email sequence templates saved us weeks of work. Our open rates increased by 40%!"</p><footer style="margin-top:8px;font-style:italic;">— Chioma, Digital Marketing Agency</footer></blockquote><blockquote style="border-left:4px solid #6366f1;padding:12px 20px;margin:16px 0;background:#f8f9fa;"><p>"The e-commerce starter kit was a game-changer. We launched our online store in under a week."</p><footer style="margin-top:8px;font-style:italic;">— Ade, Small Business Owner</footer></blockquote><p>Ready to join them? <a href="https://joala.com.ng/store">Browse our full catalog</a> and find the perfect solution for your business.</p>',
                'delay_days' => 5,
            ],
            [
                'subject' => 'Exclusive Offer Just for You',
                'body' => '<h2>Hi {{name}},</h2><p>As a thank you for being part of our community, we would like to offer you an exclusive discount on any product from our store!</p><div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:24px;border-radius:8px;text-align:center;margin:20px 0;"><h2 style="margin:0 0 8px;font-size:24px;">15% OFF</h2><p style="margin:0 0 16px;font-size:16px;">Your first purchase at JoAla Ventures</p><p style="margin:0;font-size:14px;">Use code: <strong style="font-size:20px;letter-spacing:2px;">WELCOME15</strong></p></div><p>This offer is valid for the next 7 days. Do not miss out!</p><p><a href="https://joala.com.ng/store" style="display:inline-block;background:#6366f1;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;">Shop Now</a></p><p>If you have any questions or need help choosing the right product, simply reply to this email. We are here to help!</p><p>Best regards,<br><strong>The JoAla Team</strong></p><p style="font-size:12px;color:#888;margin-top:20px;">If you would rather not receive these emails, <a href="{{unsubscribe}}">unsubscribe here</a>.</p>',
                'delay_days' => 7,
            ],
        ];

        foreach ($steps as $i => $stepData) {
            \App\Models\SequenceStep::create([
                'sequence_id' => $seqId,
                'subject' => $stepData['subject'],
                'body' => $stepData['body'],
                'delay_days' => $stepData['delay_days'],
                'step_order' => $i + 1,
            ]);
        }

        return "<h2>Welcome Sequence Created</h2>
                <p><strong>Sequence ID:</strong> {$seqId} (in both sequences and email_sequences tables)</p>
                <p><strong>Steps created:</strong> " . count($steps) . "</p>
                <p><strong>Delay schedule:</strong> Day 0, Day 2, Day 5, Day 7</p>
                <p>Edit it at: <a href='/admin/marketing/sequences/{$seqId}/edit'>admin/marketing/sequences/{$seqId}/edit</a></p>";
    } catch (\Exception $e) {
        return "<h2>Error</h2><pre>" . $e->getMessage() . "</pre>";
    }
});

// Test: Subscribe + auto-enroll in one call
Route::get('/test-subscribe', function () {
    $key = request('key', '');
    if ($key !== 'joala2024') { return "Invalid key"; }

    $email = request('email');
    if (!$email) { return "Missing ?email parameter"; }
    $name = request('name', 'Test User');

    $lead = \App\Models\Lead::where('email', $email)->first();

    if ($lead && $lead->confirmed) {
        // Already confirmed — enroll directly
        $seq = \App\Models\EmailSequence::where('name', 'Welcome Sequence')->where('is_active', true)->first();
        if ($seq && !$lead->sequence_id) {
            try {
                app(\App\Services\MarketingService::class)->enrollLeadInSequence($lead, $seq->id);
            } catch (\Exception $e) {
                return "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        // Subscribe as new lead
        $lead = \App\Models\Lead::subscribeToNewsletter($email, $name);
        if ($lead->confirmed) {
            $seq = \App\Models\EmailSequence::where('name', 'Welcome Sequence')->where('is_active', true)->first();
            if ($seq && !$lead->sequence_id) {
                try {
                    app(\App\Services\MarketingService::class)->enrollLeadInSequence($lead, $seq->id);
                } catch (\Exception $e) {
                    return "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
                }
            }
        } else {
            // New lead — auto-confirm to simulate clicking confirmation link
            $lead->confirm();
            $seq = \App\Models\EmailSequence::where('name', 'Welcome Sequence')->where('is_active', true)->first();
            if ($seq && !$lead->sequence_id) {
                try {
                    app(\App\Services\MarketingService::class)->enrollLeadInSequence($lead, $seq->id);
                } catch (\Exception $e) {
                    return "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
                }
            }
        }
    }

    $out = "<h2>Result: {$lead->email}</h2>";
    $out .= "<p>Lead ID: {$lead->id}</p>";
    $out .= "<p>Confirmed: " . ($lead->confirmed ? 'yes' : 'no') . "</p>";
    $out .= "<p>Sequence ID: " . ($lead->sequence_id ?? 'none') . "</p>";
    $out .= "<p>Enrolled at: " . ($lead->enrolled_at ?? 'never') . "</p>";

    $queue = \App\Models\EmailQueue::where('lead_id', $lead->id)->get();
    $out .= "<p>Queue entries: {$queue->count()}</p>";
    foreach ($queue as $q) {
        $out .= "<p>Step #{$q->sequence_step_id}: status={$q->status}, scheduled={$q->scheduled_send_time}</p>";
    }

    return $out;
});

// Debug: Check welcome enrollment
Route::get('/debug-enroll/{leadId?}', function ($leadId = null) {
    $key = request('key', '');
    if ($key !== 'joala2024') { return "Invalid key"; }

    $seq = \App\Models\EmailSequence::where('name', 'Welcome Sequence')->first();
    $lead = $leadId ? \App\Models\Lead::find($leadId) : null;

    $out = "<h2>Welcome Sequence Status</h2>";
    $out .= "<p>Found: " . ($seq ? "YES (ID: {$seq->id}, Active: " . ($seq->is_active ? 'yes' : 'no') . ")" : "NO") . "</p>";

    if ($lead) {
        $out .= "<h2>Lead #{$lead->id}: {$lead->email}</h2>";
        $out .= "<p>Sequence ID: " . ($lead->sequence_id ?? 'none') . "</p>";
        $out .= "<p>Confirmed: " . ($lead->confirmed ? 'yes' : 'no') . "</p>";
        $queue = \App\Models\EmailQueue::where('lead_id', $lead->id)->get();
        $out .= "<p>Queue entries: {$queue->count()}</p>";
        foreach ($queue as $q) {
            $out .= "<p>Step {$q->sequence_step_id}: status={$q->status}, scheduled={$q->scheduled_send_time}</p>";
        }
    }

    // Test enrollment endpoint
    $enroll = request('enroll');
    if ($enroll && $lead && $seq) {
        try {
            app(\App\Services\MarketingService::class)->enrollLeadInSequence($lead, $seq->id);
            $out .= "<p style='color:green'>Enrollment attempted via MarketingService</p>";
        } catch (\Exception $e) {
            $out .= "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
        }
    }

    return $out;
});

// Check/Set Brevo API key
Route::get('/check-brevo-key', function () {
    $key = request('key', '');
    if ($key !== 'joala2024') { return "Invalid key"; }

    $set = request('set');
    if ($set) {
        \App\Models\Setting::set('brevo_api_key', $set);
        return "Brevo API key updated successfully.";
    }

    $apiKey = \App\Models\Setting::get('brevo_api_key');
    if (empty($apiKey)) {
        return "Brevo API key is NOT set. To set it, visit: /check-brevo-key?key=joala2024&set=YOUR_BREVO_API_KEY";
    }
    return "Brevo API key is configured (masked): " . substr($apiKey, 0, 8) . '...';
});
