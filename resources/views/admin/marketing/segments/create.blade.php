@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.marketing.segments') }}" class="text-slate-600 hover:text-slate-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Create Segment</h1>
    </div>

    <form method="POST" action="{{ route('admin.marketing.segments.store') }}" class="space-y-6">
        @csrf
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Segment Name</label>
                    <input type="text" name="name" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <input type="text" name="description" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Conditions</h2>
            <p class="text-sm text-slate-500 mb-4">Leads must match ALL conditions to be in this segment.</p>
            
            <div id="conditionsContainer" class="space-y-4">
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
            </div>

            <button type="button" onclick="addCondition()" class="mt-4 text-indigo-600 hover:text-indigo-800 text-sm">
                <i class="fas fa-plus mr-1"></i>Add Condition
            </button>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                Create Segment
            </button>
        </div>
    </form>
</div>

<script>
let conditionCount = 1;
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