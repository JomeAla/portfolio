<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query();
        
        if ($request->category) {
            $query->where('category', $request->category);
        }
        
        $projects = $query->orderBy('id')->paginate(12);
        
        return view('front.portfolio.index', compact('projects'));
    }

    public function show(Project $project)
    {
        return view('front.portfolio.show', compact('project'));
    }
}
