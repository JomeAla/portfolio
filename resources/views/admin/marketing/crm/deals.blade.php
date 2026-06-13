@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Deals Pipeline</h1>
            <p class="text-slate-600 mt-1">Track your sales opportunities</p>
        </div>
        <button onclick="document.getElementById('newDealModal').classList.remove('hidden')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>New Deal
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    <!-- Pipeline Overview -->
    <div class="grid grid-cols-7 gap-4 mb-8">
        @foreach($pipeline as $stage => $data)
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-lg font-bold text-slate-800">{{ $data['count'] }}</div>
            <div class="text-xs text-slate-500">{{ $data['label'] }}</div>
            <div class="text-sm text-green-600">${{ number_format($data['value'], 0) }}</div>
        </div>
        @endforeach
    </div>

    @php $dealsByStage = $deals->groupBy('stage'); @endphp
    <!-- Pipeline View -->
    <div class="grid grid-cols-7 gap-4">
        @foreach(['lead' => 'Lead', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'proposal' => 'Proposal Sent', 'negotiation' => 'Negotiation', 'won' => 'Won', 'lost' => 'Lost'] as $stage => $label)
        <div class="bg-slate-50 rounded-lg p-3">
            <div class="font-bold text-slate-700 text-sm mb-3 text-center">{{ $label }}</div>
            <div class="space-y-3">
                @foreach($dealsByStage->get($stage, collect()) as $deal)
                <div class="bg-white rounded-lg shadow-sm p-3 cursor-pointer hover:shadow-md">
                    <div class="font-medium text-slate-800 text-sm">{{ $deal->title }}</div>
                    <div class="text-lg font-bold text-green-600">${{ number_format($deal->value, 0) }}</div>
                    @if($deal->lead)
                    <div class="text-xs text-slate-500">{{ $deal->lead->email }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- New Deal Modal -->
<div id="newDealModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold text-slate-800 mb-4">New Deal</h3>
        <form method="POST" action="{{ route('admin.marketing.deals.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Deal Title</label>
                    <input type="text" name="title" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Value ($)</label>
                    <input type="number" name="value" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Stage</label>
                    <select name="stage" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                        @foreach(\App\Models\Deal::stages() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Expected Close</label>
                    <input type="date" name="expected_close_date" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Create</button>
                <button type="button" onclick="document.getElementById('newDealModal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
</script>
@endsection