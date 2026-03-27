<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ProjectBrief;
use App\Models\Setting;
use Illuminate\Http\Request;

class BriefController extends Controller
{
    public function create()
    {
        $settings = Setting::getAll();
        return view('front.brief.create', compact('settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
            'project_type' => 'required|string',
            'description' => 'required|string|min:20',
            'budget_range' => 'nullable|string',
            'timeline' => 'nullable|string',
        ]);

        $brief = ProjectBrief::create($request->all());

        // Send notification (implement email/WhatsApp here)
        
        return redirect()->route('brief.create')->with('success', 'Project brief submitted successfully! We will contact you soon.');
    }
}