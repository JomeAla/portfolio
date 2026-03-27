<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ProjectBrief;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

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

        // Send email notification to admin
        $this->sendAdminNotification($brief);
        
        return redirect()->route('brief.create')->with('success', 'Project brief submitted successfully! We will contact you soon.');
    }

    protected function sendAdminNotification($brief)
    {
        try {
            $adminEmail = Setting::get('contact_email', 'jomealawuru@hotmail.com');
            
            Config::set('mail.default', 'log');
            Config::set('mail.from.address', 'noreply@joala.com.ng');
            Config::set('mail.from.name', 'JoAla Website');
            Config::set('mail.mailers.log', ['transport' => 'log']);

            Mail::send('emails.new_brief', ['brief' => $brief], function ($message) use ($brief, $adminEmail) {
                $message->to($adminEmail)
                        ->subject('New Project Brief: ' . $brief->project_type);
            });
        } catch (\Exception $e) {
            // Silent fail - don't interrupt user experience
        }
    }
}