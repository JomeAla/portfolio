<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Segment;
use App\Models\Setting;
use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppContact;
use App\Models\WhatsAppGroup;
use App\Services\WhatsAppBroadcastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $segments = collect();
        $templates = collect();
        $groups = collect();
        $leadCount = 0;
        $contactCount = 0;

        try { $segments = Segment::where('is_active', true)->get(); } catch (\Throwable $e) {}
        try { $groups = WhatsAppGroup::active()->get(); } catch (\Throwable $e) {}
        try { $leadCount = Lead::count(); } catch (\Throwable $e) {}
        try { $contactCount = WhatsAppContact::where('opted_in', true)->count(); } catch (\Throwable $e) {}
        try { $tc = 'App\Models\WhatsAppTemplate'; $templates = $tc::where('status', 'active')->get(); } catch (\Throwable $e) {}

        return view('admin.whatsapp.create', compact('segments', 'templates', 'groups', 'leadCount', 'contactCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'nullable|string',
            'message_type' => 'required|in:text,template',
            'template_id' => 'required_if:message_type,template|exists:whatsapp_templates,id',
            'schedule' => 'nullable|date',
            'scope' => 'required|in:all,segment,group',
            'segment_id' => 'required_if:scope,segment|exists:segments,id',
            'group_id' => 'required_if:scope,group|exists:whatsapp_groups,id',
        ]);

        try {
            $payload = null;
            $message = $request->message ?? '';
            $groupJid = null;

            if ($request->scope === 'group' && $request->group_id) {
                $group = WhatsAppGroup::findOrFail($request->group_id);
                $groupJid = $group->group_jid;
            }

            if ($request->message_type === 'template' && $request->template_id) {
                $tc = 'App\Models\WhatsAppTemplate';
                $template = $tc::findOrFail($request->template_id);
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
                'group_jid' => $groupJid,
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
        $segments = Segment::where('is_active', true)->get();
        $groups = WhatsAppGroup::active()->get();
        $apiConfigured = !empty(Setting::get('whatsapp_api_endpoint', ''));
        return view('admin.whatsapp.show', compact('broadcast', 'segments', 'groups', 'apiConfigured'));
    }

    public function send(Request $request, $id)
    {
        $broadcast = WhatsAppBroadcast::findOrFail($id);

        if ($broadcast->status === 'sent') {
            return back()->with('error', 'This broadcast has already been sent.');
        }

        try {
            $scope = $request->scope ?? 'all';

            if ($scope === 'group' && $request->group_id) {
                $group = WhatsAppGroup::findOrFail($request->group_id);
                $broadcast->update(['group_jid' => $group->group_jid]);
            }

            $result = match ($scope) {
                'segment' => $this->whatsapp->sendToSegment((int) $request->segment_id, $broadcast),
                'group' => $this->whatsapp->sendToGroup($broadcast),
                default => $this->whatsapp->sendToAllLeads($broadcast),
            };

            $msg = $result['sent'] > 0
                ? "Broadcast sent. Delivered: {$result['sent']}, Failed: {$result['failed']}"
                : "Broadcast completed. " . ($result['errors'][0] ?? 'No messages were sent.');

            $result['sent'] > 0
                ? Log::info("WhatsApp broadcast #{$broadcast->id} sent successfully: {$result['sent']} delivered, {$result['failed']} failed")
                : Log::warning("WhatsApp broadcast #{$broadcast->id} had issues: " . ($result['errors'][0] ?? 'unknown'));

            return redirect('/admin/whatsapp')->with(
                $result['sent'] > 0 ? 'success' : 'error',
                $msg
            );
        } catch (\Throwable $e) {
            Log::error("WhatsApp broadcast #{$broadcast->id} send exception: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
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

    public function testApi()
    {
        $result = $this->whatsapp->testApiConnection();
        return response()->json($result);
    }

    public function toggleOptIn($id)
    {
        $contact = WhatsAppContact::findOrFail($id);
        $contact->update(['opted_in' => !$contact->opted_in]);
        return back()->with('success', 'Contact opt-in status updated.');
    }
}
