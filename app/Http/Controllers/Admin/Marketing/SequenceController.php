<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Marketing\Sequence;
use App\Models\Marketing\SequenceStep;
use App\Services\Marketing\MarketingService;
use Illuminate\Http\Request;

class SequenceController extends Controller
{
    public function index()
    {
        $sequences = Sequence::with('steps')->get();
        return view('admin.marketing.sequences.index', compact('sequences'));
    }

    public function create()
    {
        return view('admin.marketing.sequences.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $sequence = Sequence::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.marketing.sequences.edit', $sequence)->with('success', 'Sequence created. Add email steps below.');
    }

    public function edit(Sequence $sequence)
    {
        $sequence->load('steps');
        return view('admin.marketing.sequences.edit', compact('sequence'));
    }

    public function update(Request $request, Sequence $sequence)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $sequence->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.marketing.sequences.index')->with('success', 'Sequence updated.');
    }

    public function destroy(Sequence $sequence)
    {
        $sequence->delete();
        return redirect()->route('admin.marketing.sequences.index')->with('success', 'Sequence deleted.');
    }

    public function addStep(Request $request, Sequence $sequence)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'delay_days' => 'required|integer|min:0',
        ]);

        $maxOrder = $sequence->steps()->max('step_order') ?? 0;

        SequenceStep::create([
            'sequence_id' => $sequence->id,
            'subject' => $request->subject,
            'body' => $request->body,
            'delay_days' => $request->delay_days,
            'step_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Step added.');
    }

    public function editStep(Sequence $sequence, SequenceStep $step)
    {
        return view('admin.marketing.sequences.edit-step', compact('sequence', 'step'));
    }

    public function updateStep(Request $request, Sequence $sequence, SequenceStep $step)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'delay_days' => 'required|integer|min:0',
        ]);

        $step->update([
            'subject' => $request->subject,
            'body' => $request->body,
            'delay_days' => $request->delay_days,
        ]);

        return redirect()->route('admin.marketing.sequences.edit', $sequence)->with('success', 'Step updated.');
    }

    public function destroyStep(Sequence $sequence, SequenceStep $step)
    {
        $step->delete();
        return back()->with('success', 'Step deleted.');
    }

    public function reorderSteps(Request $request, Sequence $sequence)
    {
        foreach ($request->order as $index => $stepId) {
            SequenceStep::where('id', $stepId)->update(['step_order' => $index + 1]);
        }
        return back()->with('success', 'Steps reordered.');
    }
}