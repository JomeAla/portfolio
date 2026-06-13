@extends('layouts.admin')

@section('title', 'Deploy Funnel: ' . $stagingFunnel->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Deploy to Production</h1>
            <p class="text-slate-500">Staging: {{ $stagingFunnel->name }}</p>
        </div>
        <a href="/admin/marketing/funnels/{{ $stagingFunnel->id }}/edit" class="text-blue-600 hover:text-blue-800">
            &larr; Back to Funnel
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-slate-800 mb-6">Choose Deployment Mode</h2>
        
        <form action="/admin/marketing/funnels/{{ $stagingFunnel->id }}/deploy" method="POST">
            @csrf
            <input type="hidden" name="mode" id="deployMode">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <label class="cursor-pointer">
                    <input type="radio" name="mode_radio" value="clone" class="hidden peer" checked>
                    <div class="border-2 border-slate-200 rounded-lg p-4 hover:border-indigo-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition">
                        <div class="text-2xl mb-2">📋</div>
                        <h3 class="font-bold text-slate-800">Clone</h3>
                        <p class="text-sm text-slate-500 mt-1">Create new production funnel</p>
                    </div>
                </label>
                
                <label class="cursor-pointer">
                    <input type="radio" name="mode_radio" value="replace" class="hidden peer">
                    <div class="border-2 border-slate-200 rounded-lg p-4 hover:border-indigo-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition">
                        <div class="text-2xl mb-2">🔄</div>
                        <h3 class="font-bold text-slate-800">Replace</h3>
                        <p class="text-sm text-slate-500 mt-1">Update existing production</p>
                    </div>
                </label>
                
                <label class="cursor-pointer">
                    <input type="radio" name="mode_radio" value="export" class="hidden peer">
                    <div class="border-2 border-slate-200 rounded-lg p-4 hover:border-indigo-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition">
                        <div class="text-2xl mb-2">📥</div>
                        <h3 class="font-bold text-slate-800">Export Only</h3>
                        <p class="text-sm text-slate-500 mt-1">Download JSON file</p>
                    </div>
                </label>
            </div>

            @if($productionFunnel)
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                <p class="text-amber-800 text-sm">
                    <i class="fas fa-info-circle mr-1"></i>
                    Production version exists: <strong>{{ $productionFunnel->name }}</strong> (ID: {{ $productionFunnel->id }})
                </p>
            </div>
            @endif

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 font-medium">
                    <i class="fas fa-rocket mr-2"></i>Deploy
                </button>
                <a href="/admin/marketing/funnels/{{ $stagingFunnel->id }}/edit" class="px-6 py-3 border border-slate-300 rounded-lg hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <div class="bg-slate-50 rounded-lg p-6 mt-6">
        <h3 class="font-bold text-slate-700 mb-3">Deployment Summary</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-slate-500">Stages:</span>
                <strong>{{ $stagingFunnel->stages->count() }}</strong>
            </div>
            <div>
                <span class="text-slate-500">Automation:</span>
                <strong>{{ $stagingFunnel->automation_enabled ? 'Enabled' : 'Disabled' }}</strong>
            </div>
            <div>
                <span class="text-slate-500">Upsell:</span>
                <strong>{{ $stagingFunnel->upsell_enabled ? 'Enabled' : 'Disabled' }}</strong>
            </div>
            <div>
                <span class="text-slate-500">Order Bumps:</span>
                <strong>{{ $stagingFunnel->order_bumps_enabled ? 'Enabled' : 'Disabled' }}</strong>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="mode_radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('deployMode').value = this.value;
    });
});
document.getElementById('deployMode').value = document.querySelector('input[name="mode_radio"]:checked').value;
</script>
@endsection