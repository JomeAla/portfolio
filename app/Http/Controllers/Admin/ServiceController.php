<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('id')->paginate(10);
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title);
        $data['features'] = $request->features ? array_map('trim', explode(',', $request->features)) : [];
        $data['order'] = $request->order ?? 0;
        $data['is_active'] = $request->has('is_active');

        Service::create($data);

        return redirect('/admin/services')->with('success', 'Service created.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title);
        $data['features'] = $request->features ? array_map('trim', explode(',', $request->features)) : [];
        $data['order'] = $request->order ?? 0;
        $data['is_active'] = $request->has('is_active');

        $service->update($data);

        return redirect('/admin/services')->with('success', 'Service updated.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect('/admin/services')->with('success', 'Service deleted.');
    }
}
