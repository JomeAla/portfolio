@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Run Database Migrations</h1>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-yellow-700">
                <strong>Warning:</strong> This will run pending migrations on your live database.
            </p>
        </div>

        <form method="POST" action="/migrate">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Security Key</label>
                <input type="text" name="key" required 
                    class="w-full border border-slate-300 rounded-lg px-4 py-2"
                    placeholder="Enter migration key">
            </div>

            <button type="submit" 
                class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                Run Migrations
            </button>
        </form>
    </div>
</div>
@endsection