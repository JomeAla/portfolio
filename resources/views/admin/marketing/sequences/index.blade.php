@extends('layouts.admin')

@section('title', 'Email Sequences')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Email Sequences</h1>
        <p class="text-slate-600 mt-2">Create automated email drip campaigns</p>
    </div>
    <a href="/admin/marketing/sequences/create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i>New Sequence
    </a>
</div>

@foreach($sequences as $sequence)
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800">{{ $sequence->name }}</h3>
            <p class="text-sm text-slate-500">{{ $sequence->description ?? 'No description' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-2 py-1 text-xs rounded {{ $sequence->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $sequence->is_active ? 'Active' : 'Inactive' }}
            </span>
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">
                {{ $sequence->steps_count ?? 0 }} steps
            </span>
            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded">
                {{ $sequence->leads_count ?? 0 }} leads
            </span>
        </div>
    </div>

    <div class="flex gap-2 mb-6">
        <a href="/admin/marketing/sequences/{{ $sequence->id }}/edit" class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
        <form method="POST" action="/admin/marketing/sequences/{{ $sequence->id }}" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete sequence?')">Delete</button>
        </form>
    </div>

    @if($sequence->steps->count() > 0)
    <div class="border-t border-slate-200 pt-4">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Email Flow</h4>
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-play text-green-600 text-sm"></i>
                </div>
                <span class="text-xs text-slate-500">Start</span>
            </div>
            
            @foreach($sequence->steps->sortBy('step_number') as $index => $step)
            <div class="flex items-center gap-2">
                <svg class="w-6 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
                <div class="relative group">
                    <div class="w-48 bg-slate-50 border-2 border-slate-200 rounded-lg p-3 hover:border-indigo-400 transition-colors cursor-pointer">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-5 h-5 bg-indigo-100 rounded-full flex items-center justify-center text-xs font-bold text-indigo-600">{{ $step->step_number }}</span>
                            <span class="text-xs text-slate-500">{{ $step->delay_days }} day{{ $step->delay_days != 1 ? 's' : '' }} delay</span>
                        </div>
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $step->subject }}</p>
                    </div>
                    <div class="absolute hidden group-hover:block z-10 bg-slate-800 text-white text-xs rounded p-2 -top-12 left-0 w-64">
                        <p class="font-medium">{{ $step->subject }}</p>
                        <p class="text-slate-300 mt-1">{{ Str::limit(strip_tags($step->body), 100) }}</p>
                    </div>
                </div>
            </div>
            @endforeach
            
            <div class="flex items-center gap-2">
                <svg class="w-6 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
                <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-slate-500 text-sm"></i>
                </div>
                <span class="text-xs text-slate-500">Complete</span>
            </div>
        </div>
    </div>
    @else
    <div class="border-t border-slate-200 pt-4">
        <p class="text-sm text-slate-500">No steps yet. <a href="/admin/marketing/sequences/{{ $sequence->id }}/edit" class="text-indigo-600 hover:text-indigo-800">Add steps</a></p>
    </div>
    @endif
</div>
@endforeach

<div class="mt-4">
    {{ $sequences->links() }}
</div>
@endsection