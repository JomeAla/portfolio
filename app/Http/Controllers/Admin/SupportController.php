<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::orderBy('id', 'desc')->paginate(15);
        return view('admin.support.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket)
    {
        return view('admin.support.show', compact('ticket'));
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'admin_response' => 'required|string',
            'status' => 'required|string|in:open,pending,closed',
        ]);

        $ticket->update([
            'admin_response' => $request->admin_response,
            'status' => $request->status,
            'responded_at' => now(),
        ]);

        $this->sendResponseEmail($ticket);

        return back()->with('success', 'Response sent to customer.');
    }

    protected function sendResponseEmail($ticket)
    {
        try {
            $fromAddress = Setting::get('mail_from_address', 'support@joala.com.ng');
            $fromName = Setting::get('mail_from_name', 'JoAla Support');

            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);

            Mail::send('emails.support_response', ['ticket' => $ticket], function ($message) use ($ticket) {
                $message->to($ticket->email, $ticket->name)
                        ->subject('Re: ' . $ticket->subject . ' - Ticket #' . $ticket->ticket_number);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send support response email: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy(SupportTicket $ticket)
    {
        $ticket->delete();
        return redirect('/admin/support')->with('success', 'Ticket deleted.');
    }
}
