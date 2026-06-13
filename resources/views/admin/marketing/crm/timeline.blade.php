@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.marketing.leads') }}" class="text-slate-600 hover:text-slate-800">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $lead->name }}</h1>
                <p class="text-slate-500">{{ $lead->email }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.marketing.deals') }}?lead={{ $lead->id }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="fas fa-dollar-sign mr-2"></i>Add Deal
            </a>
        </div>
    </div>

    <!-- Add Activity Form -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="font-bold text-slate-800 mb-4">Add Activity</h3>
        <form method="POST" action="{{ route('admin.marketing.leads.activity.store', $lead) }}" class="flex gap-4">
            @csrf
            <select name="type" class="border border-slate-300 rounded-lg px-4 py-2">
                @foreach(App\Models\LeadActivity::activityTypes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="text" name="description" placeholder="Description" required class="flex-1 border border-slate-300 rounded-lg px-4 py-2">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">Add</button>
        </form>
    </div>

    <!-- Activity Timeline -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-bold text-slate-800 mb-4">Activity Timeline</h3>
        
        @if($activities->count() > 0)
        <div class="space-y-4">
            @foreach($activities as $activity)
            <div class="flex gap-4 border-l-2 border-indigo-200 pl-4 relative">
                <div class="absolute -left-2 top-0 w-4 h-4 bg-indigo-600 rounded-full"></div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-600">{{ $activity->type }}</span>
                        <span class="text-xs text-slate-400">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-slate-700">{{ $activity->description }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4">
            {{ $activities->links() }}
        </div>
        @else
        <p class="text-slate-500 text-center py-8">No activities yet</p>
        @endif
    </div>
</div>
@endsection