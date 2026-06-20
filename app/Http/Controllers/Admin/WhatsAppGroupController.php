<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppGroup;
use Illuminate\Http\Request;

class WhatsAppGroupController extends Controller
{
    public function index()
    {
        $groups = WhatsAppGroup::latest()->get();
        return view('admin.whatsapp.groups.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.whatsapp.groups.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'group_jid' => 'required|string|max:255|unique:whatsapp_groups,group_jid',
            'description' => 'nullable|string|max:500',
            'member_count' => 'nullable|integer|min:0',
        ]);

        WhatsAppGroup::create($request->all());

        return redirect('/admin/whatsapp/groups')->with('success', 'WhatsApp group added.');
    }

    public function edit(WhatsAppGroup $group)
    {
        return view('admin.whatsapp.groups.edit', compact('group'));
    }

    public function update(Request $request, WhatsAppGroup $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'group_jid' => 'required|string|max:255|unique:whatsapp_groups,group_jid,' . $group->id,
            'description' => 'nullable|string|max:500',
            'member_count' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $group->update($request->all());

        return redirect('/admin/whatsapp/groups')->with('success', 'Group updated.');
    }

    public function destroy(WhatsAppGroup $group)
    {
        $group->delete();
        return redirect('/admin/whatsapp/groups')->with('success', 'Group removed.');
    }

    public function toggleActive(WhatsAppGroup $group)
    {
        $group->update(['is_active' => !$group->is_active]);
        return back()->with('success', 'Group status updated.');
    }
}
