@extends('layouts.admin')

@section('title', 'Edit Sequence')

@section('content')
<form method="POST" action="/admin/marketing/sequences/{{ $sequence->id }}/update" id="sequenceForm">
    @csrf
    <div class="mb-6">
        <a href="/admin/marketing/sequences" class="text-blue-600 hover:text-blue-800">&larr; Back to Sequences</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Sequence Details</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Name</label>
                    <input type="text" name="name" value="{{ $sequence->name }}" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ $sequence->description }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" {{ $sequence->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-slate-700">Active</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-3 rounded-lg hover:bg-indigo-700 font-medium">
                Update Sequence
            </button>
        </div>
    </div>
</form>

<!-- Add Step Form (Outside Main Form) -->
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-slate-800 mb-4">Add Email Step</h2>
    <form method="POST" action="/admin/marketing/sequences/{{ $sequence->id }}/steps">
        @csrf
    
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Subject</label>
                <input type="text" name="subject" id="subjectInput" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Delay (days)</label>
                <input type="number" name="delay_days" value="0" min="0" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
        </div>

        <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <label class="block text-sm font-medium text-slate-700">Email Body</label>
                <button type="button" onclick="togglePreview()" class="text-sm text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-eye mr-1"></i> Live Preview
                </button>
            </div>
            <textarea name="body" id="bodyInput" rows="6" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" oninput="updatePreview()"></textarea>
        </div>

        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
            Add Step
        </button>
    </form>

    <div id="previewPanel" class="hidden mt-6 border-t border-slate-200 pt-4">
        <h3 class="font-bold text-slate-800 mb-2">Email Preview</h3>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="border-b border-gray-200 pb-2 mb-2">
                <span class="text-xs text-gray-500">Subject:</span>
                <span id="previewSubject" class="ml-2 font-medium text-gray-800"></span>
            </div>
            <div id="previewBody" class="bg-white border border-gray-100 rounded p-4 text-sm text-gray-700" style="min-height: 200px;"></div>
        </div>
    </div>
</div>

<!-- Existing Steps -->
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-slate-800 mb-4">Email Steps</h2>
    
    @if($sequence->steps->count() > 0)
        <div class="space-y-4 mb-6">
            @foreach($sequence->steps->sortBy('step_number') as $step)
                <div class="border border-slate-200 rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-sm font-medium text-slate-700">Step {{ $step->step_number }}</span>
                        <span class="text-xs text-slate-500">{{ $step->delay_days }} days delay</span>
                    </div>
                    <h3 class="font-bold text-slate-800 mb-1">{{ $step->subject }}</h3>
                    <p class="text-sm text-slate-500 mb-3">{{ Str::limit($step->body, 100) }}</p>
                    <div class="flex items-center gap-3">
                        <a href="/admin/marketing/steps/{{ $step->id }}/edit" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</a>
                        <button type="button" class="text-blue-600 hover:text-blue-800 text-sm preview-btn" data-subject="{{ $step->subject }}" data-body="{!! addslashes($step->body) !!}">Preview</button>
                        <button type="button" onclick="deleteStep({{ $step->id }})" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-slate-500 mb-4">No steps added yet.</p>
    @endif
</div>

<script>
function deleteStep(id) {
    if(confirm('Delete this step?')) {
        fetch('/admin/marketing/steps/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => window.location.reload());
    }
}

function togglePreview() {
    const panel = document.getElementById('previewPanel');
    panel.classList.toggle('hidden');
}

function updatePreview() {
    document.getElementById('previewSubject').textContent = document.getElementById('subjectInput').value;
    document.getElementById('previewBody').innerHTML = document.getElementById('bodyInput').value.replace(/\n/g, '<br>');
}

document.querySelectorAll('.preview-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('subjectInput').value = this.dataset.subject;
        document.getElementById('bodyInput').value = this.dataset.body;
        updatePreview();
        document.getElementById('previewPanel').classList.remove('hidden');
    });
});
</script>
@endsection