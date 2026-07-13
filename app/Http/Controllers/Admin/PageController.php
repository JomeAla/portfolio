<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::all();
        return view('admin.pages.index', compact('pages'));
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
        ]);

        $data = $request->only(['title', 'meta_title', 'meta_description']);

        if ($page->slug === 'home') {
            $content = $page->content ?? [];
            $content['hero']['title'] = $request->input('hero_title', $content['hero']['title'] ?? '');
            $content['hero']['subtitle'] = $request->input('hero_subtitle', $content['hero']['subtitle'] ?? '');
            $content['hero']['badge'] = $request->input('hero_badge', $content['hero']['badge'] ?? 'Available for projects');
            $content['cta']['text'] = $request->input('cta_text', $content['cta']['text'] ?? '');
            $content['cta']['link'] = $request->input('cta_link', $content['cta']['link'] ?? '');
            $content['stats']['projects'] = $request->input('stat_projects', $content['stats']['projects'] ?? '50+');
            $content['stats']['projects_label'] = $request->input('stat_projects_label', $content['stats']['projects_label'] ?? 'Projects Completed');
            $content['stats']['experience'] = $request->input('stat_experience', $content['stats']['experience'] ?? '5+');
            $content['stats']['experience_label'] = $request->input('stat_experience_label', $content['stats']['experience_label'] ?? 'Years Experience');
            $content['stats']['satisfaction'] = $request->input('stat_satisfaction', $content['stats']['satisfaction'] ?? '100%');
            $content['stats']['satisfaction_label'] = $request->input('stat_satisfaction_label', $content['stats']['satisfaction_label'] ?? 'Client Satisfaction');
            $data['content'] = $content;
        } else {
            $data['content'] = $request->input('content');
        }

        $page->update($data);

        return back()->with('success', 'Page updated.');
    }
}