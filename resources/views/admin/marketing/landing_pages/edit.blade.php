@extends('layouts.admin')

@section('title', 'Edit Landing Page')

@section('content')
<form method="POST" action="/admin/marketing/landing-pages/{{ $landingPage->id }}">
    @csrf
    @method('PUT')
    <div class="mb-6">
        <a href="/admin/marketing/landing-pages" class="text-blue-600 hover:text-blue-800">&larr; Back to Pages</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Page Content</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Title</label>
                    <input type="text" name="title" value="{{ $landingPage->title }}" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Slug</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l-lg bg-slate-100 border border-r-0 border-slate-300 text-slate-500 text-sm">/l/</span>
                        <input type="text" name="slug" value="{{ $landingPage->slug }}" class="flex-1 border border-slate-300 rounded-r-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">HTML Content</label>
                    <textarea name="custom_html" rows="20" class="w-full border border-slate-300 rounded-lg px-4 py-2 font-mono text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">{{ $landingPage->custom_html }}</textarea>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Settings</h2>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" {{ $landingPage->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                        <span class="ml-2 text-sm text-slate-700">Active</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email Sequence</label>
                    <select name="sequence_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">No sequence</option>
                        @foreach($sequences as $seq)
                            <option value="{{ $seq->id }}" {{ $landingPage->sequence_id == $seq->id ? 'selected' : '' }}>{{ $seq->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Funnel</label>
                    <select name="funnel_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">No funnel</option>
                        @if(!empty($funnels))
                            @foreach($funnels as $funnel)
                                <option value="{{ $funnel->id }}" {{ $landingPage->funnel_id == $funnel->id ? 'selected' : '' }}>{{ $funnel->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <p class="text-sm text-slate-500 mt-1">Connect this page to a sales funnel</p>
                </div>

                <hr class="my-4 border-slate-200">

                <h3 class="font-semibold text-slate-800 mb-3">Countdown Timer</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">End Date & Time</label>
                    <input type="datetime-local" name="countdown_end" value="{{ $landingPage->countdown_end ? $landingPage->countdown_end->format('Y-m-d\TH:i') : '' }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Message After Timer Ends</label>
                    <input type="text" name="countdown_message" value="{{ $landingPage->countdown_message }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Offer has ended!">
                </div>

                <hr class="my-4 border-slate-200">

                <h3 class="font-semibold text-slate-800 mb-3">Popup Modal</h3>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="show_popup" @checked($landingPage->show_popup) class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                        <span class="ml-2 text-sm text-slate-700">Enable popup</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Popup Delay (seconds)</label>
                    <input type="number" name="popup_delay" value="{{ $landingPage->popup_delay ?? 5 }}" min="0" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Popup Title</label>
                    <input type="text" name="popup_title" value="{{ $landingPage->popup_title }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Wait! Don't leave yet...">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Popup HTML</label>
                    <textarea name="popup_html" rows="4" class="w-full border border-slate-300 rounded-lg px-4 py-2 font-mono text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">{!! $landingPage->popup_html !!}</textarea>
                </div>
            </div>

            <button type="submit" class="w-full bg-orange-500 text-white px-4 py-3 rounded-lg hover:bg-orange-600 font-medium">
                Update Page
            </button>
        </div>
    </div>
</form>
@endsection