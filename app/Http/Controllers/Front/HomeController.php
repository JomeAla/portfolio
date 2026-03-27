<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\Product;
use App\Models\PromoBanner;
use App\Models\Service;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $settings = [];
        try {
            $settings = Setting::getAll() ?: [];
        } catch (\Exception $e) {
            // Use defaults if settings fail
        }
        
        $featuredProjects = Project::where('is_featured', true)
            ->orderBy('id', 'desc')
            ->limit(4)
            ->get();
        
        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('order')
            ->limit(6)
            ->get();

        $featuredProducts = Product::where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('id', 'desc')
            ->limit(4)
            ->get();

        $activeBanners = PromoBanner::activeBanners();

        $services = Service::where('is_active', true)
            ->orderBy('order')
            ->get();
        
        return view('front.home', [
            'settings' => $settings,
            'featuredProjects' => $featuredProjects,
            'testimonials' => $testimonials,
            'featuredProducts' => $featuredProducts,
            'activeBanners' => $activeBanners,
            'services' => $services,
        ]);
    }
}
