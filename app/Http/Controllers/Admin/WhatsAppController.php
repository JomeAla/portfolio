<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Segment;
use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppContact;
use App\Services\WhatsAppBroadcastService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected WhatsAppBroadcastService $whatsapp;

    public function __construct(WhatsAppBroadcastService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        $broadcasts = WhatsAppBroadcast::latest()->get();
        $contactCount = WhatsAppContact::count();
        $optedInCount = WhatsAppContact::where('opted_in', true)->count();
        $sentBroadcasts = WhatsAppBroadcast::where('status', 'sent')->count();

        return view('admin.whatsapp.index', compact(
            'broadcasts', 'contactCount', 'optedInCount', 'sentBroadcasts'
        ));
    }

    public function create()
    {
        try {
            $html = '<h2>Create method reached</h2><p>Querying data...</p>';
            $segments = collect();
            try { $segments = Segment::where('is_active', true)->get(); $html .= "<p>Segments: OK (" . $segments->count() . ")</p>"; } catch (\Throwable $e) { $html .= "<p>Segments: " . $e->getMessage() . "</p>"; }
            $leadCount = 0;
            try { $leadCount = Lead::count(); $html .= "<p>Leads: OK ($leadCount)</p>"; } catch (\Throwable $e) { $html .= "<p>Leads: " . $e->getMessage() . "</p>"; }
            $contactCount = 0;
            try { $contactCount = WhatsAppContact::where('opted_in', true)->count(); $html .= "<p>Contacts: OK ($contactCount)</p>"; } catch (\Throwable $e) { $html .= "<p>Contacts: " . $e->getMessage() . "</p>"; }
            $templates = collect();
            try { $tc = 'App\Models\WhatsAppTemplate'; $templates = $tc::where('status', 'active')->get(); $html .= "<p>Templates: OK (" . $templates->count() . ")</p>"; } catch (\Throwable $e) { $html .= "<p>Templates: " . $e->getMessage() . "</p>"; }
            $html .= "<p>Rendering view...</p>";
            return view('admin.whatsapp.create', compact('segments', 'leadCount', 'contactCount', 'templates'));
        } catch (\Throwable $e) {
            return response('<h2>Create error</h2><pre>' . $e->getMessage() . "\nFile: " . $e->getFile() . ':' . $e->getLine() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre>', 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'nullable|string',
            'message_type' => 'required|in:text,template',
            'template_id' => 'required_if:message_type,template|exists:whatsapp_templates,id',
            'schedule' => 'nullable|date',
            'scope' => 'required|in:all,segment,custom',
            'segment_id' => 'required_if:scope,segment|exists:segments,id',
        ]);

        try {
            $payload = null;
            $message = $request->message ?? '';

            if ($request->message_type === 'template' && $request->template_id) {
                $template = WhatsAppTemplate::findOrFail($request->template_id);
                $payload = $this->whatsapp->buildTemplatePayload($template);
                $message = $template->body;
            } elseif (empty($message)) {
                return back()->with('error', 'Message body is required for text broadcasts.')->withInput();
            }

            $broadcast = WhatsAppBroadcast::create([
                'name' => $request->name,
                'message' => $message,
                'payload' => $payload,
                'template_id' => $request->template_id,
                'status' => $request->schedule ? 'scheduled' : 'draft',
                'scheduled_at' => $request->schedule,
            ]);

            if ($request->schedule) {
                return redirect('/admin/whatsapp')->with('success', 'Broadcast scheduled for ' . $request->schedule);
            }

            return redirect('/admin/whatsapp/' . $broadcast->id)->with('success', 'Broadcast created as draft.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $broadcast = WhatsAppBroadcast::with('template')->findOrFail($id);
        return view('admin.whatsapp.show', compact('broadcast'));
    }

    public function send(Request $request, $id)
    {
        $broadcast = WhatsAppBroadcast::findOrFail($id);

        if ($broadcast->status === 'sent') {
            return back()->with('error', 'This broadcast has already been sent.');
        }

        try {
            $scope = $request->scope ?? 'all';
            $result = match ($scope) {
                'segment' => $this->whatsapp->sendToSegment($request->segment_id, $broadcast),
                default => $this->whatsapp->sendToAllLeads($broadcast),
            };

            return redirect('/admin/whatsapp')->with('success',
                "Broadcast sent. Delivered: {$result['sent']}, Failed: {$result['failed']}"
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $broadcast = WhatsAppBroadcast::findOrFail($id);
        $broadcast->delete();
        return redirect('/admin/whatsapp')->with('success', 'Broadcast deleted.');
    }

    public function contacts()
    {
        $contacts = WhatsAppContact::with('lead')->latest()->paginate(20);
        return view('admin.whatsapp.contacts', compact('contacts'));
    }

    public function importContacts(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'lead_id' => 'nullable|exists:leads,id',
            'name' => 'nullable|string|max:255',
        ]);

        try {
            if ($request->lead_id) {
                $this->whatsapp->syncLeadPhone($request->lead_id, $request->phone);
            } else {
                $lead = Lead::create([
                    'name' => $request->name ?? 'Imported',
                    'email' => $request->phone . '@imported.whatsapp',
                    'phone' => $request->phone,
                ]);
                $this->whatsapp->syncLeadPhone($lead->id, $request->phone);
            }
            return back()->with('success', 'Contact imported.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function toggleOptIn($id)
    {
        $contact = WhatsAppContact::findOrFail($id);
        $contact->update(['opted_in' => !$contact->opted_in]);
        return back()->with('success', 'Contact opt-in status updated.');
    }
}
