<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppTemplate;
use Illuminate\Http\Request;

class WhatsAppTemplateController extends Controller
{
    public function index()
    {
        $templates = WhatsAppTemplate::latest()->get();
        return view('admin.whatsapp.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.whatsapp.templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:marketing,utility,authentication',
            'message_type' => 'required|in:text,interactive,media,template,flow',
            'body' => 'required|string',
            'footer' => 'nullable|string|max:60',
            'header_type' => 'nullable|string',
            'header_value' => 'nullable|string|max:60',
            'buttons' => 'nullable|json',
            'sections' => 'nullable|json',
            'media_url' => 'nullable|string',
        ]);

        try {
            WhatsAppTemplate::create([
                'name' => $request->name,
                'category' => $request->category,
                'message_type' => $request->message_type,
                'body' => $request->body,
                'footer' => $request->footer,
                'header_type' => $request->header_type,
                'header_value' => $request->header_value,
                'buttons' => $request->buttons ? json_decode($request->buttons, true) : null,
                'sections' => $request->sections ? json_decode($request->sections, true) : null,
                'media_url' => $request->media_url,
                'status' => 'draft',
            ]);

            return redirect('/admin/whatsapp/templates')->with('success', 'Template created.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        return view('admin.whatsapp.templates.show', compact('template'));
    }

    public function edit($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        return view('admin.whatsapp.templates.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $template = WhatsAppTemplate::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        try {
            $template->update([
                'name' => $request->name,
                'category' => $request->category ?? $template->category,
                'message_type' => $request->message_type ?? $template->message_type,
                'body' => $request->body,
                'footer' => $request->footer,
                'header_type' => $request->header_type,
                'header_value' => $request->header_value,
                'buttons' => $request->buttons ? json_decode($request->buttons, true) : $template->buttons,
                'sections' => $request->sections ? json_decode($request->sections, true) : $template->sections,
                'media_url' => $request->media_url,
            ]);

            return redirect('/admin/whatsapp/templates')->with('success', 'Template updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function toggleStatus($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        $template->update(['status' => $template->status === 'active' ? 'draft' : 'active']);
        return back()->with('success', 'Template status updated.');
    }

    public function destroy($id)
    {
        WhatsAppTemplate::findOrFail($id)->delete();
        return redirect('/admin/whatsapp/templates')->with('success', 'Template deleted.');
    }

    public function preview($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        $payload = app(\App\Services\WhatsAppBroadcastService::class)->buildTemplatePayload($template);
        return response()->json(['template' => $template, 'payload' => $payload]);
    }

    public function sendTest(Request $request, $id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        $phone = $request->phone;

        if (!$phone) return back()->with('error', 'Test phone number required.');

        try {
            $service = app(\App\Services\WhatsAppBroadcastService::class);
            $service->sendTemplate($phone, $template);
            return back()->with('success', "Test template sent to {$phone}");
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
