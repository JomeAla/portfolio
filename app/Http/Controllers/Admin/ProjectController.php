<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
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
        $data['slug'] = Str::slug($request->title);

        if ($request->hasFile('thumbnail')) {
            $filename = time() . '_' . Str::random(10) . '.' . $request->file('thumbnail')->getClientOriginalExtension();
            $request->file('thumbnail')->move(public_path('uploads/projects'), $filename);
            $data['thumbnail'] = 'uploads/projects/' . $filename;
        }

        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/projects/gallery'), $filename);
                $images[] = 'uploads/projects/gallery/' . $filename;
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
        $data['slug'] = Str::slug($request->title);

        if ($request->hasFile('thumbnail')) {
            if ($project->thumbnail && file_exists(public_path($project->thumbnail))) {
                unlink(public_path($project->thumbnail));
            }
            $filename = time() . '_' . Str::random(10) . '.' . $request->file('thumbnail')->getClientOriginalExtension();
            $request->file('thumbnail')->move(public_path('uploads/projects'), $filename);
            $data['thumbnail'] = 'uploads/projects/' . $filename;
        }

        if ($request->hasFile('images')) {
            if ($project->images) {
                foreach (json_decode($project->images) as $img) {
                    if (file_exists(public_path($img))) {
                        unlink(public_path($img));
                    }
                }
            }
            $images = [];
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/projects/gallery'), $filename);
                $images[] = 'uploads/projects/gallery/' . $filename;
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

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        
        if ($project->thumbnail && file_exists(public_path($project->thumbnail))) {
            unlink(public_path($project->thumbnail));
        }
        if ($project->images) {
            foreach (json_decode($project->images) as $img) {
                if (file_exists(public_path($img))) {
                    unlink(public_path($img));
                }
            }
        }
        $project->delete();
        return redirect('/admin/projects')->with('success', 'Project deleted.');
    }
}
