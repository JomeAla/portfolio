<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected string $defaultSubfolder = 'products';
    
    public function index()
    {
        $products = Product::orderBy('order')->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|string',
        ]);

        $data = $request->all();
        
        $slug = Str::slug($request->title);
        $baseSlug = $slug;
        $counter = 1;
        while (\App\Models\Product::where('slug', $slug)->where('id', '!=', null)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        $data['slug'] = $slug;

        $uploadBasePath = '/home/joalacom/public_html/public/uploads';
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $uploadPath = $uploadBasePath . '/products';
            if (!is_dir($uploadPath)) { mkdir($uploadPath, 0755, true); }
            $file->move($uploadPath, $filename);
            $data['image'] = '/uploads/products/' . $filename;
        }

        if ($request->hasFile('file')) {
            $filename = time() . '_' . Str::random(10) . '.' . $request->file('file')->getClientOriginalExtension();
            $uploadDir = storage_path('app/public/uploads/products/files');
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
            $request->file('file')->move($uploadDir, $filename);
            $data['file_path'] = 'uploads/products/files/' . $filename;
        }

        if ($request->hasFile('images')) {
            $images = [];
            $files = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];
            $uploadPath = $uploadBasePath . '/products/gallery';
            if (!is_dir($uploadPath)) { mkdir($uploadPath, 0755, true); }
            foreach ($files as $idx => $file) {
                $filename = time() . '_' . uniqid() . '_' . $idx . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                $images[] = '/uploads/products/gallery/' . $filename;
            }
            $data['images'] = json_encode($images);
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');

        Product::create($data);
        return redirect('/admin/products')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|string',
        ]);

        $data = $request->all();
        if ($request->title !== $product->title) {
            $slug = Str::slug($request->title);
            $baseSlug = $slug;
            $counter = 1;
            while (\App\Models\Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        $uploadBasePath = '/home/joalacom/public_html/public/uploads';
        
        if ($request->hasFile('image')) {
            if ($product->image && file_exists(base_path($product->image))) {
                unlink(base_path($product->image));
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $uploadPath = $uploadBasePath . '/products';
            if (!is_dir($uploadPath)) { mkdir($uploadPath, 0755, true); }
            $file->move($uploadPath, $filename);
            $data['image'] = '/uploads/products/' . $filename;
        }

        if ($request->hasFile('file')) {
            $oldPath = storage_path('app/public/' . $product->file_path);
            if ($product->file_path && file_exists($oldPath)) {
                unlink($oldPath);
            }
            $filename = time() . '_' . Str::random(10) . '.' . $request->file('file')->getClientOriginalExtension();
            $uploadDir = storage_path('app/public/uploads/products/files');
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
            $request->file('file')->move($uploadDir, $filename);
            $data['file_path'] = 'uploads/products/files/' . $filename;
        }

        if ($request->hasFile('images')) {
            if ($product->images) {
                foreach ((array)json_decode($product->images) as $img) {
                    if (file_exists(base_path($img))) { unlink(base_path($img)); }
                }
            }
            $images = [];
            $files = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];
            $uploadPath = $uploadBasePath . '/products/gallery';
            if (!is_dir($uploadPath)) { mkdir($uploadPath, 0755, true); }
            foreach ($files as $idx => $file) {
                $filename = time() . '_' . uniqid() . '_' . $idx . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                $images[] = '/uploads/products/gallery/' . $filename;
            }
            $data['images'] = json_encode($images);
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');

        $product->update($data);
        return redirect('/admin/products')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        if ($product->image && file_exists(base_path($product->image))) {
            unlink(base_path($product->image));
        }
        $oldFilePath = storage_path('app/public/' . $product->file_path);
        if ($product->file_path && file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }
        if ($product->images) {
            foreach (json_decode($product->images) as $img) {
                if (file_exists(base_path($img))) {
                    unlink(base_path($img));
                }
            }
        }
        $product->delete();
        return redirect('/admin/products')->with('success', 'Product deleted.');
    }
}