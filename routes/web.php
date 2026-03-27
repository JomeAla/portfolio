<?php

use Illuminate\Support\Facades\Route;
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

// Public Routes
Route::get('/test', function() {
    return 'Server is working!';
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
Route::get('/services', [FrontServiceController::class, 'index'])->name('services');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/brief', [FrontBriefController::class, 'create'])->name('brief.create');
Route::post('/brief', [FrontBriefController::class, 'store'])->name('brief.store');

// Store Routes
Route::get('/store', [StoreController::class, 'index'])->name('store');
Route::get('/store/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::post('/store/validate-coupon', [StoreController::class, 'validateCoupon'])->name('store.coupon');
Route::post('/store/initiate-payment', [OrderController::class, 'initiatePayment'])->name('order.initiate');
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
        
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
        Route::post('/orders/{order}/resend', [AdminOrderController::class, 'resendEmail'])->name('admin.orders.resend');
        
        Route::resource('coupons', CouponController::class);
        Route::resource('banners', BannerController::class);
        
        Route::get('/support', [SupportController::class, 'index'])->name('admin.support');
        Route::get('/support/{ticket}', [SupportController::class, 'show'])->name('admin.support.show');
        Route::put('/support/{ticket}', [SupportController::class, 'update'])->name('admin.support.update');
        Route::delete('/support/{ticket}', [SupportController::class, 'destroy'])->name('admin.support.destroy');
        
        Route::get('/briefs', [BriefController::class, 'index'])->name('admin.briefs');
        Route::get('/briefs/{brief}', [BriefController::class, 'show'])->name('admin.briefs.show');
        Route::put('/briefs/{brief}', [BriefController::class, 'update'])->name('admin.briefs.update');
        Route::delete('/briefs/{brief}', [BriefController::class, 'destroy'])->name('admin.briefs.destroy');
        
        // Invoice Routes
        Route::resource('invoices', AdminInvoiceController::class)->names('admin.invoices');
        Route::post('/invoices/{invoice}/send', [AdminInvoiceController::class, 'sendInvoice'])->name('admin.invoices.send');
        Route::post('/invoices/{invoice}/payment-link', [AdminInvoiceController::class, 'generatePaymentLink'])->name('admin.invoices.payment-link');
        Route::post('/invoices/{invoice}/mark-paid', [AdminInvoiceController::class, 'markAsPaid'])->name('admin.invoices.mark-paid');
    });
});