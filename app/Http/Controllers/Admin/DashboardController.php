<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\ProjectBrief;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'projects' => Project::count(),
            'services' => Service::count(),
            'testimonials' => Testimonial::count(),
            'briefs' => ProjectBrief::count(),
            'new_briefs' => ProjectBrief::where('status', 'new')->count(),
        ];

        $recentBriefs = ProjectBrief::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentBriefs'));
    }
}