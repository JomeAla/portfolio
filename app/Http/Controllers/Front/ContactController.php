<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $settings = Setting::getAll();
        return view('front.contact', compact('settings'));
    }

    public function support(): View
    {
        return view('front.support');
    }

    public function submitSupport(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open',
        ]);

        // Send email notification to admin
        $this->sendAdminNotification($ticket);

        return back()->with('success', 'Your message has been sent! We will get back to you shortly.');
    }

    protected function sendAdminNotification($ticket)
    {
        try {
            $adminEmail = Setting::get('contact_email', 'jomealawuru@hotmail.com');
            
            Config::set('mail.default', 'log');
            Config::set('mail.from.address', 'noreply@joala.com.ng');
            Config::set('mail.from.name', 'JoAla Website');
            Config::set('mail.mailers.log', ['transport' => 'log']);

            Mail::send('emails.new_ticket', ['ticket' => $ticket], function ($message) use ($ticket, $adminEmail) {
                $message->to($adminEmail)
                        ->subject('New Contact: ' . $ticket->subject);
            });
        } catch (\Exception $e) {
            // Silent fail - don't interrupt user experience
        }
    }
}
