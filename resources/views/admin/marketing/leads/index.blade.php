@extends('layouts.admin')

@section('title', 'Leads')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Leads</h1>
        <p class="text-slate-600 mt-2">Manage captured leads from landing pages</p>
    </div>
    <div class="flex gap-3">
        <a href="/admin/marketing/leads/export" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            <i class="fas fa-download mr-2"></i>Export CSV
        </a>
        <a href="/admin/marketing/leads/create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>Add Lead
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow mb-6 p-4">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by email or name..." class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="w-40">
            <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
            <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
            </select>
        </div>
        <div class="w-40">
            <label class="block text-xs font-medium text-slate-500 mb-1">Sequence</label>
            <select name="sequence_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Sequences</option>
                @foreach($sequences as $seq)
                <option value="{{ $seq->id }}" {{ request('sequence_id') == $seq->id ? 'selected' : '' }}>{{ $seq->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-40">
            <label class="block text-xs font-medium text-slate-500 mb-1">Source</label>
            <select name="source" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Sources</option>
                <option value="landing_page" {{ request('source') == 'landing_page' ? 'selected' : '' }}>Landing Page</option>
                <option value="admin" {{ request('source') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="import" {{ request('source') == 'import' ? 'selected' : '' }}>Import</option>
                <option value="newsletter" {{ request('source') == 'newsletter' ? 'selected' : '' }}>Newsletter</option>
            </select>
        </div>
        <div class="w-40">
            <label class="block text-xs font-medium text-slate-500 mb-1">Tag</label>
            <select name="tag_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Tags</option>
                @if(isset($tags))
                @foreach($tags as $tag)
                <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                @endforeach
                @endif
            </select>
        </div>
        <div class="w-32">
            <label class="block text-xs font-medium text-slate-500 mb-1">Min Score</label>
            <input type="number" name="min_score" value="{{ request('min_score') }}" placeholder="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="bg-slate-600 text-white px-4 py-2 rounded-lg hover:bg-slate-700 text-sm">
            Filter
        </button>
        @if(request()->anyFilled(['search', 'status', 'sequence_id', 'source']))
        <a href="/admin/marketing/leads" class="text-slate-500 hover:text-slate-700 text-sm py-2">
            Clear
        </a>
        @endif
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Score</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Tags</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Sequence</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Source</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($leads as $lead)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-slate-800">{{ $lead->email }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-slate-600">{{ $lead->name ?? '-' }}</div>
                </td>
                <td class="px-6 py-4">
                    @php
                        $statusColors = [
                            'new' => 'bg-blue-100 text-blue-700',
                            'active' => 'bg-green-100 text-green-700',
                            'contacted' => 'bg-yellow-100 text-yellow-700',
                            'converted' => 'bg-purple-100 text-purple-700',
                            'lost' => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <span class="px-2 py-1 text-xs rounded {{ $statusColors[$lead->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ ucfirst($lead->status) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm font-medium {{ $lead->score > 50 ? 'text-green-600' : 'text-slate-600' }}">{{ $lead->score }}</span>
                </td>
                <td class="px-6 py-4">
                    @forelse($lead->tags as $tag)
                        <span class="inline-block text-xs px-2 py-1 rounded mr-1 mb-1" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">{{ $tag->name }}</span>
                    @empty
                        <span class="text-sm text-slate-400">-</span>
                    @endforelse
                </td>
                <td class="px-6 py-4">
                    @if($lead->sequence)
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">{{ $lead->sequence->name }}</span>
                    @else
                        <span class="text-sm text-slate-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @php
                        $sourceLabels = [
                            'landing_page' => 'Landing Page',
                            'admin' => 'Admin',
                            'import' => 'Import',
                            'newsletter' => 'Newsletter',
                        ];
                    @endphp
                    <span class="text-sm text-slate-600">{{ $sourceLabels[$lead->source] ?? $lead->source ?? 'Direct' }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" onclick="editLead({{ $lead->id }}, '{{ $lead->email }}', '{{ $lead->name ?? '' }}', '{{ $lead->status }}', {{ $lead->sequence_id ?? 'null' }})" class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                        <button type="button" onclick="manageTags({{ $lead->id }})" class="text-indigo-600 hover:text-indigo-800 text-sm">Tags</button>
                        <form method="POST" action="/admin/marketing/leads/{{ $lead->id }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete this lead?')">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                    No leads found. <a href="/admin/marketing/leads/create" class="text-indigo-600 hover:text-indigo-800">Add one</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $leads->links() }}
</div>

<div id="editLeadModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Edit Lead</h3>
        <form id="editLeadForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                <input type="email" name="email" id="editEmail" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Name</label>
                <input type="text" name="name" id="editName" class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Sequence</label>
                <select name="sequence_id" id="editSequence" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    <option value="">-- No sequence --</option>
                    @foreach($sequences as $seq)
                    <option value="{{ $seq->id }}">{{ $seq->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                <select name="status" id="editStatus" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    <option value="new">New</option>
                    <option value="active">Active</option>
                    <option value="contacted">Contacted</option>
                    <option value="converted">Converted</option>
                    <option value="lost">Lost</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Save</button>
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editLead(id, email, name, status, sequenceId) {
    document.getElementById('editEmail').value = email;
    document.getElementById('editName').value = name;
    document.getElementById('editStatus').value = status;
    document.getElementById('editSequence').value = sequenceId || '';
    document.getElementById('editLeadForm').action = '/admin/marketing/leads/' + id;
    document.getElementById('editLeadModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('editLeadModal').classList.add('hidden');
}

function manageTags(leadId) {
    var tags = @json($tags ?? []);
    var lead = {!! json_encode($leads->items()) !!}.find(function(l) { return l.id === leadId; });
    var currentTags = lead ? lead.tags || [] : [];
    
    var checkboxHtml = '';
    tags.forEach(function(tag) {
        var isChecked = currentTags.some(function(t) { return t.id === tag.id; });
        checkboxHtml += '<label class="flex items-center gap-2 mb-2"><input type="checkbox" name="tag_ids[]" value="' + tag.id + '"' + (isChecked ? ' checked' : '') + ' class="rounded"><span class="w-3 h-3 rounded-full" style="background:' + tag.color + '"></span>' + tag.name + '</label>';
    });
    
    document.getElementById('tagsCheckboxContainer').innerHTML = checkboxHtml || 'No tags available';
    document.getElementById('tagsForm').action = '/admin/marketing/leads/' + leadId + '/tags';
    document.getElementById('tagsModal').classList.remove('hidden');
}

function closeTagsModal() {
    document.getElementById('tagsModal').classList.add('hidden');
}
</script>

<!-- Tags Modal -->
<div id="tagsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Manage Tags</h3>
        <form method="POST" id="tagsForm" action="">
            @csrf
            <div id="tagsCheckboxContainer" class="mb-4 max-h-60 overflow-y-auto">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Save Tags</button>
                <button type="button" onclick="closeTagsModal()" class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection