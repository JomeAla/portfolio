<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index()
    {
        $banners = PromoBanner::orderBy('order')->paginate(15);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $filename = time() . '_' . Str::random(10) . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/banners'), $filename);
            $data['image'] = 'uploads/banners/' . $filename;
        }

        $data['is_active'] = $request->has('is_active');

        PromoBanner::create($data);

        return redirect('/admin/banners')->with('success', 'Banner created.');
    }

    public function edit(PromoBanner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, PromoBanner $banner)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($banner->image && file_exists(public_path($banner->image))) {
                unlink(public_path($banner->image));
            }
            $filename = time() . '_' . Str::random(10) . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/banners'), $filename);
            $data['image'] = 'uploads/banners/' . $filename;
        }

        $data['is_active'] = $request->has('is_active');

        $banner->update($data);

        return redirect('/admin/banners')->with('success', 'Banner updated.');
    }

    public function destroy(PromoBanner $banner)
    {
        if ($banner->image && file_exists(public_path($banner->image))) {
            unlink(public_path($banner->image));
        }
        $banner->delete();
        return redirect('/admin/banners')->with('success', 'Banner deleted.');
    }
}
