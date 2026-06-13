@extends('layouts.admin')

@section('title', 'Embed Code - ' . $landingPage->title)

@section('content')
<div class="mb-6">
    <a href="/admin/marketing/landing-pages" class="text-blue-600 hover:text-blue-800">&larr; Back to Landing Pages</a>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <h2 class="text-xl font-bold text-slate-800 mb-4">Embed Code for: {{ $landingPage->title }}</h2>
    
    <p class="text-slate-600 mb-4">Copy this code and paste it into any HTML page or website builder:</p>
    
    <div class="bg-slate-800 text-slate-200 p-4 rounded-lg mb-4 overflow-x-auto">
        <pre class="text-sm font-mono whitespace-pre-wrap">&lt;iframe src="{{ url('/l/' . $landingPage->slug) }}" width="100%" height="600" frameborder="0"&gt;&lt;/iframe&gt;</pre>
    </div>
    
    <h3 class="font-bold text-slate-800 mb-2">Direct Link:</h3>
    <div class="bg-slate-100 p-3 rounded-lg mb-4">
        <code class="text-blue-600">{{ url('/l/' . $landingPage->slug) }}</code>
    </div>
    
    <h3 class="font-bold text-slate-800 mb-2">Lead Form Only (API):</h3>
    <p class="text-sm text-slate-500 mb-2">Use this for custom pages:</p>
    <div class="bg-slate-800 text-slate-200 p-4 rounded-lg overflow-x-auto">
        <pre class="text-sm font-mono whitespace-pre-wrap">&lt;form action="{{ url('/l/' . $landingPage->slug . '/submit') }}" method="POST"&gt;
  &lt;input type="hidden" name="_token" value="{{ csrf_token() }}"&gt;
  &lt;input type="email" name="email" placeholder="Your email" required&gt;
  &lt;input type="text" name="name" placeholder="Your name"&gt;
  &lt;button type="submit"&gt;Subscribe&lt;/button&gt;
&lt;/form&gt;</pre>
    </div>
    
    <div class="mt-6">
        <a href="/l/{{ $landingPage->slug }}" target="_blank" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-external-link-alt mr-2"></i>Preview Page
        </a>
    </div>
</div>
@endsection