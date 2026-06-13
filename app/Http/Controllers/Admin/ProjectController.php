<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    protected string $defaultSubfolder = 'projects';
    
    public function index()
    {
        $projects = Project::orderBy('order')->paginate(10);
        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
        ]);

        $data = $request->all();
        
        $slug = \Illuminate\Support\Str::slug($request->title);
        $baseSlug = $slug;
        $counter = 1;
        while (\App\Models\Project::where('slug', $slug)->where('id', '!=', null)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        $data['slug'] = $slug;

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $uploadPath = '/home/joalacom/public_html/public/uploads/projects';
            if (!is_dir($uploadPath)) { mkdir($uploadPath, 0755, true); }
            $file->move($uploadPath, $filename);
            $data['thumbnail'] = '/uploads/projects/' . $filename;
        }

        if ($request->hasFile('images')) {
            $images = [];
            $files = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];
            foreach ($files as $idx => $file) {
                $filename = time() . '_' . uniqid() . '_' . $idx . '.' . $file->getClientOriginalExtension();
                $uploadPath = '/home/joalacom/public_html/public/uploads/projects/gallery';
                if (!is_dir($uploadPath)) { mkdir($uploadPath, 0755, true); }
                $file->move($uploadPath, $filename);
                $images[] = '/uploads/projects/gallery/' . $filename;
            }
            $data['images'] = json_encode($images);
        }

        if ($request->technologies) {
            $data['technologies'] = json_encode(array_map('trim', explode(',', $request->technologies)));
        }

        Project::create($data);
        return redirect('/admin/projects')->with('success', 'Project created.');
    }

    public function edit(Project $project)
    {
        return view('admin.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
        ]);

        $data = $request->all();
        if ($request->title !== $project->title) {
            $slug = \Illuminate\Support\Str::slug($request->title);
            $baseSlug = $slug;
            $counter = 1;
            while (\App\Models\Project::where('slug', $slug)->where('id', '!=', $project->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('thumbnail')) {
            if ($project->thumbnail && file_exists(base_path($project->thumbnail))) {
                unlink(base_path($project->thumbnail));
            }
            $file = $request->file('thumbnail');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $uploadPath = '/home/joalacom/public_html/public/uploads/projects';
            if (!is_dir($uploadPath)) { mkdir($uploadPath, 0755, true); }
            $file->move($uploadPath, $filename);
            $data['thumbnail'] = '/uploads/projects/' . $filename;
        }

        if ($request->hasFile('images')) {
            if ($project->images) {
                foreach ((array)json_decode($project->images) as $img) {
                    if (file_exists(base_path($img))) { unlink(base_path($img)); }
                }
            }
            $images = [];
            $files = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];
            foreach ($files as $idx => $file) {
                $filename = time() . '_' . uniqid() . '_' . $idx . '.' . $file->getClientOriginalExtension();
                $uploadPath = '/home/joalacom/public_html/public/uploads/projects/gallery';
                if (!is_dir($uploadPath)) { mkdir($uploadPath, 0755, true); }
                $file->move($uploadPath, $filename);
                $images[] = '/uploads/projects/gallery/' . $filename;
            }
            $data['images'] = json_encode($images);
        }

        if ($request->technologies) {
            $data['technologies'] = json_encode(array_map('trim', explode(',', $request->technologies)));
        } else {
            $data['technologies'] = null;
        }

        $project->update($data);
        return redirect('/admin/projects')->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        if ($project->thumbnail && file_exists(base_path($project->thumbnail))) {
            unlink(base_path($project->thumbnail));
        }
        if ($project->images) {
            foreach (json_decode($project->images) as $img) {
                if (file_exists(base_path($img))) { unlink(base_path($img)); }
            }
        }
        $project->delete();
        return redirect('/admin/projects')->with('success', 'Project deleted.');
    }
}