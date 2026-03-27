<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $settings = Setting::getAll();
        $featuredProjects = Project::where('is_featured', true)->take(4)->get();
        $testimonials = Testimonial::where('is_active', true)->orderBy('order')->take(6)->get();
        
        return view('front.home', compact('settings', 'featuredProjects', 'testimonials'));
    }
}