@extends('layouts.admin')

@section('title', 'Add Lead')

@section('content')
<div class="mb-6">
    <a href="/admin/marketing/leads" class="text-blue-600 hover:text-blue-800">&larr; Back to Leads</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-4">Add New Lead</h2>
            
            <form method="POST" action="/admin/marketing/leads">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email *</label>
                    <input type="email" name="email" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="email@example.com">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Name</label>
                    <input type="text" name="name" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Full name">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Assign to Sequence</label>
                    <select name="sequence_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">-- No sequence --</option>
                        @foreach($sequences as $sequence)
                        <option value="{{ $sequence->id }}">{{ $sequence->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Lead will receive emails from this sequence</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="new">New</option>
                        <option value="active">Active</option>
                        <option value="contacted">Contacted</option>
                        <option value="converted">Converted</option>
                        <option value="lost">Lost</option>
                    </select>
                </div>

                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                    Add Lead
                </button>
            </form>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-slate-800 mb-3">Quick Stats</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Leads</span>
                    <span class="font-medium text-slate-800">{{ \App\Models\Lead::count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Newsletter Subscribers</span>
                    <span class="font-medium text-slate-800">{{ \App\Models\Lead::where('is_newsletter', true)->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Active Sequences</span>
                    <span class="font-medium text-slate-800">{{ \App\Models\EmailSequence::where('is_active', true)->count() }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-slate-800 mb-3">CSV Import</h3>
            <p class="text-sm text-slate-500 mb-3">Import leads from CSV file (email, name columns)</p>
            <form method="POST" action="/admin/marketing/leads/import" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="file" name="csv_file" accept=".csv" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm font-medium">
                    Import Leads
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
