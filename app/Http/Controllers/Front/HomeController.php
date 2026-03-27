<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Setting;
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
        
        return view('front.home', [
            'settings' => $settings,
            'featuredProjects' => collect([]),
            'testimonials' => collect([]),
        ]);
    }
}
