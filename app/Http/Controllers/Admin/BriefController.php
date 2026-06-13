<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectBrief;
use Illuminate\Http\Request;

class BriefController extends Controller
{
    public function index()
    {
        $briefs = ProjectBrief::latest()->paginate(15);
        return view('admin.briefs.index', compact('briefs'));
    }

    public function show(ProjectBrief $brief)
    {
        if (!$brief->is_read) {
            $brief->update(['is_read' => true]);
        }

        return view('admin.briefs.show', compact('brief'));
    }

    public function update(Request $request, ProjectBrief $brief)
    {
        $request->validate([
            'status' => 'required|string|in:new,contacted,in_progress,completed',
            'notes' => 'nullable|string',
        ]);

        $brief->update($request->all());

        return back()->with('success', 'Brief updated.');
    }

    public function destroy(ProjectBrief $brief)
    {
        $brief->delete();
        return redirect()->route('admin.briefs')->with('success', 'Brief deleted.');
    }

    public function unreadCountJson()
    {
        $count = self::getUnreadCount();
        return response()->json(['count' => $count]);
    }

    public static function getUnreadCount(): int
    {
        try {
            return ProjectBrief::where('is_read', false)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}