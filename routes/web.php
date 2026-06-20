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
Route::get('/order/validate-coupon', [OrderController::class, 'validateCoupon'])->name('order.validate.coupon');
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
        Route::post('/settings/whatsapp', [SettingsController::class, 'updateWhatsApp'])->name('admin.settings.whatsapp');
        
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
        Route::delete('/notifications/{id}/delete', [MembershipController::class, 'destroyNotification'])->name('admin.notifications.delete');
        
        // Advanced Analytics Route
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');

        // Dashboard Stats API
        Route::get('/stats', function () {
            $visitorsDaily = \App\Models\VisitorLog::where('visited_at', '>=', now()->subDay())->distinct('ip_address')->count('ip_address');
            $visitorsWeekly = \App\Models\VisitorLog::where('visited_at', '>=', now()->subWeek())->distinct('ip_address')->count('ip_address');
            $visitorsMonthly = \App\Models\VisitorLog::where('visited_at', '>=', now()->subDays(30))->distinct('ip_address')->count('ip_address');
            $visitorsYearly = \App\Models\VisitorLog::where('visited_at', '>=', now()->subYear())->distinct('ip_address')->count('ip_address');

            return response()->json([
                'leads' => \App\Models\Lead::count(),
                'deals' => \App\Models\Deal::whereNotIn('stage', ['won', 'lost'])->count(),
                'orders' => \App\Models\Order::where('created_at', '>=', now()->subDays(30))->count(),
                'revenue' => (float) \App\Models\Order::where('payment_status', 'success')->where('created_at', '>=', now()->subDays(30))->sum('final_amount'),
                'visitors_daily' => $visitorsDaily,
                'visitors_weekly' => $visitorsWeekly,
                'visitors_monthly' => $visitorsMonthly,
                'visitors_yearly' => $visitorsYearly,
            ]);
        })->name('admin.stats');

        Route::get('/chart-data', function () {
            $months = [];
            $leadsData = [];
            $revenueData = [];
            for ($i = 5; $i >= 0; $i--) {
                $m = date('M', strtotime("-$i months"));
                $ym = date('Y-m', strtotime("-$i months"));
                $months[] = $m;
                $leadsData[] = \App\Models\Lead::whereYear('created_at', substr($ym, 0, 4))
                    ->whereMonth('created_at', substr($ym, 5, 2))
                    ->count();
            }
            $revenueMap = \App\Models\Order::selectRaw("DATE_FORMAT(created_at,'%Y-%m') as ym, SUM(final_amount) as total")
                ->where('payment_status', 'success')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('ym')
                ->orderBy('ym')
                ->pluck('total', 'ym');
            for ($i = 5; $i >= 0; $i--) {
                $ymKey = date('Y-m', strtotime("-$i months"));
                $revenueData[] = (float)($revenueMap[$ymKey] ?? 0);
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
        Route::post('/marketing/funnels/{funnel}/health', [MarketingController::class, 'calculateFunnelHealth'])->name('admin.marketing.funnels.health');
        Route::get('/marketing/funnels/{funnel}/ab-test', [MarketingController::class, 'funnelAbTest'])->name('admin.marketing.funnels.ab-test');
        Route::post('/marketing/funnels/{funnel}/ab-test', [MarketingController::class, 'funnelAbTestStore'])->name('admin.marketing.funnels.ab-test.store');
        Route::post('/marketing/funnels/{funnel}/ab-test/winner', [MarketingController::class, 'funnelAbTestWinner'])->name('admin.marketing.funnels.ab-test.winner');
        Route::post('/marketing/funnels/{funnel}/ab-test/stop', [MarketingController::class, 'funnelAbTestStop'])->name('admin.marketing.funnels.ab-test.stop');
        Route::post('/marketing/funnels/{funnel}/ab-test/reset', [MarketingController::class, 'funnelAbTestReset'])->name('admin.marketing.funnels.ab-test.reset');
        Route::post('/marketing/funnels/{funnel}/ab-test/traffic', [MarketingController::class, 'funnelAbTestTraffic'])->name('admin.marketing.funnels.ab-test.traffic');
        Route::get('/marketing/funnels/{funnel}/edit', [MarketingController::class, 'funnelsEdit'])->name('admin.marketing.funnels.edit');
        Route::put('/marketing/funnels/{funnel}', [MarketingController::class, 'funnelsUpdate'])->name('admin.marketing.funnels.update');
        Route::delete('/marketing/funnels/{funnel}', [MarketingController::class, 'funnelsDestroy'])->name('admin.marketing.funnels.destroy');
        Route::post('/marketing/funnels/{funnel}/stages', [MarketingController::class, 'funnelStagesStore'])->name('admin.marketing.funnels.stages');
        Route::post('/marketing/funnels/{funnel}/clone', [MarketingController::class, 'funnelsClone'])->name('admin.marketing.funnels.clone');
        Route::post('/marketing/funnels/{funnel}/product', [MarketingController::class, 'updateFunnelProduct'])->name('admin.marketing.funnels.product');
        Route::get('/marketing/funnels/{funnel}/analytics', [MarketingController::class, 'getFunnelAnalytics'])->name('admin.marketing.funnels.analytics');
        Route::get('/marketing/funnels/{funnel}/leads', [MarketingController::class, 'getFunnelLeads'])->name('admin.marketing.funnels.leads');
        Route::get('/marketing/funnels/{funnel}/leads/export', [MarketingController::class, 'exportFunnelLeads'])->name('admin.marketing.funnels.leads.export');
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
                $funnel = \App\Models\Funnel::where('is_active', true)->latest()->first();
                if (!$funnel) {
                    $funnel = (object) ['id' => 0, 'name' => 'No Funnel — Create one first'];
                }
            } catch (\Exception $e) {
                $funnel = (object) ['id' => 0, 'name' => 'Demo Funnel'];
            }
            $sequences = \App\Models\EmailSequence::where('is_active', true)->get(['id', 'name']);
            $tags = \App\Models\Tag::orderBy('name')->get(['id', 'name']);
            $webhooks = \App\Models\Webhook::where('is_active', true)->get(['id', 'name']);
            return view('admin.marketing.automation.builder', compact('funnel', 'sequences', 'tags', 'webhooks'));
        })->name('admin.marketing.automation.builder');
        
        // Quick migration runner
        Route::get('/run-order-bumps', function() {
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'order_bumps')) {
                    return "✅ order_bumps column already exists!";
                }
                
                \Illuminate\Support\Facades\Schema::table('orders', function ($table) {
                    $table->json('order_bumps')->nullable()->after('checkout_abandoned_at');
                    $table->decimal('order_bumps_total', 10, 2)->nullable()->after('order_bumps');
                });
                
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
                $refunds = \Illuminate\Support\Facades\DB::table('refund_requests')->orderBy('created_at', 'desc')->limit(50)->get()->map(function ($r) { return (array) $r; })->toArray();
                return view('admin.refunds.index', compact('refunds'));
            } catch (\Exception $e) {
                return "Error: " . $e->getMessage();
            }
        })->name('admin.refunds');
        
        Route::post('/refunds/{id}/approve', function($id) {
            try {
                \Illuminate\Support\Facades\DB::table('refund_requests')->where('id', $id)->update(['status' => 'approved', 'processed_at' => now()]);
                return back()->with('success', 'Refund approved!');
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        })->name('admin.refunds.approve');
        
        Route::post('/refunds/{id}/reject', function($id) {
            try {
                \Illuminate\Support\Facades\DB::table('refund_requests')->where('id', $id)->update(['status' => 'rejected', 'processed_at' => now()]);
                return back()->with('success', 'Refund rejected!');
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        })->name('admin.refunds.reject');
        
        // Affiliate Management Routes
        Route::get('/affiliates', function() {
            try {
                $affiliates = \App\Models\Affiliate::latest()->limit(50)->get()->toArray();
                return view('admin.affiliates.index', compact('affiliates'));
            } catch (\Exception $e) {
                return "Error: " . $e->getMessage();
            }
        })->name('admin.affiliates');
        
        Route::delete('/affiliates/{id}', function($id) {
            try {
                \App\Models\Affiliate::findOrFail($id)->delete();
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
        $affiliate = \App\Models\Affiliate::where('referral_code', $code)->where('status', 'active')->first();
        
        if ($affiliate) {
            $affiliate->increment('total_clicks');
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
        $amount = null;
        if ($request->order_id) {
            $order = \App\Models\Order::find(intval($request->order_id));
            if ($order) {
                $amount = $order->final_amount;
            }
        }
        
        \Illuminate\Support\Facades\DB::table('refund_requests')->insert([
            'order_id' => $request->order_id ?? null,
            'user_email' => $request->email,
            'reason' => $request->reason,
            'status' => 'pending',
            'amount' => $amount,
            'created_at' => now(),
        ]);
        
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

// Serve free product files from separate directory
Route::get('/free-download/{slug}', function ($slug) {
    $product = \App\Models\Product::where('slug', $slug)->where('price', 0)->firstOrFail();
    $path = public_path($product->file_path);
    if (!file_exists($path)) {
        abort(404, 'File not found');
    }
    return response()->download($path);
})->where('slug', '[\w\-]+');

// Show download page after lead magnet signup (dynamic for any product)
Route::get('/download/{slug}', function ($slug) {
    $landingPage = \App\Models\LandingPage::where('slug', $slug)->firstOrFail();
    $funnel = $landingPage->funnel;

    $premiumProduct = null;
    if ($funnel && $funnel->product_id) {
        $premiumProduct = \App\Models\Product::find($funnel->product_id);
    }

    // Free product uses the same slug as the landing page
    $freeProduct = \App\Models\Product::where('slug', $slug)->where('price', 0)->first();
    // Backward compat: e-commerce free product slug differs from landing page slug
    if (!$freeProduct && $slug === 'free-e-commerce-starter-kit') {
        $freeProduct = \App\Models\Product::where('slug', 'free-ecommerce-starter-kit')->where('price', 0)->first();
    }

    $downloadUrl = $freeProduct ? url('/free-download/' . $freeProduct->slug) : '#';
    // Check for a custom sales page URL from the funnel's checkout stage
    $salesPageUrl = url('/store');
    if ($funnel) {
        $checkoutStage = $funnel->stages()->where('type', 'checkout')->first();
        if ($checkoutStage && isset($checkoutStage->content['url'])) {
            $salesPageUrl = url($checkoutStage->content['url']);
        }
    }
    if (!$salesPageUrl || $salesPageUrl === url('/store')) {
        $salesPageUrl = $premiumProduct ? url('/store/' . $premiumProduct->slug) : url('/store');
    }
    $productName = $premiumProduct ? $premiumProduct->title : 'Premium Product';
    $productPrice = $premiumProduct ? '₦' . number_format($premiumProduct->current_price) : '';

    return view('front.download-page', compact(
        'downloadUrl', 'salesPageUrl', 'productName', 'productPrice', 'landingPage', 'funnel'
    ));
})->where('slug', '[\w\-]+');

// Comprehensive E-Commerce Starter Kit Funnel Setup
Route::get('/setup-ecommerce-funnel', function () {
    try {
        $out = [];

        // 1. Create free lead magnet product
        $freeProduct = \App\Models\Product::firstOrCreate(
            ['slug' => 'free-ecommerce-starter-kit'],
            [
                'title' => 'Free eCommerce Starter Kit',
                'slug' => 'free-ecommerce-starter-kit',
                'short_description' => '7-step checklist to launch your online store',
                'description' => 'Complete checklist covering store setup, product management, payment gateway, shipping, tax settings, SEO, and launch.',
                'type' => 'ebook',
                'price' => 0,
                'sale_price' => 0,
                'file_path' => 'uploads/free-products/files/free-ecommerce-starter-kit.html',
                'is_active' => 1,
                'is_featured' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $out[] = "Free product ID: {$freeProduct->id}";

        // 2. Create pre-sale email sequence
        $presaleSeq = \App\Models\EmailSequence::updateOrCreate(
            ['name' => 'E-Commerce Starter Kit Pre-Sale'],
            [
                'name' => 'E-Commerce Starter Kit Pre-Sale',
                'description' => 'Nurture leads who got the free kit toward purchasing the premium Laravel e-commerce platform',
                'trigger_type' => 'welcome',
                'is_active' => true,
            ]
        );
        \Illuminate\Support\Facades\DB::table('sequence_steps')->where('sequence_id', $presaleSeq->id)->delete();
        \Illuminate\Support\Facades\DB::table('sequence_steps')->insert([
            ['sequence_id' => $presaleSeq->id, 'step_order' => 1, 'delay_days' => 0, 'subject' => 'Your Free eCommerce Kit is ready!', 'body' => "Hi {{name}},\n\nYour free eCommerce Starter Kit checklist is ready.\n\nDownload it here: https://joala.com.ng/free-download/free-ecommerce-starter-kit\n\nInside this checklist, you'll find everything you need to launch your online store — from domain setup to payment gateways.\n\nBut if you're ready to skip the DIY and get a complete, ready-to-run e-commerce platform, check out the premium version:\nhttps://joala.com.ng/ecommerce-starter-kit.php\n\nIt includes Laravel installation, Paystack/Flutterwave/Stripe integration, admin dashboard, inventory management, and more.\n\nCheers,\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 2, 'delay_days' => 2, 'subject' => 'Why most online stores fail (and how to avoid it)', 'body' => "Hi {{name}},\n\nDid you know that 80% of online stores fail within the first 3 months?\n\nThe #1 reason? They use complicated platforms that take months to set up.\n\nThat's exactly why I built the E-Commerce Starter Kit — a complete Laravel platform that you can launch in 48 hours.\n\nHere's what's included:\n✓ Paystack, Stripe & Flutterwave integration\n✓ Admin dashboard with real-time analytics\n✓ Inventory & order management\n✓ Physical & digital product support\n✓ Africa & worldwide shipping\n✓ Lifetime free updates\n\nSee it here: https://joala.com.ng/ecommerce-starter-kit.php\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 3, 'delay_days' => 4, 'subject' => 'Success story: From 0 to 50 orders in one week', 'body' => "Hi {{name}},\n\n\"We launched our online store in just 2 days. The admin panel makes managing orders effortless. Best investment we've made.\"\n— Adebola Kuti, Fashion Store Owner, Lagos\n\nAdebola used the E-Commerce Starter Kit to launch her fashion store. Within one week, she had processed 50 orders.\n\nWhat made the difference?\n- She didn't waste weeks on setup\n- The built-in payment integrations worked instantly\n- The dashboard gave her real-time insights into what was selling\n\nYou can get the same results: https://joala.com.ng/ecommerce-starter-kit.php\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 4, 'delay_days' => 6, 'subject' => 'Special offer: 15% off the E-Commerce Starter Kit', 'body' => "Hi {{name}},\n\nI'm giving you an exclusive 15% discount on the E-Commerce Starter Kit.\n\nUse code: LAUNCH15 at checkout\n\nThis brings the price down to just ₦85,000 — a one-time investment for a lifetime of sales.\n\nHere's exactly what you'll get:\n• Complete Laravel e-commerce platform\n• Paystack, Stripe & Flutterwave integration\n• Admin dashboard with analytics\n• Inventory & order management\n• Mobile responsive storefront\n• Physical & digital product support\n• Africa & worldwide shipping\n• Lifetime free updates\n• Priority support\n\nGet it now: https://joala.com.ng/ecommerce-starter-kit.php?coupon=LAUNCH15\n\nThis offer won't last forever. Grab it today.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 5, 'delay_days' => 9, 'subject' => 'Last chance: Your 15% discount expires soon', 'body' => "Hi {{name}},\n\nJust a friendly reminder that your 15% discount (code: LAUNCH15) is still available.\n\nBut I can't keep it open forever.\n\nIf you're serious about launching your online store, now is the time.\n\nThe E-Commerce Starter Kit gives you everything you need — no coding required, no complex setup, just a complete platform that works out of the box.\n\nGet started today: https://joala.com.ng/ecommerce-starter-kit.php\n\nIf you have any questions, just reply to this email.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
        ]);
        $out[] = "Pre-sale sequence ID: {$presaleSeq->id} (5 steps)";

        // Sync sequences table
        $seq = \App\Models\Sequence::updateOrCreate(
            ['id' => $presaleSeq->id],
            ['name' => 'E-Commerce Starter Kit Pre-Sale', 'description' => 'Nurture leads toward the premium e-commerce platform', 'is_active' => true]
        );
        $out[] = "Sequences table synced ID: {$seq->id}";

        // 3. Find premium product
        $premiumProduct = \App\Models\Product::where('slug', 'e-commerce-starter-kit')->first();
        $premiumId = $premiumProduct ? $premiumProduct->id : 0;
        $out[] = "Premium product ID: " . ($premiumProduct ? $premiumProduct->id : 'NOT FOUND');

        // 4. Create the funnel
        $funnel = \App\Models\Funnel::updateOrCreate(
            ['slug' => 'ecommerce-starter-kit-funnel'],
            [
                'name' => 'E-Commerce Starter Kit Funnel',
                'slug' => 'ecommerce-starter-kit-funnel',
                'description' => 'Lead magnet → download → checkout → pre-sale nurture',
                'goal' => 'sales',
                'funnel_type' => 'sales',
                'product_id' => $premiumId ?: null,
                'welcome_sequence_id' => $presaleSeq->id,
                'environment' => 'production',
                'is_active' => true,
                'upsell_enabled' => false,
                'countdown_enabled' => false,
            ]
        );
        $out[] = "Funnel ID: {$funnel->id}";

        // 5. Link landing page to funnel and pre-sale sequence
        try {
            $landingPage = \App\Models\LandingPage::where('slug', 'free-e-commerce-starter-kit')->first();
            if ($landingPage) {
                $landingPage->update([
                    'funnel_id' => $funnel->id,
                    'sequence_id' => $presaleSeq->id,
                ]);
                $out[] = "Landing page linked: ID {$landingPage->id}";
            } else {
                $out[] = "WARNING: Landing page 'free-e-commerce-starter-kit' not found in DB";
            }
        } catch (\Exception $e) {
            $out[] = "WARNING: Could not link landing page: " . $e->getMessage();
        }

        // 5b. Create funnel stages
        \App\Models\FunnelStage::where('funnel_id', $funnel->id)->delete();

        // Stage 1: Landing page (lead capture)
        \App\Models\FunnelStage::create([
            'funnel_id' => $funnel->id,
            'name' => 'Lead Magnet Page',
            'type' => 'landing',
            'content' => ['url' => '/l/free-e-commerce-starter-kit'],
            'order' => 1,
            'delay_days' => 0,
            'is_required' => true,
            'action_on_complete' => 'advance',
        ]);
        $out[] = "Stage 1: Lead Magnet Page";

        // Stage 2: Download page
        \App\Models\FunnelStage::create([
            'funnel_id' => $funnel->id,
            'name' => 'Download Page',
            'type' => 'thank_you',
            'content' => ['url' => '/download/free-e-commerce-starter-kit'],
            'order' => 2,
            'delay_days' => 0,
            'is_required' => false,
            'action_on_complete' => 'advance',
        ]);
        $out[] = "Stage 2: Download Page";

        // Stage 3: Premium checkout
        \App\Models\FunnelStage::create([
            'funnel_id' => $funnel->id,
            'name' => 'Premium Checkout',
            'type' => 'checkout',
            'content' => ['url' => '/ecommerce-starter-kit.php'],
            'order' => 3,
            'delay_days' => 0,
            'sequence_id' => $presaleSeq->id,
            'is_required' => false,
            'action_on_complete' => 'email',
        ]);
        $out[] = "Stage 3: Premium Checkout";

        $out[] = "---";
        $out[] = "Landing page URL: https://joala.com.ng/l/free-e-commerce-starter-kit";
        $out[] = "Download page URL: https://joala.com.ng/download/free-e-commerce-starter-kit";
        $out[] = "Sales page URL: https://joala.com.ng/ecommerce-starter-kit.php";
        $out[] = "Funnel edit: /admin/marketing/funnels/{$funnel->id}/edit";

        return "<h2>E-Commerce Funnel Setup Complete</h2><pre>" . implode("\n", $out) . "</pre>";

    } catch (\Exception $e) {
        return "<h2>ERROR</h2><pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
    }
});

// Comprehensive WhatsApp Funnel Setup
Route::get('/setup-whatsapp-funnel', function () {
    try {
        $out = [];

        // 1. Create free lead magnet product
        $freeProduct = \App\Models\Product::firstOrCreate(
            ['slug' => 'free-whatsapp-marketing-bundle'],
            [
                'title' => 'Free WhatsApp Marketing Guide',
                'slug' => 'free-whatsapp-marketing-bundle',
                'short_description' => 'Complete guide to using WhatsApp for business growth',
                'description' => 'Learn WhatsApp broadcast setup, automation, CRM integration, templates, business profile optimization, and analytics tracking.',
                'type' => 'ebook',
                'price' => 0,
                'sale_price' => 0,
                'file_path' => 'uploads/free-products/files/free-whatsapp-marketing-bundle.html',
                'is_active' => 1,
                'is_featured' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $out[] = "Free product ID: {$freeProduct->id}";

        // 2. Create pre-sale email sequence
        $presaleSeq = \App\Models\EmailSequence::updateOrCreate(
            ['name' => 'WhatsApp Marketing Bundle Pre-Sale'],
            [
                'name' => 'WhatsApp Marketing Bundle Pre-Sale',
                'description' => 'Nurture leads who got the free WhatsApp guide toward purchasing the premium bundle',
                'trigger_type' => 'welcome',
                'is_active' => true,
            ]
        );
        \Illuminate\Support\Facades\DB::table('sequence_steps')->where('sequence_id', $presaleSeq->id)->delete();
        \Illuminate\Support\Facades\DB::table('sequence_steps')->insert([
            ['sequence_id' => $presaleSeq->id, 'step_order' => 1, 'delay_days' => 0, 'subject' => 'Your Free WhatsApp Marketing Guide is ready!', 'body' => "Hi {{name}},\n\nYour free WhatsApp Marketing Guide is ready.\n\nDownload it here: https://joala.com.ng/free-download/free-whatsapp-marketing-bundle\n\nInside this guide, you'll learn the fundamentals of using WhatsApp for business — from broadcast setup to analytics tracking.\n\nBut if you're ready to go beyond the basics and get a complete WhatsApp marketing system with done-for-you templates, check out the premium bundle:\nhttps://joala.com.ng/store/whatsapp-marketing-bundle\n\nIt includes broadcast templates, automation workflows, CRM integration guides, and more.\n\nCheers,\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 2, 'delay_days' => 2, 'subject' => 'Why most WhatsApp campaigns fail (and how to avoid it)', 'body' => "Hi {{name}},\n\nDid you know that 70% of business WhatsApp messages go unread?\n\nThe #1 reason? Lack of strategy and proper setup.\n\nMost businesses jump straight into broadcasting without:\n- A clean, segmented contact list\n- Proper broadcast timing and frequency\n- Follow-up automation\n- Performance tracking\n\nThat's exactly what the WhatsApp Marketing Bundle solves — a complete system with:\n✓ Broadcast templates that get replies\n✓ Automation workflows for follow-ups\n✓ CRM integration to track conversations\n✓ Analytics templates to measure ROI\n\nSee it here: https://joala.com.ng/store/whatsapp-marketing-bundle\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 3, 'delay_days' => 4, 'subject' => 'Success story: How a boutique grew sales 3x with WhatsApp', 'body' => "Hi {{name}},\n\n\"We went from 10 orders a week to 35 after implementing a proper WhatsApp broadcast system. The templates alone saved us hours.\"\n— Chioma O., Boutique Owner, Abuja\n\nChioma had been using WhatsApp the same way most businesses do — sending random messages whenever she remembered. After implementing a structured broadcast + automation system, here's what changed:\n- Messages actually got read (78% open rate)\n- Customers started replying and ordering\n- She saved 10+ hours per week on manual messaging\n\nThe WhatsApp Marketing Bundle gives you everything Chioma used, including:\n- Done-for-you broadcast scripts\n- Automation workflow templates\n- CRM tracking templates\n- Business profile optimization guide\n\nGet the same results: https://joala.com.ng/store/whatsapp-marketing-bundle\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 4, 'delay_days' => 6, 'subject' => 'Special offer: 15% off the WhatsApp Marketing Bundle', 'body' => "Hi {{name}},\n\nI'm giving you an exclusive 15% discount on the WhatsApp Marketing Bundle.\n\nUse code: WHATSAPP15 at checkout\n\nThis brings the price down to just ₦6,800 — a one-time investment for a complete WhatsApp marketing system.\n\nHere's exactly what you'll get:\n• 20+ Broadcast Templates (sales, announcements, follow-ups)\n• Automation Workflow Guide\n• CRM Integration Templates\n• Business Profile Optimization Checklist\n• Analytics & Reporting Templates\n• Customer Support Scripts\n• Product Catalogue Setup Guide\n• WhatsApp Business vs API Comparison\n• Broadcast Timing & Frequency Guide\n• Lifetime free updates\n• Priority support\n\nGet it now: https://joala.com.ng/store/whatsapp-marketing-bundle?coupon=WHATSAPP15\n\nThis offer won't last forever. Grab it today.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 5, 'delay_days' => 9, 'subject' => 'Last chance: Your 15% discount on WhatsApp Bundle expires soon', 'body' => "Hi {{name}},\n\nJust a friendly reminder that your 15% discount (code: WHATSAPP15) is still available.\n\nBut I can't keep it open forever.\n\nIf you're serious about using WhatsApp to grow your business, now is the time.\n\nThe WhatsApp Marketing Bundle gives you everything you need — ready-to-use templates, automation workflows, CRM guides, and more. No guesswork, no trial-and-error.\n\nGet started today: https://joala.com.ng/store/whatsapp-marketing-bundle?coupon=WHATSAPP15\n\nIf you have any questions, just reply to this email.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
        ]);
        $out[] = "Pre-sale sequence ID: {$presaleSeq->id} (5 steps)";

        // Sync sequences table
        $seq = \App\Models\Sequence::updateOrCreate(
            ['id' => $presaleSeq->id],
            ['name' => 'WhatsApp Marketing Bundle Pre-Sale', 'description' => 'Nurture leads toward the premium WhatsApp bundle', 'is_active' => true]
        );
        $out[] = "Sequences table synced ID: {$seq->id}";

        // 3. Create post-purchase sequence
        $postSeq = \App\Models\EmailSequence::updateOrCreate(
            ['name' => 'WhatsApp Marketing Bundle Post-Purchase'],
            [
                'name' => 'WhatsApp Marketing Bundle Post-Purchase',
                'description' => 'Onboard and engage buyers of the WhatsApp Marketing Bundle',
                'trigger_type' => 'post_purchase',
                'is_active' => true,
            ]
        );
        \Illuminate\Support\Facades\DB::table('sequence_steps')->where('sequence_id', $postSeq->id)->delete();
        \Illuminate\Support\Facades\DB::table('sequence_steps')->insert([
            ['sequence_id' => $postSeq->id, 'step_order' => 1, 'delay_days' => 0, 'subject' => 'Your WhatsApp Marketing Bundle is ready!', 'body' => "Hi {{name}},\n\nThank you for purchasing the WhatsApp Marketing Bundle!\n\nYour download link: https://joala.com.ng/order/download/{{download_token}}\n\nGetting started fast:\n1. Download the ZIP file\n2. Extract all templates to your computer\n3. Open the README file for setup instructions\n4. Start with the Broadcast Templates — pick one and customize it\n5. Use the Automation Guide to set up your first workflow\n\nInside your bundle:\n✓ 20+ Broadcast Templates\n✓ Automation Workflow Guide\n✓ CRM Integration Templates\n✓ Business Profile Optimization Checklist\n✓ Analytics & Reporting Templates\n✓ Customer Support Scripts\n✓ Product Catalogue Setup Guide\n\nPro tip: Start with the \"Welcome Broadcast\" template — it's the highest-converting template in the pack.\n\nIf you need help, just reply to this email.\n\nCheers,\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $postSeq->id, 'step_order' => 2, 'delay_days' => 3, 'subject' => 'Quick start: Set up your first WhatsApp broadcast', 'body' => "Hi {{name}},\n\nReady to send your first broadcast? Here's a quick 3-step process using your bundle:\n\n1. Open the Broadcast Templates folder\n2. Choose the \"Promotional Offer\" template\n3. Customize it with your product/service details\n4. Send to your contact list via WhatsApp Broadcast\n\nBest practices:\n- Send between 10am-2pm for highest open rates\n- Keep messages under 200 characters\n- Always include a call-to-action\n- Track replies as leads\n\nThe Broadcast Templates folder includes templates for:\n✓ Sales & Promotions\n✓ Announcements & Launches\n✓ Customer Follow-ups\n✓ Abandoned Cart Recovery\n✓ Re-engagement Campaigns\n\nStart with one campaign this week and track your results.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $postSeq->id, 'step_order' => 3, 'delay_days' => 5, 'subject' => 'Master WhatsApp Automation & CRM tracking', 'body' => "Hi {{name}},\n\nNow that you've sent your first broadcast, let's level up with automation.\n\nThe Automation Workflow Guide in your bundle shows you how to:\n- Set up auto-replies for common questions\n- Create follow-up sequences for leads\n- Tag and segment your contacts\n- Track conversations in a CRM\n\nQuick tip: Use the \"Abandoned Cart Recovery\" template for customers who showed interest but didn't buy. Set it to send 24 hours after the initial inquiry.\n\nCRM tracking tip: Create a simple spreadsheet (template included) to track:\n- Date of first contact\n- Product/service they asked about\n- Follow-up status\n- Deal value\n\nThis simple system can double your conversion rate.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $postSeq->id, 'step_order' => 4, 'delay_days' => 7, 'subject' => 'Optimize your WhatsApp Business Profile for more sales', 'body' => "Hi {{name}},\n\nYour WhatsApp Business Profile is your storefront — is it optimized?\n\nUse the Business Profile Optimization Checklist in your bundle to:\n✓ Complete your profile with high-quality images\n✓ Write a compelling business description\n✓ Set up your product catalogue\n✓ Configure quick replies for FAQs\n✓ Set business hours and location\n✓ Add your website and social links\n✓ Create greeting and away messages\n\nBusinesses with complete profiles get 3x more customer inquiries.\n\nTake 15 minutes today to run through the checklist. Every field you complete is another reason for a customer to trust you.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $postSeq->id, 'step_order' => 5, 'delay_days' => 10, 'subject' => 'WhatsApp Business API vs WhatsApp Business App — which is right for you?', 'body' => "Hi {{name}},\n\nAs your business grows, you might wonder if you need the WhatsApp Business API.\n\nHere's a quick comparison (full guide in your bundle):\n\nWhatsApp Business App (Free):\n✓ Great for small businesses with <50 daily conversations\n✓ Manual broadcast to 256 contacts at a time\n✓ Basic quick replies and labels\n✓ Free to use\n\nWhatsApp Business API (Paid):\n✓ For businesses with 50+ daily conversations\n✓ Unlimited broadcast reach\n✓ CRM integration for automation\n✓ Multiple agents can reply\n✓ Analytics dashboard\n\nTips:\n- Start with the free Business App\n- When you hit 30+ conversations/day, consider the API\n- Use the CRM templates in your bundle regardless of which option you choose\n\nYou already have everything you need to start making sales with WhatsApp today.\n\nIf you ever need help, reply to this email.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
        ]);
        $out[] = "Post-purchase sequence ID: {$postSeq->id} (5 steps)";

        // Sync sequences table
        $seq2 = \App\Models\Sequence::updateOrCreate(
            ['id' => $postSeq->id],
            ['name' => 'WhatsApp Marketing Bundle Post-Purchase', 'description' => 'Onboard new buyers of WhatsApp bundle', 'is_active' => true]
        );
        $out[] = "Sequences table synced ID: {$seq2->id}";

        // 4. Find premium product
        $premiumProduct = \App\Models\Product::where('slug', 'whatsapp-marketing-bundle')->first();
        $premiumId = $premiumProduct ? $premiumProduct->id : 0;
        $out[] = "Premium product ID: " . ($premiumProduct ? $premiumProduct->id : 'NOT FOUND');

        // 5. Create the funnel
        $funnel = \App\Models\Funnel::updateOrCreate(
            ['slug' => 'whatsapp-marketing-bundle-funnel'],
            [
                'name' => 'WhatsApp Marketing Bundle Funnel',
                'slug' => 'whatsapp-marketing-bundle-funnel',
                'description' => 'Lead magnet → download → checkout → pre-sale nurture',
                'goal' => 'sales',
                'funnel_type' => 'sales',
                'product_id' => $premiumId ?: null,
                'welcome_sequence_id' => $presaleSeq->id,
                'environment' => 'production',
                'is_active' => true,
                'upsell_enabled' => false,
                'countdown_enabled' => false,
            ]
        );
        $out[] = "Funnel ID: {$funnel->id}";

        // 6. Link landing page to funnel and pre-sale sequence
        try {
            $landingPage = \App\Models\LandingPage::where('slug', 'free-whatsapp-marketing-bundle')->first();
            if ($landingPage) {
                $landingPage->update([
                    'funnel_id' => $funnel->id,
                    'sequence_id' => $presaleSeq->id,
                ]);
                $out[] = "Landing page linked: ID {$landingPage->id}";
            } else {
                $out[] = "WARNING: Landing page 'free-whatsapp-marketing-bundle' not found in DB";
            }
        } catch (\Exception $e) {
            $out[] = "WARNING: Could not link landing page: " . $e->getMessage();
        }

        // 7. Create funnel stages
        \App\Models\FunnelStage::where('funnel_id', $funnel->id)->delete();

        // Stage 1: Landing page (lead capture)
        \App\Models\FunnelStage::create([
            'funnel_id' => $funnel->id,
            'name' => 'Lead Magnet Page',
            'type' => 'landing',
            'content' => ['url' => '/l/free-whatsapp-marketing-bundle'],
            'order' => 1,
            'delay_days' => 0,
            'is_required' => true,
            'action_on_complete' => 'advance',
        ]);
        $out[] = "Stage 1: Lead Magnet Page";

        // Stage 2: Download page
        \App\Models\FunnelStage::create([
            'funnel_id' => $funnel->id,
            'name' => 'Download Page',
            'type' => 'thank_you',
            'content' => ['url' => '/download/free-whatsapp-marketing-bundle'],
            'order' => 2,
            'delay_days' => 0,
            'is_required' => false,
            'action_on_complete' => 'advance',
        ]);
        $out[] = "Stage 2: Download Page";

        // Stage 3: Premium checkout
        \App\Models\FunnelStage::create([
            'funnel_id' => $funnel->id,
            'name' => 'Premium Checkout',
            'type' => 'checkout',
            'content' => ['url' => '/store/whatsapp-marketing-bundle'],
            'order' => 3,
            'delay_days' => 0,
            'sequence_id' => $presaleSeq->id,
            'is_required' => false,
            'action_on_complete' => 'email',
        ]);
        $out[] = "Stage 3: Premium Checkout";

        // 8. Write free product deliverable HTML file
        $freeFilePath = public_path('uploads/free-products/files/free-whatsapp-marketing-bundle.html');
        $dir = dirname($freeFilePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($freeFilePath, '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Free WhatsApp Marketing Guide - JoAla Ventures</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;color:#1e293b;line-height:1.7;padding:40px 20px}
.container{max-width:720px;margin:0 auto}
h1{font-size:2em;margin-bottom:8px;color:#0f172a}
.lead{font-size:1.15em;color:#64748b;margin-bottom:32px}
h2{font-size:1.4em;margin:32px 0 12px;color:#075985;border-bottom:2px solid #e2e8f0;padding-bottom:6px}
h3{font-size:1.1em;margin:20px 0 8px;color:#0c4a6e}
p{margin-bottom:12px}
ul{list-style:none;padding:0;margin:0 0 16px}
ul li{padding:8px 0 8px 28px;position:relative;border-bottom:1px solid #f1f5f9}
ul li:before{content:"\\2713";position:absolute;left:0;color:#059669;font-weight:bold}
.badge{display:inline-block;background:#dbeafe;color:#1e40af;padding:2px 12px;border-radius:20px;font-size:.85em;font-weight:600}
.footer{margin-top:40px;padding-top:24px;border-top:2px solid #e2e8f0;text-align:center;color:#94a3b8;font-size:.9em}
</style>
</head>
<body>
<div class="container">
<h1>WhatsApp Marketing Guide</h1>
<p class="lead">Your complete guide to using WhatsApp for business growth — broadcast, automation, CRM, and more.</p>

<h2>1. WhatsApp Broadcast Setup</h2>
<p>WhatsApp Broadcast allows you to send messages to multiple contacts at once while keeping each conversation private. Unlike groups, recipients only see your message — not who else received it.</p>
<ul>
<li>Use broadcast lists for announcements, promotions, and updates</li>
<li>Keep broadcasts to 256 recipients per list (Business App limit)</li>
<li>Always personalise the first line with the recipient\'s name</li>
<li>Send between 10am-2pm for optimal open rates</li>
<li>Track replies manually and follow up within 24 hours</li>
<li>Segment your audience before broadcasting (e.g., customers vs prospects)</li>
</ul>

<h2>2. Automation Strategies</h2>
<p>Save hours with smart automation. The key is to automate repetitive tasks while keeping the personal touch.</p>
<ul>
<li>Set up quick replies for FAQs (pricing, hours, location)</li>
<li>Use away messages for after-hours inquiries</li>
<li>Create greeting messages that capture lead info</li>
<li>Build follow-up sequences for new leads</li>
<li>Automate abandoned cart recovery messages</li>
<li>Schedule broadcast messages during peak engagement times</li>
</ul>

<h2>3. CRM Integration</h2>
<p>Turn WhatsApp from a messaging app into a sales engine by tracking every conversation.</p>
<ul>
<li>Tag contacts by source, interest, and purchase stage</li>
<li>Track conversation history per customer</li>
<li>Log deals and follow-ups in a simple spreadsheet or CRM</li>
<li>Set reminders for follow-ups based on lead response</li>
<li>Measure conversion rate from message to sale</li>
<li>Export your contact list for backup and analysis</li>
</ul>

<h2>4. Ready-to-Use Templates</h2>
<p>Copy, paste, and customise these proven templates:</p>

<h3 class="badge">Sales Broadcast</h3>
<p>"Hi [Name], we have a special offer just for you! Get [discount]% off on [product] this week only. Reply YES to claim yours."</p>

<h3 class="badge">Follow-Up</h3>
<p>"Hi [Name], just checking in! Did you have a chance to review the [product] we discussed? Happy to answer any questions."</p>

<h3 class="badge">Abandoned Cart</h3>
<p>"Hi [Name], you left [product] in your cart! Complete your order now and get free delivery. Reply ORDER to proceed."</p>

<h3 class="badge">Re-engagement</h3>
<p>"Hi [Name], it\'s been a while! We\'ve added new products you might like. Check them out here: [link]"</p>

<h2>5. Business Profile Optimization</h2>
<p>Your WhatsApp Business profile is your digital storefront.</p>
<ul>
<li>Use a professional logo as your profile picture</li>
<li>Write a clear business description (3-4 lines max)</li>
<li>Add your business address, hours, and website</li>
<li>Set up your product catalogue with photos and prices</li>
<li>Create quick replies for top 5 FAQs</li>
<li>Enable greeting and away messages</li>
<li>Link your Instagram and Facebook accounts</li>
<li>Verify your business if eligible</li>
</ul>

<h2>6. Analytics & Tracking</h2>
<p>Track these metrics to optimise your WhatsApp marketing:</p>
<ul>
<li>Message delivery rate (aim for 95%+)</li>
<li>Read rate (aim for 70%+)</li>
<li>Reply rate (aim for 30%+)</li>
<li>Conversion rate (from message to sale)</li>
<li>Average response time (aim for under 1 hour)</li>
<li>Best sending days and times</li>
<li>Most engaging message types</li>
</ul>

<div class="footer">
<p><strong>Want the complete system?</strong> Get the WhatsApp Marketing Bundle with 20+ ready-to-use templates, automation workflows, CRM guides, and more.</p>
<p>Visit joala.com.ng/store/whatsapp-marketing-bundle</p>
<p>&copy; 2026 JoAla Ventures. All rights reserved.</p>
</div>
</div>
</body>
</html>');
        $out[] = "Free product deliverable HTML created at: uploads/free-products/files/free-whatsapp-marketing-bundle.html";

        $out[] = "---";
        $out[] = "Landing page URL: https://joala.com.ng/l/free-whatsapp-marketing-bundle";
        $out[] = "Download page URL: https://joala.com.ng/download/free-whatsapp-marketing-bundle";
        $out[] = "Sales page URL: https://joala.com.ng/store/whatsapp-marketing-bundle";
        $out[] = "Funnel edit: /admin/marketing/funnels/{$funnel->id}/edit";

        return "<h2>WhatsApp Funnel Setup Complete</h2><pre>" . implode("\n", $out) . "</pre>";

    } catch (\Exception $e) {
        return "<h2>ERROR</h2><pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
    }
});

// Comprehensive Email Sequence Templates Pack Funnel Setup
Route::get('/setup-email-funnel', function () {
    try {
        $out = [];

        // 1. Create free lead magnet product
        $freeProduct = \App\Models\Product::firstOrCreate(
            ['slug' => 'free-email-sequence-templates-pack'],
            [
                'title' => 'Free Email Sequence Templates',
                'slug' => 'free-email-sequence-templates-pack',
                'short_description' => '24 proven email templates to automate your marketing',
                'description' => 'Get ready-to-use email sequences for welcome, sales funnel, cart abandonment, re-engagement, upsell, and follow-up campaigns.',
                'type' => 'ebook',
                'price' => 0,
                'sale_price' => 0,
                'file_path' => 'uploads/free-products/files/free-email-sequence-templates-pack.html',
                'is_active' => 1,
                'is_featured' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $out[] = "Free product ID: {$freeProduct->id}";

        // 2. Create pre-sale email sequence
        $presaleSeq = \App\Models\EmailSequence::updateOrCreate(
            ['name' => 'Email Sequence Templates Pre-Sale'],
            [
                'name' => 'Email Sequence Templates Pre-Sale',
                'description' => 'Nurture leads who got the free email templates toward purchasing the premium pack',
                'trigger_type' => 'welcome',
                'is_active' => true,
            ]
        );
        \Illuminate\Support\Facades\DB::table('sequence_steps')->where('sequence_id', $presaleSeq->id)->delete();
        \Illuminate\Support\Facades\DB::table('sequence_steps')->insert([
            ['sequence_id' => $presaleSeq->id, 'step_order' => 1, 'delay_days' => 0, 'subject' => 'Your Free Email Sequence Templates are ready!', 'body' => "Hi {{name}},\n\nYour free Email Sequence Templates are ready.\n\nDownload them here: https://joala.com.ng/free-download/free-email-sequence-templates-pack\n\nInside this pack, you'll find ready-to-use templates for welcome emails, sales funnels, cart abandonment, re-engagement, upsells, and follow-ups.\n\nBut if you're ready to go beyond templates and get a complete system of 6 full sequences with 24 tested templates, check out the premium pack:\nhttps://joala.com.ng/email-sequence-templates-pack.php\n\nIt includes complete email sequences you can copy, paste, and send — with subject lines, preview text, body copy, and timing recommendations.\n\nCheers,\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 2, 'delay_days' => 2, 'subject' => 'Why most email sequences fail (and how to make yours convert)', 'body' => "Hi {{name}},\n\nDid you know that the average email open rate across industries is just 21%?\n\nThe difference between a sequence that converts and one that gets ignored often comes down to three things:\n\n1. The right sequence structure\n2. Compelling subject lines\n3. Strategic timing\n\nMost people write emails randomly — one today, one next week, no real system. That's why they don't see results.\n\nThe Email Sequence Templates Pack gives you a proven framework:\n✓ Welcome Sequence (5 emails)\n✓ Launch Sequence (4 emails)\n✓ Abandoned Cart Recovery (3 emails)\n✓ Post-Purchase Thank You (3 emails)\n✓ Re-engagement Campaign (4 emails)\n✓ Weekly Newsletter Template\n\nEach template includes subject line, preview text, body, CTA, and timing. Just copy, customize, and send.\n\nSee it here: https://joala.com.ng/email-sequence-templates-pack.php\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 3, 'delay_days' => 4, 'subject' => 'Case study: How a coach doubled email revenue with proper sequences', 'body' => "Hi {{name}},\n\n\"I was emailing my list randomly — sending promos whenever I remembered. When I switched to a structured sequence system, my revenue from email doubled in one month.\"\n— Tunde A., Business Coach, Lagos\n\nTunde was where most business owners are — sending emails without a plan. After implementing the welcome, launch, and follow-up sequences, here's what changed:\n- Open rates went from 15% to 38%\n- Click-through rates tripled\n- Revenue from email campaigns doubled\n\nThe Email Sequence Templates Pack gives you the exact same system Tunde used:\n- 6 complete sequences\n- 24 tested templates\n- Subject lines, body copy, and CTAs included\n- Timing and frequency recommendations\n\nGet the same results: https://joala.com.ng/email-sequence-templates-pack.php\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 4, 'delay_days' => 6, 'subject' => 'Special offer: 15% off the Email Sequence Templates Pack', 'body' => "Hi {{name}},\n\nI'm giving you an exclusive 15% discount on the Email Sequence Templates Pack.\n\nUse code: EMAIL15 at checkout\n\nThis brings the price down to just ₦10,200 — a one-time investment for a complete email marketing system.\n\nHere's exactly what you'll get:\n• Welcome Sequence (5 emails)\n• Launch Sequence (4 emails)\n• Abandoned Cart Recovery (3 emails)\n• Post-Purchase Thank You (3 emails)\n• Re-engagement Campaign (4 emails)\n• Weekly Newsletter Template\n• Subject line swipe file\n• CTA placement guide\n• Timing & frequency recommendations\n• Lifetime free updates\n• Priority support\n\nGet it now: https://joala.com.ng/email-sequence-templates-pack.php?coupon=EMAIL15\n\nThis offer won't last forever. Grab it today.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $presaleSeq->id, 'step_order' => 5, 'delay_days' => 9, 'subject' => 'Last chance: Your 15% discount on Email Templates expires soon', 'body' => "Hi {{name}},\n\nJust a friendly reminder that your 15% discount (code: EMAIL15) on the Email Sequence Templates Pack is still available.\n\nBut I can't keep it open forever.\n\nIf you're serious about building an email system that converts subscribers into customers, now is the time.\n\nThe Email Sequence Templates Pack gives you everything you need — 6 complete sequences, 24 tested templates, ready to copy, paste, and send.\n\nGet started today: https://joala.com.ng/email-sequence-templates-pack.php?coupon=EMAIL15\n\nIf you have any questions, just reply to this email.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
        ]);
        $out[] = "Pre-sale sequence ID: {$presaleSeq->id} (5 steps)";

        // Sync sequences table
        $seq = \App\Models\Sequence::updateOrCreate(
            ['id' => $presaleSeq->id],
            ['name' => 'Email Sequence Templates Pre-Sale', 'description' => 'Nurture leads toward the premium email templates pack', 'is_active' => true]
        );
        $out[] = "Sequences table synced ID: {$seq->id}";

        // 3. Create post-purchase sequence
        $postSeq = \App\Models\EmailSequence::updateOrCreate(
            ['name' => 'Email Sequence Templates Post-Purchase'],
            [
                'name' => 'Email Sequence Templates Post-Purchase',
                'description' => 'Onboard and engage buyers of the Email Sequence Templates Pack',
                'trigger_type' => 'post_purchase',
                'is_active' => true,
            ]
        );
        \Illuminate\Support\Facades\DB::table('sequence_steps')->where('sequence_id', $postSeq->id)->delete();
        \Illuminate\Support\Facades\DB::table('sequence_steps')->insert([
            ['sequence_id' => $postSeq->id, 'step_order' => 1, 'delay_days' => 0, 'subject' => 'Your Email Sequence Templates Pack is ready!', 'body' => "Hi {{name}},\n\nThank you for purchasing the Email Sequence Templates Pack!\n\nYour download link: https://joala.com.ng/order/download/{{download_token}}\n\nGetting started fast:\n1. Download the ZIP file\n2. Extract to your computer\n3. Open the Welcome Sequence folder\n4. Read the quick-start guide\n5. Customize your first template\n6. Upload to your email marketing platform\n\nInside your pack:\n✓ Welcome Sequence (5 emails)\n✓ Launch Sequence (4 emails)\n✓ Abandoned Cart Recovery (3 emails)\n✓ Post-Purchase Thank You (3 emails)\n✓ Re-engagement Campaign (4 emails)\n✓ Weekly Newsletter Template\n✓ Subject line swipe file\n\nPro tip: Start with the Welcome Sequence — it's the highest-impact sequence and sets the tone for all future emails.\n\nIf you need help, just reply to this email.\n\nCheers,\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $postSeq->id, 'step_order' => 2, 'delay_days' => 3, 'subject' => 'Quick start: Setting up your welcome sequence', 'body' => "Hi {{name}},\n\nYour welcome sequence is the most important email sequence you'll ever set up. It's where first impressions are made.\n\nHere's a quick setup guide:\n\n1. Open the Welcome Sequence folder\n2. Review the 5-email structure:\n   - Email 1: Welcome + what to expect\n   - Email 2: Free value (your best content)\n   - Email 3: Social proof + testimonials\n   - Email 4: Soft offer\n   - Email 5: Hard offer + urgency\n3. Copy each template into your email platform\n4. Set delays: 1 day between each email\n5. Personalize with merge tags\n\nBest practices:\n- Send email 1 immediately after signup\n- Send emails in the morning (8-10am)\n- Test subject lines for high open rates\n- Track which emails get the most clicks\n\nThe templates include subject line options for each email — pick the one that fits your brand voice.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $postSeq->id, 'step_order' => 3, 'delay_days' => 5, 'subject' => 'Advanced: Crafting high-converting sales sequences', 'body' => "Hi {{name}},\n\nNow that your welcome sequence is running, let's talk about sales sequences.\n\nThe Launch Sequence in your pack is designed to promote product launches, promotions, and special offers.\n\nStructure (4 emails):\n1. Teaser — Build anticipation\n2. Announce — The big reveal\n3. Social proof — Show who's buying\n4. Urgency — Last chance\n\nQuick tips:\n- Email 1: Use curiosity-driven subject lines\n- Email 2: Lead with the biggest benefit\n- Email 3: Include customer testimonials or case studies\n- Email 4: Create genuine scarcity (limited time/quantity)\n\nThe templates are ready to go — just add your product details and launch date.\n\nRemember: Not everyone who wants to buy is ready today. The Abandoned Cart Recovery sequence handles those who showed interest but didn't purchase. Set it to trigger 24 hours after abandoned interest.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $postSeq->id, 'step_order' => 4, 'delay_days' => 7, 'subject' => 'Re-engagement strategies to win back cold subscribers', 'body' => "Hi {{name}},\n\nEvery email list has inactive subscribers — people who signed up but stopped opening emails.\n\nThe Re-engagement Campaign in your pack is designed specifically to win them back.\n\nStructure (4 emails):\n1. \"We miss you\" — Friendly reconnection\n2. \"Here's what you missed\" — Best content roundup\n3. \"Is this still relevant?\" — Survey/feedback\n4. \"Last chance to stay\" — Final re-engagement or unsubscribe option\n\nWhat if they don't re-engage?\nIt's better to remove inactive subscribers. A smaller engaged list outperforms a large disengaged list every time.\n\nThe Post-Purchase Thank You sequence is equally important — it turns one-time buyers into repeat customers.\n\nTake 30 minutes this week to set up the Re-engagement sequence. It could re-activate 10-15% of your cold list.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ['sequence_id' => $postSeq->id, 'step_order' => 5, 'delay_days' => 10, 'subject' => 'Email marketing best practices to maximize your results', 'body' => "Hi {{name}},\n\nHere are some proven email marketing tips to get the most out of your templates:\n\nSubject Lines:\n✓ Keep under 50 characters\n✓ Use personalization (name, location)\n✓ Create curiosity without being clickbait\n✓ Test 3-5 subject lines per email\n✓ Avoid spam trigger words\n\nSend Timing:\n✓ Tuesday-Thursday: Best open rates\n✓ 8-11am: Optimal send time\n✓ Test different days/times for your audience\n\nList Health:\n✓ Clean your list every 3 months\n✓ Remove non-openers after 6 months\n✓ Segment by behavior (openers, clickers, buyers)\n✓ Use re-engagement sequences before removing\n\nTemplates:\n✓ Customize each template to your brand voice\n✓ Add your own testimonials and case studies\n✓ Test different CTAs (button vs text link)\n✓ Track and optimize based on data\n\nYou have everything you need in the Email Sequence Templates Pack. The templates are proven — now it's up to you to implement them.\n\nIf you ever need help, reply to this email.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
        ]);
        $out[] = "Post-purchase sequence ID: {$postSeq->id} (5 steps)";

        // Sync sequences table
        $seq2 = \App\Models\Sequence::updateOrCreate(
            ['id' => $postSeq->id],
            ['name' => 'Email Sequence Templates Post-Purchase', 'description' => 'Onboard new buyers of email templates pack', 'is_active' => true]
        );
        $out[] = "Sequences table synced ID: {$seq2->id}";

        // 4. Find premium product
        $premiumProduct = \App\Models\Product::where('slug', 'email-sequence-templates-pack')->first();
        $premiumId = $premiumProduct ? $premiumProduct->id : 0;
        $out[] = "Premium product ID: " . ($premiumProduct ? $premiumProduct->id : 'NOT FOUND');

        // 5. Create the funnel
        $funnel = \App\Models\Funnel::updateOrCreate(
            ['slug' => 'email-sequence-templates-funnel'],
            [
                'name' => 'Email Sequence Templates Funnel',
                'slug' => 'email-sequence-templates-funnel',
                'description' => 'Lead magnet → download → checkout → pre-sale nurture',
                'goal' => 'sales',
                'funnel_type' => 'sales',
                'product_id' => $premiumId ?: null,
                'welcome_sequence_id' => $presaleSeq->id,
                'environment' => 'production',
                'is_active' => true,
                'upsell_enabled' => false,
                'countdown_enabled' => false,
            ]
        );
        $out[] = "Funnel ID: {$funnel->id}";

        // 6. Link landing page to funnel and pre-sale sequence
        try {
            $landingPage = \App\Models\LandingPage::where('slug', 'free-email-sequence-templates-pack')->first();
            if ($landingPage) {
                $landingPage->update([
                    'funnel_id' => $funnel->id,
                    'sequence_id' => $presaleSeq->id,
                ]);
                $out[] = "Landing page linked: ID {$landingPage->id}";
            } else {
                $out[] = "WARNING: Landing page 'free-email-sequence-templates-pack' not found in DB";
            }
        } catch (\Exception $e) {
            $out[] = "WARNING: Could not link landing page: " . $e->getMessage();
        }

        // 7. Create funnel stages
        \App\Models\FunnelStage::where('funnel_id', $funnel->id)->delete();

        // Stage 1: Landing page (lead capture)
        \App\Models\FunnelStage::create([
            'funnel_id' => $funnel->id,
            'name' => 'Lead Magnet Page',
            'type' => 'landing',
            'content' => ['url' => '/l/free-email-sequence-templates-pack'],
            'order' => 1,
            'delay_days' => 0,
            'is_required' => true,
            'action_on_complete' => 'advance',
        ]);
        $out[] = "Stage 1: Lead Magnet Page";

        // Stage 2: Download page
        \App\Models\FunnelStage::create([
            'funnel_id' => $funnel->id,
            'name' => 'Download Page',
            'type' => 'thank_you',
            'content' => ['url' => '/download/free-email-sequence-templates-pack'],
            'order' => 2,
            'delay_days' => 0,
            'is_required' => false,
            'action_on_complete' => 'advance',
        ]);
        $out[] = "Stage 2: Download Page";

        // Stage 3: Premium checkout
        \App\Models\FunnelStage::create([
            'funnel_id' => $funnel->id,
            'name' => 'Premium Checkout',
            'type' => 'checkout',
            'content' => ['url' => '/email-sequence-templates-pack.php'],
            'order' => 3,
            'delay_days' => 0,
            'sequence_id' => $presaleSeq->id,
            'is_required' => false,
            'action_on_complete' => 'email',
        ]);
        $out[] = "Stage 3: Premium Checkout";

        // 8. Write free product deliverable HTML file
        $freeFilePath = public_path('uploads/free-products/files/free-email-sequence-templates-pack.html');
        $dir = dirname($freeFilePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($freeFilePath, '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Free Email Sequence Templates - JoAla Ventures</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;color:#1e293b;line-height:1.7;padding:40px 20px}
.container{max-width:720px;margin:0 auto}
h1{font-size:2em;margin-bottom:8px;color:#0f172a}
.lead{font-size:1.15em;color:#64748b;margin-bottom:32px}
h2{font-size:1.4em;margin:32px 0 12px;color:#075985;border-bottom:2px solid #e2e8f0;padding-bottom:6px}
h3{font-size:1.1em;margin:20px 0 8px;color:#0c4a6e}
p{margin-bottom:12px}
ul{list-style:none;padding:0;margin:0 0 16px}
ul li{padding:8px 0 8px 28px;position:relative;border-bottom:1px solid #f1f5f9}
ul li:before{content:"\\2713";position:absolute;left:0;color:#059669;font-weight:bold}
.badge{display:inline-block;background:#dbeafe;color:#1e40af;padding:2px 12px;border-radius:20px;font-size:.85em;font-weight:600}
.footer{margin-top:40px;padding-top:24px;border-top:2px solid #e2e8f0;text-align:center;color:#94a3b8;font-size:.9em}
</style>
</head>
<body>
<div class="container">
<h1>Email Sequence Templates</h1>
<p class="lead">24 proven email templates to automate your marketing — welcome, sales, cart recovery, re-engagement, and more.</p>

<h2>1. Welcome Sequence Template</h2>
<p>First impressions matter. A well-crafted welcome sequence builds trust and sets expectations.</p>
<ul>
<li>Email 1: Warm welcome + what subscribers can expect</li>
<li>Email 2: Deliver on your promise (free resource/guide)</li>
<li>Email 3: Share your story + brand values</li>
<li>Email 4: Social proof + customer testimonials</li>
<li>Email 5: Soft CTA leading to your main offer</li>
</ul>

<h2>2. Sales Funnel / Launch Sequence</h2>
<p>Promote products, services, or launches with a structured approach that builds excitement and drives conversions.</p>
<ul>
<li>Email 1: Teaser — spark curiosity about what\'s coming</li>
<li>Email 2: Announcement — the big reveal + key benefits</li>
<li>Email 3: Social proof — share early adopter results</li>
<li>Email 4: Urgency — limited time offer + CTA</li>
</ul>

<h2>3. Cart Abandonment Recovery</h2>
<p>Recover lost sales with timely follow-ups that remind and persuade.</p>
<ul>
<li>Email 1: Friendly reminder about what they left behind</li>
<li>Email 2: Highlight benefits + address objections</li>
<li>Email 3: Offer incentive (discount/free shipping) + urgency</li>
</ul>

<h2>4. Re-engagement Campaign</h2>
<p>Win back inactive subscribers before they churn forever.</p>
<ul>
<li>Email 1: "We miss you" — gentle reconnection</li>
<li>Email 2: Show what they\'ve missed (best content roundup)</li>
<li>Email 3: Survey — ask what they want to see</li>
<li>Email 4: Final re-engagement or unsubscribe option</li>
</ul>

<h2>5. Upsell / Follow-Up Sequence</h2>
<p>Maximize customer lifetime value with strategic follow-ups after purchase.</p>
<ul>
<li>Email 1: Thank you + confirm purchase details</li>
<li>Email 2: Deliver additional value (tips, guides)</li>
<li>Email 3: Recommend complementary products/services</li>
<li>Email 4: Request review or testimonial</li>
</ul>

<h2>6. Newsletter Template Framework</h2>
<p>An ongoing content framework to stay top-of-mind with your audience.</p>
<ul>
<li>Opening: Personal note from founder/team</li>
<li>Main Content: 1-2 valuable insights or tips</li>
<li>Feature: Spotlight a customer, product, or case study</li>
<li>CTA: Guide readers to the next step</li>
<li>Closing: Warm sign-off + social links</li>
</ul>

<h2>Email Marketing Best Practices</h2>
<ul>
<li>Keep subject lines under 50 characters</li>
<li>Personalize with subscriber name and preferences</li>
<li>Send between 8-11am for optimal open rates</li>
<li>Test subject lines (A/B test 3-5 options)</li>
<li>Track open rates, click rates, and conversions</li>
<li>Clean your list quarterly — remove non-openers</li>
<li>Segment subscribers by behavior and interests</li>
<li>Always include a clear call-to-action</li>
<li>Use preview text to complement subject lines</li>
<li>Maintain consistent sending frequency</li>
</ul>

<div class="footer">
<p><strong>Want the complete system?</strong> Get the Email Sequence Templates Pack with 6 full sequences, 24 tested templates, subject line swipe file, and timing guide.</p>
<p>Visit joala.com.ng/email-sequence-templates-pack.php</p>
<p>&copy; 2026 JoAla Ventures. All rights reserved.</p>
</div>
</div>
</body>
</html>');
        $out[] = "Free product deliverable HTML created at: uploads/free-products/files/free-email-sequence-templates-pack.html";

        $out[] = "---";
        $out[] = "Landing page URL: https://joala.com.ng/l/free-email-sequence-templates-pack";
        $out[] = "Download page URL: https://joala.com.ng/download/free-email-sequence-templates-pack";
        $out[] = "Sales page URL: https://joala.com.ng/email-sequence-templates-pack.php";
        $out[] = "Funnel edit: /admin/marketing/funnels/{$funnel->id}/edit";

        return "<h2>Email Sequences Funnel Setup Complete</h2><pre>" . implode("\n", $out) . "</pre>";

    } catch (\Exception $e) {
        return "<h2>ERROR</h2><pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
    }
});

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
Route::get('/customer/notifications/unread-count', [CustomerController::class, 'getUnreadCount']);
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

        // Create customer_notifications table
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS customer_notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_email VARCHAR(255) NOT NULL,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT,
                link VARCHAR(500),
                is_read TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_customer (customer_email),
                INDEX idx_read (is_read),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
        
        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Achievements page (direct route - not via controller)
Route::get('/my-achievements', function() {
    if (!session()->has('customer_id')) {
        return redirect('/customer/login');
    }
    
    $pdo = DB::connection()->getPdo();
    $stmt = $pdo->prepare("SELECT * FROM customer_accounts WHERE id = ?");
    $stmt->execute([session('customer_id')]);
    $customer = $stmt->fetch();
    if (!$customer) {
        return redirect('/customer/login');
    }
    
    $svc = app(\App\Services\AchievementService::class);
    $achievements = $svc->getAchievementsForCustomer($customer['email']);
    $totalPoints = $svc->getTotalPoints($customer['email']);
    
    $cards = '';
    foreach ($achievements as $a) {
        $awarded = $a['is_awarded'];
        $border = $awarded ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-200';
        $badge = $awarded ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400';
        $cards .= '<div class="bg-white rounded-2xl border ' . $border . ' p-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center ' . $badge . '">
                    <i class="fas ' . $a['icon'] . ' text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">' . $a['name'] . ' ' . ($awarded ? '<i class="fas fa-check-circle text-emerald-500 ml-1"></i>' : '') . '</h3>
                    <p class="text-sm text-slate-600 mt-1">' . $a['description'] . '</p>
                    <span class="inline-flex items-center gap-1 text-xs font-medium mt-2 px-2 py-1 rounded-full ' . ($awarded ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600') . '">
                        <i class="fas fa-star"></i> ' . $a['points'] . ' pts
                    </span>
                </div>
            </div>
        </div>';
    }
    
    return '<link href="https://cdn.tailwindcss.com" rel="stylesheet">
        <div class="min-h-screen bg-slate-50 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Achievements</h1>
                        <p class="text-slate-600">Track your progress and unlock rewards</p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-amber-600">' . $totalPoints . '</div>
                        <p class="text-sm text-slate-500">Total Points</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">' . $cards . '</div>
            </div>
        </div>';
});

// Fix lesson_progress table - add customer_email column
Route::get('/fix-lesson-progress', function() {
    try {
        $pdo = DB::connection()->getPdo();
        $check = $pdo->query("SHOW COLUMNS FROM lesson_progress LIKE 'customer_email'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE lesson_progress ADD COLUMN customer_email VARCHAR(255) NOT NULL AFTER `id`");
            $pdo->exec("ALTER TABLE lesson_progress ADD INDEX idx_customer_email (customer_email)");
            return "<h2 style='color:green;'>Fixed! Added customer_email column to lesson_progress table.</h2>";
        }
        return "<h2 style='color:blue;'>customer_email column already exists. No fix needed.</h2>";
    } catch (\Exception $e) {
        return "<h2 style='color:red;'>Error: " . $e->getMessage() . "</h2>";
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
            $payouts = \Illuminate\Support\Facades\DB::table('affiliate_payouts')
                ->join('affiliates', 'affiliate_payouts.affiliate_id', '=', 'affiliates.id')
                ->select('affiliate_payouts.*', 'affiliates.name as affiliate_name', 'affiliates.email')
                ->orderBy('affiliate_payouts.created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($p) { return (array) $p; })
                ->toArray();
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
        
        $orders = \App\Models\Order::where('is_cart_abandoned', 1)
            ->latest('cart_abandoned_at')
            ->limit(20)
            ->get();
        
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

// Admin - WhatsApp Broadcasting
Route::middleware(['admin'])->prefix('admin')->group(function () {
    // Static routes FIRST (before any {id} wildcards)
    Route::get('/whatsapp', [\App\Http\Controllers\Admin\WhatsAppController::class, 'index'])->name('admin.whatsapp.index');
    Route::get('/whatsapp/create', [\App\Http\Controllers\Admin\WhatsAppController::class, 'create'])->name('admin.whatsapp.create');
    Route::post('/whatsapp', [\App\Http\Controllers\Admin\WhatsAppController::class, 'store'])->name('admin.whatsapp.store');
    Route::get('/whatsapp/contacts', [\App\Http\Controllers\Admin\WhatsAppController::class, 'contacts'])->name('admin.whatsapp.contacts');
    Route::post('/whatsapp/contacts/import', [\App\Http\Controllers\Admin\WhatsAppController::class, 'importContacts'])->name('admin.whatsapp.contacts.import');
    Route::post('/whatsapp/contacts/{id}/toggle-optin', [\App\Http\Controllers\Admin\WhatsAppController::class, 'toggleOptIn'])->name('admin.whatsapp.contacts.toggle');

    // Templates
    Route::get('/whatsapp/templates', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'index'])->name('admin.whatsapp.templates');
    Route::get('/whatsapp/templates/create', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'create'])->name('admin.whatsapp.templates.create');
    Route::post('/whatsapp/templates', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'store'])->name('admin.whatsapp.templates.store');
    Route::get('/whatsapp/templates/{id}', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'show'])->name('admin.whatsapp.templates.show');
    Route::get('/whatsapp/templates/{id}/edit', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'edit'])->name('admin.whatsapp.templates.edit');
    Route::post('/whatsapp/templates/{id}', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'update'])->name('admin.whatsapp.templates.update');
    Route::post('/whatsapp/templates/{id}/toggle', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'toggleStatus'])->name('admin.whatsapp.templates.toggle');
    Route::get('/whatsapp/templates/{id}/preview', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'preview'])->name('admin.whatsapp.templates.preview');
    Route::post('/whatsapp/templates/{id}/test', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'sendTest'])->name('admin.whatsapp.templates.test');
    Route::post('/whatsapp/templates/{id}/delete', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'destroy'])->name('admin.whatsapp.templates.destroy');

    // Flows
    Route::get('/whatsapp/flows', [\App\Http\Controllers\Admin\WhatsAppFlowController::class, 'index'])->name('admin.whatsapp.flows');
    Route::get('/whatsapp/flows/create', [\App\Http\Controllers\Admin\WhatsAppFlowController::class, 'create'])->name('admin.whatsapp.flows.create');
    Route::post('/whatsapp/flows', [\App\Http\Controllers\Admin\WhatsAppFlowController::class, 'store'])->name('admin.whatsapp.flows.store');
    Route::get('/whatsapp/flows/{id}', [\App\Http\Controllers\Admin\WhatsAppFlowController::class, 'show'])->name('admin.whatsapp.flows.show');
    Route::get('/whatsapp/flows/{id}/edit', [\App\Http\Controllers\Admin\WhatsAppFlowController::class, 'edit'])->name('admin.whatsapp.flows.edit');
    Route::post('/whatsapp/flows/{id}', [\App\Http\Controllers\Admin\WhatsAppFlowController::class, 'update'])->name('admin.whatsapp.flows.update');
    Route::post('/whatsapp/flows/{id}/deploy', [\App\Http\Controllers\Admin\WhatsAppFlowController::class, 'deploy'])->name('admin.whatsapp.flows.deploy');
    Route::post('/whatsapp/flows/{id}/delete', [\App\Http\Controllers\Admin\WhatsAppFlowController::class, 'destroy'])->name('admin.whatsapp.flows.destroy');

    // Conversations
    Route::get('/whatsapp/conversations', [\App\Http\Controllers\Admin\WhatsAppConversationController::class, 'index'])->name('admin.whatsapp.conversations');
    Route::get('/whatsapp/conversations/create', [\App\Http\Controllers\Admin\WhatsAppConversationController::class, 'create'])->name('admin.whatsapp.conversations.create');
    Route::post('/whatsapp/conversations', [\App\Http\Controllers\Admin\WhatsAppConversationController::class, 'store'])->name('admin.whatsapp.conversations.store');
    Route::get('/whatsapp/conversations/{id}', [\App\Http\Controllers\Admin\WhatsAppConversationController::class, 'show'])->name('admin.whatsapp.conversations.show');
    Route::get('/whatsapp/conversations/{id}/edit', [\App\Http\Controllers\Admin\WhatsAppConversationController::class, 'edit'])->name('admin.whatsapp.conversations.edit');
    Route::post('/whatsapp/conversations/{id}', [\App\Http\Controllers\Admin\WhatsAppConversationController::class, 'update'])->name('admin.whatsapp.conversations.update');
    Route::post('/whatsapp/conversations/{id}/toggle', [\App\Http\Controllers\Admin\WhatsAppConversationController::class, 'toggleActive'])->name('admin.whatsapp.conversations.toggle');
    Route::post('/whatsapp/conversations/{id}/delete', [\App\Http\Controllers\Admin\WhatsAppConversationController::class, 'destroy'])->name('admin.whatsapp.conversations.destroy');
    Route::get('/whatsapp/conversations/logs', [\App\Http\Controllers\Admin\WhatsAppConversationController::class, 'logs'])->name('admin.whatsapp.conversations.logs');

    // Wildcard routes LAST (so static sub-paths like /contacts, /templates match first)
    Route::get('/whatsapp/{id}', [\App\Http\Controllers\Admin\WhatsAppController::class, 'show'])->name('admin.whatsapp.show');
    Route::post('/whatsapp/{id}/send', [\App\Http\Controllers\Admin\WhatsAppController::class, 'send'])->name('admin.whatsapp.send');
    Route::post('/whatsapp/{id}/delete', [\App\Http\Controllers\Admin\WhatsAppController::class, 'destroy'])->name('admin.whatsapp.destroy');
});

// One-time setup endpoint for creating new feature tables
Route::get('/setup-new-tables', function () {
    try {
        $out = [];

        \Illuminate\Support\Facades\Schema::create('page_visits', function ($table) {
            $table->bigIncrements('id');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->text('referer')->nullable();
            $table->string('session_id')->nullable()->index();
            $table->timestamp('visited_at')->useCurrent();
            $table->index('visited_at');
        });
        $out[] = "page_visits table OK";

        if (!\Illuminate\Support\Facades\Schema::hasColumn('project_briefs', 'is_read')) {
            \Illuminate\Support\Facades\Schema::table('project_briefs', function ($table) {
                $table->boolean('is_read')->default(false)->after('notes');
            });
            $out[] = "project_briefs.is_read column added";
        } else {
            $out[] = "project_briefs.is_read already exists";
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('whatsapp_broadcasts')) {
            \Illuminate\Support\Facades\Schema::create('whatsapp_broadcasts', function ($table) {
                $table->id();
                $table->string('name');
                $table->text('message');
                $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'failed'])->default('draft');
                $table->timestamp('scheduled_at')->nullable();
                $table->integer('total_recipients')->default(0);
                $table->integer('sent_count')->default(0);
                $table->integer('failed_count')->default(0);
                $table->json('log')->nullable();
                $table->timestamps();
            });
            $out[] = "whatsapp_broadcasts table created";
        } else {
            $out[] = "whatsapp_broadcasts already exists";
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('whatsapp_contacts')) {
            \Illuminate\Support\Facades\Schema::create('whatsapp_contacts', function ($table) {
                $table->id();
                $table->unsignedBigInteger('lead_id')->nullable();
                $table->string('phone', 20);
                $table->boolean('opted_in')->default(true);
                $table->timestamp('last_sent_at')->nullable();
                $table->timestamps();
                $table->unique(['lead_id', 'phone']);
            });
            $out[] = "whatsapp_contacts table created";
        } else {
            $out[] = "whatsapp_contacts already exists";
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('leads', 'phone')) {
            \Illuminate\Support\Facades\Schema::table('leads', function ($table) {
                $table->string('phone', 20)->nullable()->after('email');
            });
            $out[] = "leads.phone column added";
        } else {
            $out[] = "leads.phone already exists";
        }

        return "<h2>Setup Complete</h2><pre>" . implode("\n", $out) . "</pre>";
    } catch (\Exception $e) {
        return "<h2>Error</h2><pre>" . $e->getMessage() . "</pre>";
    }
});

// Dedicated WhatsApp Tables Setup
Route::get('/setup-whatsapp-tables', function () {
    try {
        $out = [];

        if (!\Illuminate\Support\Facades\Schema::hasColumn('whatsapp_broadcasts', 'payload')) {
            \Illuminate\Support\Facades\Schema::table('whatsapp_broadcasts', function ($table) {
                $table->json('payload')->nullable()->after('message');
            });
            $out[] = "whatsapp_broadcasts.payload column added";
        } else {
            $out[] = "whatsapp_broadcasts.payload already exists";
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('whatsapp_broadcasts', 'template_id')) {
            \Illuminate\Support\Facades\Schema::table('whatsapp_broadcasts', function ($table) {
                $table->unsignedBigInteger('template_id')->nullable()->after('payload');
            });
            $out[] = "whatsapp_broadcasts.template_id column added";
        } else {
            $out[] = "whatsapp_broadcasts.template_id already exists";
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('whatsapp_broadcasts')) {
            \Illuminate\Support\Facades\Schema::create('whatsapp_broadcasts', function ($table) {
                $table->id();
                $table->string('name');
                $table->text('message');
                $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'failed'])->default('draft');
                $table->timestamp('scheduled_at')->nullable();
                $table->integer('total_recipients')->default(0);
                $table->integer('sent_count')->default(0);
                $table->integer('failed_count')->default(0);
                $table->json('log')->nullable();
                $table->timestamps();
            });
            $out[] = "whatsapp_broadcasts table created";
        } else {
            $out[] = "whatsapp_broadcasts already exists";
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('whatsapp_contacts')) {
            \Illuminate\Support\Facades\Schema::create('whatsapp_contacts', function ($table) {
                $table->id();
                $table->unsignedBigInteger('lead_id')->nullable();
                $table->string('phone', 20);
                $table->boolean('opted_in')->default(true);
                $table->timestamp('last_sent_at')->nullable();
                $table->timestamps();
                $table->unique(['lead_id', 'phone']);
            });
            $out[] = "whatsapp_contacts table created";
        } else {
            $out[] = "whatsapp_contacts already exists";
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('leads', 'phone')) {
            \Illuminate\Support\Facades\Schema::table('leads', function ($table) {
                $table->string('phone', 20)->nullable()->after('email');
            });
            $out[] = "leads.phone column added";
        } else {
            $out[] = "leads.phone already exists";
        }

        // Advanced tables
        if (!\Illuminate\Support\Facades\Schema::hasTable('whatsapp_templates')) {
            \Illuminate\Support\Facades\Schema::create('whatsapp_templates', function ($table) {
                $table->id();
                $table->string('name');
                $table->enum('category', ['marketing', 'utility', 'authentication'])->default('marketing');
                $table->enum('message_type', ['text', 'interactive', 'media', 'template', 'flow'])->default('text');
                $table->string('header_type')->nullable();
                $table->text('header_value')->nullable();
                $table->text('body');
                $table->text('footer')->nullable();
                $table->json('buttons')->nullable();
                $table->json('sections')->nullable();
                $table->string('media_url')->nullable();
                $table->string('catalog_id')->nullable();
                $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
                $table->timestamps();
            });
            $out[] = "whatsapp_templates table created";
        } else {
            $out[] = "whatsapp_templates already exists";
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('whatsapp_flows')) {
            \Illuminate\Support\Facades\Schema::create('whatsapp_flows', function ($table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('flow_id')->nullable();
                $table->json('flow_json');
                $table->json('flow_data')->nullable();
                $table->enum('status', ['draft', 'deployed', 'archived'])->default('draft');
                $table->timestamps();
            });
            $out[] = "whatsapp_flows table created";
        } else {
            $out[] = "whatsapp_flows already exists";
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('whatsapp_conversations')) {
            \Illuminate\Support\Facades\Schema::create('whatsapp_conversations', function ($table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('trigger_event', ['lead_created', 'purchase_made', 'broadcast_reply', 'manual', 'schedule'])->default('manual');
                $table->json('steps');
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
            $out[] = "whatsapp_conversations table created";
        } else {
            $out[] = "whatsapp_conversations already exists";
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('whatsapp_conversation_logs')) {
            \Illuminate\Support\Facades\Schema::create('whatsapp_conversation_logs', function ($table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->unsignedBigInteger('contact_id');
                $table->integer('current_step')->default(0);
                $table->enum('status', ['active', 'completed', 'exited'])->default('active');
                $table->text('last_response')->nullable();
                $table->timestamp('last_step_at')->nullable();
                $table->timestamps();
                $table->foreign('conversation_id')->references('id')->on('whatsapp_conversations')->onDelete('cascade');
                $table->foreign('contact_id')->references('id')->on('whatsapp_contacts')->onDelete('cascade');
            });
            $out[] = "whatsapp_conversation_logs table created";
        } else {
            $out[] = "whatsapp_conversation_logs already exists";
        }

        return "<h2>WhatsApp Setup Complete</h2><pre>" . implode("\n", $out) . "</pre>";
    } catch (\Exception $e) {
        return "<h2>Error</h2><pre>" . $e->getMessage() . "</pre>";
    }
});

// Refresh autoloader
Route::get('/dump-autoload', function () {
    $key = request('key', '');
    if ($key !== 'joala2024') { return "Invalid key"; }
    try {
        exec('composer dump-autoload 2>&1', $output, $code);
        return "<h2>dump-autoload (exit code: $code)</h2><pre>" . implode("\n", $output) . "</pre>";
    } catch (\Exception $e) {
        return "<h2>Error</h2><pre>" . $e->getMessage() . "</pre>";
    }
});

// Diagnostic: Check what's going wrong with WhatsApp pages
Route::get('/debug-whatsapp', function () {
    $key = request('key', '');
    if ($key !== 'joala2024') { return "Invalid key"; }

    $out = [];

    // Check class existence
    $classes = [
        'App\Models\Lead',
        'App\Models\Segment',
        'App\Models\WhatsAppBroadcast',
        'App\Models\WhatsAppContact',
        'App\Models\WhatsAppTemplate',
        'App\Models\WhatsAppFlow',
        'App\Models\WhatsAppConversation',
        'App\Services\WhatsAppBroadcastService',
    ];
    foreach ($classes as $class) {
        try {
            $exists = class_exists($class, true);
            $out[] = ($exists ? 'OK' : 'NOT FOUND') . " – $class";
        } catch (\Exception $e) {
            $out[] = 'ERROR – '.$class.': '.$e->getMessage();
        } catch (\Throwable $e) {
            $out[] = 'FATAL – '.$class.': '.$e->getMessage();
        }
    }

    // Check tables
    $tables = ['leads', 'segments', 'whatsapp_broadcasts', 'whatsapp_contacts', 'whatsapp_templates', 'whatsapp_flows', 'whatsapp_conversations'];
    foreach ($tables as $table) {
        try {
            $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
            $out[] = ($exists ? 'OK' : 'MISSING') . " – table '$table'";
        } catch (\Exception $e) {
            $out[] = 'ERROR – table \''.$table.'\': '.$e->getMessage();
        }
    }

    // Test a simple query on each
    $queries = [
        'Segment::count()' => fn() => \App\Models\Segment::count(),
        'Lead::count()' => fn() => \App\Models\Lead::count(),
        'WhatsAppContact::count()' => fn() => \App\Models\WhatsAppContact::count(),
        'WhatsAppTemplate::count()' => fn() => class_exists('App\Models\WhatsAppTemplate') ? \App\Models\WhatsAppTemplate::count() : 'class not found',
    ];
    foreach ($queries as $label => $fn) {
        try {
            $result = $fn();
            $out[] = "OK – $label = $result";
        } catch (\Exception $e) {
            $out[] = 'ERROR – '.$label.': '.$e->getMessage();
        }
    }

    return "<h2>WhatsApp Diagnostics</h2><pre>" . implode("\n", $out) . "</pre>";
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
                app(\App\Services\Marketing\MarketingService::class)->enrollLeadInSequence($lead, $seq->id);
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
                    app(\App\Services\Marketing\MarketingService::class)->enrollLeadInSequence($lead, $seq->id);
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
                    app(\App\Services\Marketing\MarketingService::class)->enrollLeadInSequence($lead, $seq->id);
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
            app(\App\Services\Marketing\MarketingService::class)->enrollLeadInSequence($lead, $seq->id);
            $out .= "<p style='color:green'>Enrollment attempted via MarketingService</p>";
        } catch (\Exception $e) {
            $out .= "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
        }
    }

    return $out;
});

// Setup branching for all 3 funnels (adds re-engagement stage + conditional checkout)
Route::get('/setup-branching', function () {
    try {
        $out = [];
        $funnels = [
            'ecommerce-starter-kit-funnel' => [
                'name' => 'E-Commerce Starter Kit',
                'presale_seq_name' => 'E-Commerce Starter Kit Pre-Sale',
                'slug' => 'ecommerce-starter-kit',
                're_seq_name' => 'E-Commerce Starter Kit Re-Engagement',
                're_seq_desc' => 'Re-engage leads who haven\'t purchased the E-Commerce Starter Kit',
            ],
            'whatsapp-marketing-bundle-funnel' => [
                'name' => 'WhatsApp Marketing Bundle',
                'presale_seq_name' => 'WhatsApp Marketing Bundle Pre-Sale',
                'slug' => 'whatsapp-marketing-bundle',
                're_seq_name' => 'WhatsApp Marketing Bundle Re-Engagement',
                're_seq_desc' => 'Re-engage leads who haven\'t purchased the WhatsApp Bundle',
            ],
            'email-sequence-templates-funnel' => [
                'name' => 'Email Sequence Templates',
                'presale_seq_name' => 'Email Sequence Templates Pre-Sale',
                'slug' => 'email-sequence-templates-pack',
                're_seq_name' => 'Email Sequence Templates Re-Engagement',
                're_seq_desc' => 'Re-engage leads who haven\'t purchased the Email Templates Pack',
            ],
        ];

        foreach ($funnels as $funnelSlug => $config) {
            $funnel = \App\Models\Funnel::where('slug', $funnelSlug)->first();
            if (!$funnel) {
                $out[] = "Funnel '{$funnelSlug}' not found — skipping";
                continue;
            }
            $out[] = "--- Processing: {$config['name']} (ID {$funnel->id}) ---";

            // 1. Create re-engagement sequence
            $reSeq = \App\Models\EmailSequence::updateOrCreate(
                ['name' => $config['re_seq_name']],
                [
                    'name' => $config['re_seq_name'],
                    'description' => $config['re_seq_desc'],
                    'trigger_type' => 'welcome',
                    'is_active' => true,
                ]
            );
            \Illuminate\Support\Facades\DB::table('sequence_steps')->where('sequence_id', $reSeq->id)->delete();
            \Illuminate\Support\Facades\DB::table('sequence_steps')->insert([
                ['sequence_id' => $reSeq->id, 'step_order' => 1, 'delay_days' => 0, 'subject' => 'Did you get your free guide? + Special offer inside', 'body' => "Hi {{name}},\n\nI noticed you downloaded the free guide recently but haven't grabbed the premium version yet.\n\nI wanted to check in — do you have any questions about what's included?\n\nHere's what you're getting with the premium pack:\n✓ Everything in the free guide, supercharged\n✓ Done-for-you templates ready to use\n✓ Step-by-step implementation guides\n✓ Lifetime access + free updates\n\nCheck it out: https://joala.com.ng/store/{$config['slug']}\n\nReply if you have any questions!\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
                ['sequence_id' => $reSeq->id, 'step_order' => 2, 'delay_days' => 1, 'subject' => 'Still thinking about it? Here\'s what you\'ll miss', 'body' => "Hi {{name}},\n\nStill on the fence? I get it — you want to make sure it's worth it.\n\nHere's what customers say they wish they'd done sooner:\n\n1. Stop guessing — use proven templates instead of reinventing the wheel\n2. Save 20+ hours with ready-to-use workflows\n3. Start seeing results in days, not weeks\n\nThe free guide gives you the foundation. The premium pack gives you the complete system — and that's where the real results come from.\n\nSee what's inside: https://joala.com.ng/store/{$config['slug']}\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
                ['sequence_id' => $reSeq->id, 'step_order' => 3, 'delay_days' => 2, 'subject' => 'How one customer got 3x ROI in the first month', 'body' => "Hi {{name}},\n\n\"I was skeptical at first, but the templates saved me weeks of work. I implemented the system in one weekend and saw 3x ROI in the first month.\"\n— A recent customer\n\nThis is what happens when you stop piecing things together and use a complete, proven system.\n\nThe premium pack includes everything you need:\n✓ Ready-to-use templates\n✓ Implementation guides\n✓ Best practices from years of experience\n✓ Lifetime updates\n✓ Priority support\n\nDon't wait — start seeing results today.\n\nGet it here: https://joala.com.ng/store/{$config['slug']}\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
                ['sequence_id' => $reSeq->id, 'step_order' => 4, 'delay_days' => 3, 'subject' => 'Special offer: 20% off — just for you', 'body' => "Hi {{name}},\n\nI'm giving you an exclusive 20% discount to make this an easy yes.\n\nUse code: REENGAGE20 at checkout\n\nThis is a limited offer, so grab it while it's available:\nhttps://joala.com.ng/store/{$config['slug']}?coupon=REENGAGE20\n\nHere's why this is a no-brainer:\n- Proven templates that work\n- Complete system, not just fragments\n- Save hours of work\n- Lifetime access\n- 20% off right now\n\nDon't let this opportunity pass.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
                ['sequence_id' => $reSeq->id, 'step_order' => 5, 'delay_days' => 4, 'subject' => 'Last call: This offer won\'t last', 'body' => "Hi {{name}},\n\nThis is your last chance to grab the {$config['name']} with 20% off.\n\nAfter today, the discount code REENGAGE20 expires.\n\nIf you're serious about growing your business, now is the time. The templates, workflows, and systems in this pack will save you weeks of work and help you get results faster.\n\nGet it now: https://joala.com.ng/store/{$config['slug']}?coupon=REENGAGE20\n\nIf money is tight, reply to this email and let me know. I might be able to work something out.\n\nJome\njoala.com.ng", 'created_at' => now(), 'updated_at' => now()],
            ]);
            $out[] = "Re-engagement sequence ID: {$reSeq->id} (5 steps)";

            // Sync sequences table
            \App\Models\Sequence::updateOrCreate(
                ['id' => $reSeq->id],
                ['name' => $config['re_seq_name'], 'description' => $config['re_seq_desc'], 'is_active' => true]
            );
            $out[] = "Sequences table synced: {$reSeq->id}";

            // 2. Get existing stages to find Stage 3
            $stages = $funnel->stages()->orderBy('order')->get();
            $stage3 = $stages->where('order', 3)->first();
            if (!$stage3) {
                // Try by name
                $stage3 = $stages->firstWhere('type', 'checkout');
            }

            // 3. Update Stage 3 (checkout) with condition + branching
            if ($stage3) {
                $stage3->update([
                    'condition_type' => 'wait',
                    'wait_duration_hours' => 216, // 9 days (pre-sale duration)
                    'redirect_type' => 'conditional',
                    'conditional_stages' => [
                        ['condition' => 'converted', 'stage_id' => null, 'action' => 'complete'],
                        ['condition' => 'not_converted', 'stage_id' => null], // will be set after Stage 4 is created
                        ['condition' => 'default', 'stage_id' => null],
                    ],
                ]);
                $out[] = "Stage 3 '{$stage3->name}' updated with wait (9d) + conditional branching";
            } else {
                $out[] = "WARNING: No checkout stage found for funnel {$funnel->id}";
            }

            // 4. Create Stage 4 (Re-engagement)
            $stage4 = \App\Models\FunnelStage::updateOrCreate(
                ['funnel_id' => $funnel->id, 'order' => 4],
                [
                    'funnel_id' => $funnel->id,
                    'name' => 'Re-engagement',
                    'type' => 'email',
                    'order' => 4,
                    'delay_days' => 0,
                    'sequence_id' => $reSeq->id,
                    'is_required' => false,
                    'action_on_complete' => 'email',
                    'condition_type' => 'wait',
                    'wait_duration_hours' => 120, // 5 days for re-engagement sequence
                    'redirect_type' => 'conditional',
                    'conditional_stages' => [
                        ['condition' => 'converted', 'stage_id' => null, 'action' => 'complete'],
                        ['condition' => 'default', 'stage_id' => null, 'action' => 'complete'],
                    ],
                ]
            );
            $out[] = "Stage 4 '{$stage4->name}' created with re-engagement sequence {$reSeq->id}";

            // 5. Update Stage 3's conditional_stages to point to Stage 4
            if ($stage3) {
                $stage3->update([
                    'conditional_stages' => [
                        ['condition' => 'converted', 'stage_id' => null, 'action' => 'complete'],
                        ['condition' => 'not_converted', 'stage_id' => $stage4->id],
                        ['condition' => 'default', 'stage_id' => $stage4->id],
                    ],
                ]);
                $out[] = "Stage 3 conditional_stages updated → converted=complete, not_converted=Stage {$stage4->id}";
            }
        }

        $out[] = "========================================";
        $out[] = "Branching setup complete for all 3 funnels!";
        $out[] = "Flow: Stage 1 → Stage 2 → Stage 3 (wait 9d) → branch → Stage 4 (wait 5d) → exit";

        return "<h2>Branching Setup Complete</h2><pre>" . implode("\n", $out) . "</pre>";

    } catch (\Exception $e) {
        return "<h2>ERROR</h2><pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
    }
});

// Debug: check if layout has notifications link
Route::get('/debug-notifications-link', function () {
    $path = resource_path('views/front/customer/layout.blade.php');
    if (!file_exists($path)) return "LAYOUT FILE NOT FOUND at $path";
    $matches = [];
    preg_match('/<nav[^>]*>.*?<\/nav>/s', file_get_contents($path), $matches);
    $navHtml = $matches[0] ?? 'NO NAV FOUND';
    $hasNotif = str_contains($navHtml, 'Notifications') ? 'YES' : 'NO';
    $hasBell = str_contains($navHtml, 'fa-bell') ? 'YES' : 'NO';
    return "Nav links found: $hasNotif (Notifications text), $hasBell (bell icon)\n\nNav HTML:\n" . htmlspecialchars($navHtml);
});

// Debug: render just the sidebar nav HTML from the layout
Route::get('/debug-sidebar-html', function () {
    try {
        $html = view('front.customer.layout')->render();
        $matches = [];
        preg_match('/<nav[^>]*class="p-4 space-y-2"[^>]*>.*?<\/nav>/s', $html, $matches);
        $navHtml = $matches[0] ?? 'SIDEBAR NAV NOT FOUND';
        return '<pre style="background:#1e293b;color:#e2e8f0;padding:20px;font-size:13px;overflow:auto;max-height:100vh;">' . htmlspecialchars($navHtml) . '</pre>';
    } catch (\Exception $e) {
        return "RENDER ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
    }
});

// Clear compiled view cache
Route::get('/clear-view-cache', function () {
    $output = [];
    try {
        $files = glob(storage_path('framework/views/*'));
        $count = 0;
        foreach ($files as $file) {
            if (is_file($file) && !str_ends_with($file, '.gitkeep')) {
                unlink($file);
                $count++;
            }
        }
        $output[] = "View cache cleared ($count files removed)";
    } catch (\Exception $e) {
        $output[] = "View cache error: " . $e->getMessage();
    }
    if (function_exists('opcache_reset')) {
        try {
            opcache_reset();
            $output[] = "OPcache reset successfully";
        } catch (\Exception $e) {
            $output[] = "OPcache reset error: " . $e->getMessage();
        }
    } else {
        $output[] = "OPcache not available";
    }
    return implode("\n", $output);
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


