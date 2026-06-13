@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.marketing.segments') }}" class="text-slate-600 hover:text-slate-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Edit Segment</h1>
    </div>

    <form method="POST" action="{{ route('admin.marketing.segments.update', $segment) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Segment Name</label>
                    <input type="text" name="name" value="{{ $segment->name }}" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <input type="text" name="description" value="{{ $segment->description }}" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
            </div>
            <div class="mt-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ $segment->is_active ? 'checked' : '' }} class="rounded">
                    <span class="text-sm font-medium text-slate-700">Active</span>
                </label>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Conditions</h2>
            <p class="text-sm text-slate-500 mb-4">Leads must match ALL conditions to be in this segment.</p>
            
            <div id="conditionsContainer" class="space-y-4">
                @if($segment->conditions && is_array($segment->conditions))
                    @foreach($segment->conditions as $index => $condition)
                    <div class="condition-row flex gap-4 items-center">
                        <select name="conditions[{{ $index }}][field]" class="border border-slate-300 rounded-lg px-4 py-2">
                            <option value="score_above" {{ ($condition['field'] ?? '') == 'score_above' ? 'selected' : '' }}>Score Above</option>
                            <option value="score_below" {{ ($condition['field'] ?? '') == 'score_below' ? 'selected' : '' }}>Score Below</option>
                            <option value="status" {{ ($condition['field'] ?? '') == 'status' ? 'selected' : '' }}>Status</option>
                            <option value="source" {{ ($condition['field'] ?? '') == 'source' ? 'selected' : '' }}>Source</option>
                            <option value="created_after" {{ ($condition['field'] ?? '') == 'created_after' ? 'selected' : '' }}>Created After</option>
                            <option value="created_before" {{ ($condition['field'] ?? '') == 'created_before' ? 'selected' : '' }}>Created Before</option>
                        </select>
                        <input type="text" name="conditions[{{ $index }}][value]" value="{{ $condition['value'] ?? '' }}" placeholder="Value" class="border border-slate-300 rounded-lg px-4 py-2 flex-1">
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @endforeach
                @else
                <div class="condition-row flex gap-4 items-center">
                    <select name="conditions[0][field]" class="border border-slate-300 rounded-lg px-4 py-2">
                        <option value="score_above">Score Above</option>
                        <option value="score_below">Score Below</option>
                        <option value="status">Status</option>
                        <option value="source">Source</option>
                        <option value="created_after">Created After</option>
                        <option value="created_before">Created Before</option>
                    </select>
                    <input type="text" name="conditions[0][value]" placeholder="Value" class="border border-slate-300 rounded-lg px-4 py-2 flex-1">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                @endif
            </div>

            <button type="button" onclick="addCondition()" class="mt-4 text-indigo-600 hover:text-indigo-800 text-sm">
                <i class="fas fa-plus mr-1"></i>Add Condition
            </button>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                Update Segment
            </button>
        </div>
    </form>
</div>

<script>
let conditionCount = {{ isset($segment->conditions) && is_array($segment->conditions) ? count($segment->conditions) : 1 }};
function addCondition() {
    const container = document.getElementById('conditionsContainer');
    const row = document.createElement('div');
    row.className = 'condition-row flex gap-4 items-center';
    row.innerHTML = `
        <select name="conditions[${conditionCount}][field]" class="border border-slate-300 rounded-lg px-4 py-2">
            <option value="score_above">Score Above</option>
            <option value="score_below">Score Below</option>
            <option value="status">Status</option>
            <option value="source">Source</option>
            <option value="created_after">Created After</option>
            <option value="created_before">Created Before</option>
        </select>
        <input type="text" name="conditions[${conditionCount}][value]" placeholder="Value" class="border border-slate-300 rounded-lg px-4 py-2 flex-1">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(row);
    conditionCount++;
}
</script>
@endsection