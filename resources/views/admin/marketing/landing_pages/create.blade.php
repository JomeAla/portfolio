@extends('layouts.admin')

@section('title', 'New Landing Page')

@section('content')
<form method="POST" action="/admin/marketing/landing-pages">
    @csrf
    <div class="mb-6">
        <a href="/admin/marketing/landing-pages" class="text-blue-600 hover:text-blue-800">&larr; Back to Pages</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Page Content</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Title</label>
                    <input type="text" name="title" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Slug</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l-lg bg-slate-100 border border-r-0 border-slate-300 text-slate-500 text-sm">/l/</span>
                        <input type="text" name="slug" class="flex-1 border border-slate-300 rounded-r-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="my-page">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">HTML Content</label>
                    <textarea name="custom_html" rows="20" class="w-full border border-slate-300 rounded-lg px-4 py-2 font-mono text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="<div>Your landing page HTML...</div>"></textarea>
                    <p class="text-sm text-slate-500 mt-1">Use standard HTML or Tailwind CSS classes</p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Settings</h2>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" checked class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                        <span class="ml-2 text-sm text-slate-700">Active</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email Sequence</label>
                    <select name="sequence_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">No sequence</option>
                        @if(!empty($sequences))
                            @foreach($sequences as $seq)
                                <option value="{{ $seq->id }}">{{ $seq->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <p class="text-sm text-slate-500 mt-1">Leads will be enrolled in this sequence</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Funnel</label>
                    <select name="funnel_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">No funnel</option>
                        @if(!empty($funnels))
                            @foreach($funnels as $funnel)
                                <option value="{{ $funnel->id }}">{{ $funnel->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <p class="text-sm text-slate-500 mt-1">Connect this page to a sales funnel</p>
                </div>

                <hr class="my-4 border-slate-200">

                <h3 class="font-semibold text-slate-800 mb-3">Countdown Timer</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">End Date & Time</label>
                    <input type="datetime-local" name="countdown_end" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Message After Timer Ends</label>
                    <input type="text" name="countdown_message" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Offer has ended!">
                </div>

                <hr class="my-4 border-slate-200">

                <h3 class="font-semibold text-slate-800 mb-3">Popup Modal</h3>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="show_popup" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                        <span class="ml-2 text-sm text-slate-700">Enable popup</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Popup Delay (seconds)</label>
                    <input type="number" name="popup_delay" value="5" min="0" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Popup Title</label>
                    <input type="text" name="popup_title" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Wait! Don't leave yet...">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Popup HTML</label>
                    <textarea name="popup_html" rows="4" class="w-full border border-slate-300 rounded-lg px-4 py-2 font-mono text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="<p>Your popup content...</p>"></textarea>
                </div>
            </div>

            <button type="submit" class="w-full bg-orange-500 text-white px-4 py-3 rounded-lg hover:bg-orange-600 font-medium">
                Create Page
            </button>
        </div>
    </div>
</form>
@endsection