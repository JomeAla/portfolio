<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\MarketingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required',
            'meta_title' => 'nullable|string|max:160',
            'meta_description' => 'nullable|string|max:300',
        ]);

        $slug = MarketingService::generateSlug($request->title);

        BlogPost::create([
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt,
            'body' => $request->body,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'featured_image' => $request->featured_image,
            'is_published' => $request->has('is_published'),
            'post_to_twitter' => $request->has('post_to_twitter'),
            'published_at' => $request->has('is_published') ? now() : null,
        ]);

        return redirect('/admin/marketing/blog')->with('success', 'Blog post created.');
    }

    public function edit(BlogPost $blog)
    {
        return view('admin.marketing.blog.edit', compact('blog'));
    }

    public function update(Request $request, BlogPost $blog)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required',
            'meta_title' => 'nullable|string|max:160',
            'meta_description' => 'nullable|string|max:300',
        ]);

        $wasPublished = $blog->is_published;
        $isNowPublished = $request->has('is_published');

        $blog->update([
            'title' => $request->title,
            'excerpt' => $request->excerpt,
            'body' => $request->body,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'featured_image' => $request->featured_image,
            'is_published' => $isNowPublished,
            'post_to_twitter' => $request->has('post_to_twitter'),
            'published_at' => ($isNowPublished && !$wasPublished) ? now() : $blog->published_at,
        ]);

        if ($isNowPublished && $blog->post_to_twitter) {
            $marketingService = new MarketingService();
            $marketingService->createTweetFromBlogPost($blog);
        }

        return redirect('/admin/marketing/blog')->with('success', 'Blog post updated.');
    }

    public function destroy(BlogPost $blog)
    {
        $blog->delete();
        return redirect('/admin/marketing/blog')->with('success', 'Blog post deleted.');
    }
}