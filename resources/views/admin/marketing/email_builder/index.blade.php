@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.marketing.email-builder') }}" class="text-slate-600 hover:text-slate-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Start New Email</h1>
    </div>

    <form method="POST" action="{{ route('admin.marketing.email-builder.store') }}" class="space-y-6">
        @csrf
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Template Name</label>
                    <input type="text" name="name" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Subject Line</label>
                    <input type="text" name="subject" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
                <input type="text" name="description" class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>

            <input type="hidden" name="template_data" value='{"blocks":[{"type":"header","content":"Welcome!"},{"type":"text","content":"This is your email content."}]}'>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-700">
                <i class="fas fa-info-circle mr-2"></i>
                This will create a basic template. Use the visual builder to customize blocks.
            </p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                Create Basic Template
            </button>
            <a href="{{ route('admin.marketing.email-builder.create') }}" class="px-6 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                Open Full Builder
            </a>
        </div>
    </form>
</div>
@endsection