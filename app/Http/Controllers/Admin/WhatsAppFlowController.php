<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppFlow;
use Illuminate\Http\Request;

class WhatsAppFlowController extends Controller
{
    public function index()
    {
        $flows = WhatsAppFlow::latest()->get();
        return view('admin.whatsapp.flows.index', compact('flows'));
    }

    public function create()
    {
        return view('admin.whatsapp.flows.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'flow_json' => 'required|json',
            'flow_data' => 'nullable|json',
        ]);

        try {
            WhatsAppFlow::create([
                'name' => $request->name,
                'description' => $request->description,
                'flow_json' => json_decode($request->flow_json, true),
                'flow_data' => $request->flow_data ? json_decode($request->flow_data, true) : null,
                'status' => 'draft',
            ]);

            return redirect('/admin/whatsapp/flows')->with('success', 'Flow created.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $flow = WhatsAppFlow::findOrFail($id);
        return view('admin.whatsapp.flows.show', compact('flow'));
    }

    public function edit($id)
    {
        $flow = WhatsAppFlow::findOrFail($id);
        return view('admin.whatsapp.flows.edit', compact('flow'));
    }

    public function update(Request $request, $id)
    {
        $flow = WhatsAppFlow::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'flow_json' => 'required|json',
        ]);

        try {
            $flow->update([
                'name' => $request->name,
                'description' => $request->description,
                'flow_json' => json_decode($request->flow_json, true),
                'flow_data' => $request->flow_data ? json_decode($request->flow_data, true) : $flow->flow_data,
            ]);

            return redirect('/admin/whatsapp/flows')->with('success', 'Flow updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function deploy($id)
    {
        $flow = WhatsAppFlow::findOrFail($id);
        $flow->update(['status' => 'deployed']);
        return back()->with('success', 'Flow deployed.');
    }

    public function destroy($id)
    {
        WhatsAppFlow::findOrFail($id)->delete();
        return redirect('/admin/whatsapp/flows')->with('success', 'Flow deleted.');
    }
}
