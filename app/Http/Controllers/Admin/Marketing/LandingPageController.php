<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Marketing\LandingPage;
use App\Models\Marketing\Sequence;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        $pages = LandingPage::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.marketing.landing-pages.index', compact('pages'));
    }

    public function create()
    {
        $sequences = Sequence::where('is_active', true)->get();
        return view('admin.marketing.landing-pages.create', compact('sequences'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'custom_html' => 'required|string',
        ]);

        $slug = LandingPage::generateSlug($request->title);

        LandingPage::create([
            'title' => $request->title,
            'slug' => $slug,
            'custom_html' => $request->custom_html,
            'sequence_id' => $request->sequence_id,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.marketing.landing-pages.index')->with('success', 'Landing page created.');
    }

    public function edit(LandingPage $page)
    {
        $sequences = Sequence::where('is_active', true)->get();
        return view('admin.marketing.landing-pages.edit', compact('page', 'sequences'));
    }

    public function update(Request $request, LandingPage $page)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'custom_html' => 'required|string',
        ]);

        $page->update([
            'title' => $request->title,
            'custom_html' => $request->custom_html,
            'sequence_id' => $request->sequence_id,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.marketing.landing-pages.index')->with('success', 'Landing page updated.');
    }

    public function destroy(LandingPage $page)
    {
        $page->delete();
        return redirect()->route('admin.marketing.landing-pages.index')->with('success', 'Landing page deleted.');
    }

    public function preview(LandingPage $page)
    {
        return view('admin.marketing.landing-pages.preview', compact('page'));
    }
}