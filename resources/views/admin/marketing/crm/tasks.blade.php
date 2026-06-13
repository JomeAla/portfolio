@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tasks</h1>
            <p class="text-slate-600 mt-1">Manage lead follow-ups and reminders</p>
        </div>
        <button onclick="document.getElementById('newTaskModal').classList.remove('hidden')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>New Task
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-slate-800">{{ $tasks->count() }}</div>
            <div class="text-sm text-slate-500">Total Tasks</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $tasks->where('status', 'pending')->count() }}</div>
            <div class="text-sm text-slate-500">Pending</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-red-600">{{ $overdue }}</div>
            <div class="text-sm text-slate-500">Overdue</div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Task</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Lead</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Due Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Priority</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($tasks as $task)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-800">{{ $task->title }}</div>
                        @if($task->description)
                        <div class="text-sm text-slate-500">{{ $task->description }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($task->lead)
                        <a href="{{ route('admin.marketing.leads.timeline', $task->lead) }}" class="text-indigo-600 hover:text-indigo-800">
                            {{ $task->lead->email }}
                        </a>
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($task->due_date)
                        <span class="{{ $task->due_date < now() && $task->status != 'completed' ? 'text-red-600' : 'text-slate-600' }}">
                            {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                        </span>
                        @else
                        <span class="text-slate-400">No due date</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded 
                            {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-700' : 
                               ($task->priority === 'high' ? 'bg-orange-100 text-orange-700' : 
                               ($task->priority === 'medium' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600')) }}">
                            {{ $task->priority }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded 
                            {{ $task->status === 'completed' ? 'bg-green-100 text-green-700' : 
                               ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ $task->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.marketing.tasks.update', $task) }}" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="{{ $task->status === 'completed' ? 'pending' : 'completed' }}">
                            <button type="submit" class="text-sm text-slate-600 hover:text-slate-800 mr-3">
                                {{ $task->status === 'completed' ? 'Reopen' : 'Complete' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.marketing.tasks.destroy', $task) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No tasks yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- New Task Modal -->
<div id="newTaskModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold text-slate-800 mb-4">New Task</h3>
        <form method="POST" action="{{ route('admin.marketing.tasks.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Task Title</label>
                    <input type="text" name="title" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full border border-slate-300 rounded-lg px-4 py-2"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Due Date</label>
                    <input type="date" name="due_date" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Priority</label>
                    <select name="priority" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                        @foreach(\App\Models\LeadTask::priorities() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Create</button>
                <button type="button" onclick="document.getElementById('newTaskModal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection