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
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\PortfolioController;
use App\Http\Controllers\Front\ServiceController as FrontServiceController;
use App\Http\Controllers\Front\AboutController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\BriefController as FrontBriefController;

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
        
        Route::resource('projects', ProjectController::class)->except(['show']);
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::resource('testimonials', TestimonialController::class)->except(['show']);
        Route::resource('pages', PageController::class)->only(['index', 'edit', 'update']);
        
        Route::get('/briefs', [BriefController::class, 'index'])->name('admin.briefs');
        Route::get('/briefs/{brief}', [BriefController::class, 'show'])->name('admin.briefs.show');
        Route::put('/briefs/{brief}', [BriefController::class, 'update'])->name('admin.briefs.update');
        Route::delete('/briefs/{brief}', [BriefController::class, 'destroy'])->name('admin.briefs.destroy');
    });
});