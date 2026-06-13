@extends('layouts.admin')

@section('title', 'Edit Blog Post')

@section('content')
<form method="POST" action="/admin/marketing/blog/{{ $blog->id }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="mb-6">
        <a href="/admin/marketing/blog" class="text-blue-600 hover:text-blue-800">&larr; Back to Posts</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Content</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Title</label>
                    <input type="text" name="title" value="{{ $blog->title }}" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Slug</label>
                    <input type="text" name="slug" value="{{ $blog->slug }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Excerpt</label>
                    <textarea name="excerpt" rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ $blog->excerpt }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Content</label>
                    <textarea name="body" id="editor" rows="15" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ $blog->body }}</textarea>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Settings</h2>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_published" {{ $blog->is_published ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-slate-700">Published</span>
                    </label>
                    <input type="hidden" name="old_published" value="{{ $blog->is_published ? 1 : 0 }}">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Schedule for later</label>
                    <input type="datetime-local" name="published_at" value="{{ $blog->published_at ? $blog->published_at->format('Y-m-d\TH:i') : '' }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-slate-500 mt-1">Set a future date/time to schedule</p>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="post_to_twitter" {{ $blog->post_to_twitter ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-slate-700">Post to Twitter</span>
                    </label>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">SEO</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ $blog->meta_title }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Meta Description</label>
                    <textarea name="meta_description" rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ $blog->meta_description }}</textarea>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Featured Image</h2>
                
                @if($blog->featured_image)
                    <div class="mb-4">
                        <img src="{{ $blog->featured_image }}" alt="Current image" class="w-full h-48 object-cover rounded-lg mb-2">
                        <p class="text-sm text-slate-500">Current image</p>
                    </div>
                @endif
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Upload New Image</label>
                    <input type="file" name="featured_image_file" accept="image/*" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="previewImage(this)">
                    <p class="text-sm text-slate-500 mt-1">Or enter URL below</p>
                </div>
                
                <div id="imagePreview" class="mb-4 hidden">
                    <img src="" alt="Preview" class="w-full rounded-lg">
                </div>
                
                <input type="text" name="featured_image" id="featuredImageUrl" value="{{ $blog->featured_image }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Image URL (e.g., /uploads/blog/image.jpg)">
            </div>
            
            <script>
            function previewImage(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('imagePreview').querySelector('img').src = e.target.result;
                        document.getElementById('imagePreview').classList.remove('hidden');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }
            </script>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Popup Modal</h2>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="show_popup" {{ $blog->show_popup ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-slate-700">Enable popup</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Popup Delay (seconds)</label>
                    <input type="number" name="popup_delay" value="{{ $blog->popup_delay ?? 10 }}" min="0" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Popup Title</label>
                    <input type="text" name="popup_title" value="{{ $blog->popup_title }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Wait! Don't miss this...">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Popup HTML</label>
                    <textarea name="popup_html" rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ $blog->popup_html }}</textarea>
                </div>

                <hr class="my-4 border-slate-200">

                <h3 class="font-semibold text-slate-800 mb-3">Funnel Integration</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Connect to Funnel</label>
                    <select name="funnel_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">No funnel</option>
                        @if(isset($funnels))
                            @foreach($funnels as $funnel)
                                <option value="{{ $funnel->id }}" {{ $blog->funnel_id == $funnel->id ? 'selected' : '' }}>{{ $funnel->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <p class="text-sm text-slate-500 mt-1">When visitors subscribe via popup, they'll be added to this funnel</p>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-medium">
                Update Post
            </button>
        </div>
    </div>
</form>

@section('scripts')
<script src="https://cdn.tiny.cloud/1/009hb9xyz8apacuavuybybrvv9fb81n8ffydninn25nb5nsv/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#editor',
        height: 500,
        plugins: 'lists link image table hr anchor charmap codesample wordcount searchreplace visualblocks preview',
        toolbar: 'undo redo | formatselect | styleselect | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent | link image hr | charmap | codesample | searchreplace | visualblocks preview',
        style_formats: [
            {title: 'Heading 1', format: 'h1'},
            {title: 'Heading 2', format: 'h2'},
            {title: 'Heading 3', format: 'h3'},
            {title: 'Paragraph', format: 'p'},
            {title: 'Bold', format: 'bold'},
            {title: 'Italic', format: 'italic'},
            {title: 'Underline', format: 'underline'},
            {title: 'Strikethrough', format: 'strikethrough'},
            {title: 'Blockquote', format: 'blockquote'},
            {title: 'Code', format: 'code'}
        ],
        menubar: 'file edit view insert format tools',
        branding: false
    });
    
    document.querySelector('form').addEventListener('submit', function(e) {
        var editor = tinymce.get('editor');
        if (editor) {
            var content = editor.getContent();
            document.getElementById('editor').value = content;
        }
    });
</script>
@endsection
@endsection