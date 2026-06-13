@extends('layouts.admin')

@section('title', 'Twitter Settings')

@section('content')
<form method="POST" action="/admin/marketing/settings">
    @csrf
    <div class="mb-6">
        <a href="/admin/marketing" class="text-blue-600 hover:text-blue-800">&larr; Back to Marketing</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Twitter (X) API Settings</h2>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Client ID</label>
            <input type="text" name="client_id" value="{{ $settings->client_id }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-transparent">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Client Secret</label>
            <input type="password" name="client_secret" value="{{ $settings->client_secret }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-transparent">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">OAuth Token</label>
            <input type="text" name="oauth_token" value="{{ $settings->oauth_token }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-transparent">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">OAuth Token Secret</label>
            <input type="password" name="oauth_token_secret" value="{{ $settings->oauth_token_secret }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-transparent">
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-sky-500 text-white px-6 py-2 rounded-lg hover:bg-sky-600 font-medium">
                Save Settings
            </button>
            <a href="/admin/marketing/twitter/auth" class="bg-slate-600 text-white px-6 py-2 rounded-lg hover:bg-slate-700 font-medium">
                Connect Twitter
            </a>
        </div>
    </div>
</form>
@endsection