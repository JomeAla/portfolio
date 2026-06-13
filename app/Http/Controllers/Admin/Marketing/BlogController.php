<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Marketing\BlogPost;
use App\Services\Marketing\MarketingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.marketing.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.marketing.blog.create');
    }

    public function store(Request $request, MarketingService $marketing)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
        ]);

        $slug = BlogPost::generateSlug($request->title);

        $featuredImage = $request->featured_image;
        
        if ($request->hasFile('featured_image_file')) {
            try {
                $file = $request->file('featured_image_file');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $uploadPath = '/home/joalacom/public_html/public/uploads/blog';
                
                // Ensure directory exists
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Check if directory is writable
                if (!is_writable($uploadPath)) {
                    Log::error('Upload directory not writable: ' . $uploadPath);
                    // Try to fix permissions
                    chmod($uploadPath, 0755);
                }
                
                // Get temp file path
                $tempPath = $file->getRealPath();
                
                Log::info('Attempting upload - temp: ' . $tempPath . ', dest: ' . $uploadPath . '/' . $filename);
                
                // Try move first (most reliable)
                $moved = $file->move($uploadPath, $filename);
                
                if ($moved) {
                    $fullPath = $uploadPath . '/' . $filename;
                    chmod($fullPath, 0644);
                    $featuredImage = '/uploads/blog/' . $filename;
                    Log::info('Upload successful: ' . $featuredImage);
                } else {
                    Log::error('File move failed');
                }
                
            } catch (\Exception $e) {
                Log::error('Upload error: ' . $e->getMessage());
            }
        }

        $post = BlogPost::create([
            'title' => $request->title,
            'slug' => $slug,
            'body' => $request->body,
            'excerpt' => $request->excerpt,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'featured_image' => $featuredImage,
            'is_published' => $request->has('is_published'),
            'post_to_twitter' => $request->has('post_to_twitter'),
            'published_at' => $request->has('is_published') ? now() : null,
        ]);

        if ($request->has('is_published') && $request->has('post_to_twitter')) {
            $marketing->queueTweetForBlogPost($post);
        }

        return redirect()->route('admin.marketing.blog.index')->with('success', 'Blog post created successfully.');
    }

    public function edit(BlogPost $post)
    {
        return view('admin.marketing.blog.edit', compact('post'));
    }

    public function update(Request $request, BlogPost $post, MarketingService $marketing)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
        ]);

        $wasPublished = $post->is_published;
        $wasTwitterEnabled = $post->post_to_twitter;

        $featuredImage = $request->featured_image;
        
        if ($request->hasFile('featured_image_file')) {
            try {
                $file = $request->file('featured_image_file');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $uploadPath = '/home/joalacom/public_html/public/uploads/blog';
                
                // Ensure directory exists
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Check if directory is writable
                if (!is_writable($uploadPath)) {
                    Log::error('Upload directory not writable: ' . $uploadPath);
                    chmod($uploadPath, 0755);
                }
                
                $tempPath = $file->getRealPath();
                
                Log::info('Attempting upload - temp: ' . $tempPath . ', dest: ' . $uploadPath . '/' . $filename);
                
                // Try move
                $moved = $file->move($uploadPath, $filename);
                
                if ($moved) {
                    $fullPath = $uploadPath . '/' . $filename;
                    chmod($fullPath, 0644);
                    $featuredImage = '/uploads/blog/' . $filename;
                    Log::info('Upload successful: ' . $featuredImage);
                } else {
                    Log::error('File move failed');
                }
                
            } catch (\Exception $e) {
                Log::error('Upload error: ' . $e->getMessage());
            }
        }

        $post->update([
            'title' => $request->title,
            'body' => $request->body,
            'excerpt' => $request->excerpt,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'featured_image' => $featuredImage,
            'is_published' => $request->has('is_published'),
            'post_to_twitter' => $request->has('post_to_twitter'),
            'published_at' => $request->has('is_published') ? ($wasPublished ? $post->published_at : now()) : null,
        ]);

        if ($request->has('is_published') && $request->has('post_to_twitter') && !$wasPublished) {
            $marketing->queueTweetForBlogPost($post);
        }

        return redirect()->route('admin.marketing.blog.index')->with('success', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();
        return redirect()->route('admin.marketing.blog.index')->with('success', 'Blog post deleted.');
    }
}