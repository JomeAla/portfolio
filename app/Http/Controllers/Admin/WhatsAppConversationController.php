<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppConversationLog;
use App\Models\WhatsAppContact;
use App\Models\WhatsAppTemplate;
use Illuminate\Http\Request;

class WhatsAppConversationController extends Controller
{
    public function index()
    {
        $conversations = WhatsAppConversation::latest()->get();
        $activeLogs = WhatsAppConversationLog::where('status', 'active')->count();
        return view('admin.whatsapp.conversations.index', compact('conversations', 'activeLogs'));
    }

    public function create()
    {
        $templates = WhatsAppTemplate::where('status', 'active')->get();
        return view('admin.whatsapp.conversations.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|in:lead_created,purchase_made,broadcast_reply,manual,schedule',
            'steps' => 'required|json',
        ]);

        try {
            WhatsAppConversation::create([
                'name' => $request->name,
                'description' => $request->description,
                'trigger_event' => $request->trigger_event,
                'steps' => json_decode($request->steps, true),
            ]);

            return redirect('/admin/whatsapp/conversations')->with('success', 'Conversation flow created.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $conv = WhatsAppConversation::findOrFail($id);
        $logs = WhatsAppConversationLog::where('conversation_id', $id)
            ->with('contact.lead')->latest()->limit(50)->get();
        return view('admin.whatsapp.conversations.show', compact('conv', 'logs'));
    }

    public function edit($id)
    {
        $conv = WhatsAppConversation::findOrFail($id);
        $templates = WhatsAppTemplate::where('status', 'active')->get();
        return view('admin.whatsapp.conversations.edit', compact('conv', 'templates'));
    }

    public function update(Request $request, $id)
    {
        $conv = WhatsAppConversation::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'steps' => 'required|json',
        ]);

        try {
            $conv->update([
                'name' => $request->name,
                'description' => $request->description,
                'trigger_event' => $request->trigger_event ?? $conv->trigger_event,
                'steps' => json_decode($request->steps, true),
            ]);

            return redirect('/admin/whatsapp/conversations')->with('success', 'Conversation updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function toggleActive($id)
    {
        $conv = WhatsAppConversation::findOrFail($id);
        $conv->update(['is_active' => !$conv->is_active]);
        return back()->with('success', 'Conversation ' . ($conv->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function destroy($id)
    {
        WhatsAppConversation::findOrFail($id)->delete();
        return redirect('/admin/whatsapp/conversations')->with('success', 'Conversation deleted.');
    }

    public function logs()
    {
        $logs = WhatsAppConversationLog::with(['conversation', 'contact.lead'])
            ->latest()->paginate(30);
        return view('admin.whatsapp.conversations.logs', compact('logs'));
    }
}
