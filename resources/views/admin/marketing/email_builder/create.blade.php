@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Visual Email Builder</h1>
            <p class="text-slate-600 mt-1">Drag and drop blocks to create emails</p>
        </div>
        <div class="flex gap-3">
            <button onclick="previewEmail()" class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                <i class="fas fa-eye mr-2"></i>Preview
            </button>
            <button onclick="saveTemplate()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-save mr-2"></i>Save Template
            </button>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Blocks Sidebar -->
        <div class="col-span-3">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-bold text-slate-800 mb-4">Blocks</h3>
                <div class="space-y-2">
                    <div class="p-3 bg-slate-50 rounded cursor-move hover:bg-slate-100 draggable" draggable="true" data-type="header">
                        <i class="fas fa-heading mr-2"></i>Header
                    </div>
                    <div class="p-3 bg-slate-50 rounded cursor-move hover:bg-slate-100 draggable" draggable="true" data-type="text">
                        <i class="fas fa-paragraph mr-2"></i>Text
                    </div>
                    <div class="p-3 bg-slate-50 rounded cursor-move hover:bg-slate-100 draggable" draggable="true" data-type="image">
                        <i class="fas fa-image mr-2"></i>Image
                    </div>
                    <div class="p-3 bg-slate-50 rounded cursor-move hover:bg-slate-100 draggable" draggable="true" data-type="button">
                        <i class="fas fa-mouse-pointer mr-2"></i>Button
                    </div>
                    <div class="p-3 bg-slate-50 rounded cursor-move hover:bg-slate-100 draggable" draggable="true" data-type="divider">
                        <i class="fas fa-minus mr-2"></i>Divider
                    </div>
                    <div class="p-3 bg-slate-50 rounded cursor-move hover:bg-slate-100 draggable" draggable="true" data-type="columns">
                        <i class="fas fa-columns mr-2"></i>Two Columns
                    </div>
                </div>
            </div>

            <!-- Saved Templates -->
            <div class="bg-white rounded-lg shadow p-4 mt-4">
                <h3 class="font-bold text-slate-800 mb-4">Templates</h3>
                @forelse($templates as $template)
                <div class="p-2 border border-slate-200 rounded mb-2 cursor-pointer hover:bg-slate-50" onclick="loadTemplate('{{ $template->id }}')">
                    <div class="text-sm font-medium">{{ $template->name }}</div>
                    <div class="text-xs text-slate-500">{{ $template->subject }}</div>
                </div>
                @empty
                <p class="text-sm text-slate-500">No templates yet</p>
                @endforelse
            </div>
        </div>

        <!-- Canvas -->
        <div class="col-span-6">
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b border-slate-200">
                    <input type="text" id="emailName" placeholder="Template Name" 
                        class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div id="emailCanvas" class="min-h-[500px] p-4 bg-slate-100 overflow-y-auto">
                    <div id="canvasContent" class="bg-white min-h-[400px] shadow-lg max-w-[600px] mx-auto">
                        <!-- Dropped blocks will appear here -->
                        <div class="text-center text-slate-400 py-20" id="emptyState">
                            Drag blocks here to build your email
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Properties Panel -->
        <div class="col-span-3">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-bold text-slate-800 mb-4">Properties</h3>
                <div id="propertiesPanel">
                    <p class="text-sm text-slate-500">Select a block to edit properties</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for saving -->
<form id="saveForm" method="POST" action="{{ route('admin.marketing.email-builder.store') }}" style="display:none;">
    @csrf
    <input type="hidden" name="name" id="saveName">
    <input type="hidden" name="subject" id="saveSubject">
    <input type="hidden" name="description" id="saveDescription">
    <input type="hidden" name="template_data" id="saveTemplateData">
</form>

<script>
let blocks = [];
let selectedBlock = null;

document.querySelectorAll('.draggable').forEach(el => {
    el.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('type', el.dataset.type);
    });
});

const canvas = document.getElementById('emailCanvas');
canvas.addEventListener('dragover', (e) => e.preventDefault());
canvas.addEventListener('drop', (e) => {
    e.preventDefault();
    const type = e.dataTransfer.getData('type');
    addBlock(type);
});

function addBlock(type) {
    const id = Date.now();
    const block = { id, type, content: '', url: '', text: 'Click Here' };
    blocks.push(block);
    renderBlock(block);
    document.getElementById('emptyState').style.display = 'none';
    selectBlock(id);
}

function renderBlock(block) {
    const container = document.createElement('div');
    container.id = 'block-' + block.id;
    container.className = 'block-item p-4 border-b border-slate-200 hover:bg-slate-50 cursor-pointer relative';
    container.onclick = () => selectBlock(block.id);

    let html = '';
    switch(block.type) {
        case 'header':
            html = '<h1 class="text-2xl font-bold text-center">' + (block.content || 'Header Title') + '</h1>';
            break;
        case 'text':
            html = '<p>' + (block.content || 'Enter your text here...') + '</p>';
            break;
        case 'image':
            html = block.url ? '<img src="' + block.url + '" class="max-w-full">' : '<div class="bg-slate-200 p-8 text-center">Click to add image URL</div>';
            break;
        case 'button':
            html = '<div class="text-center"><a href="#" class="inline-block bg-blue-600 text-white px-6 py-3 rounded">' + block.text + '</a></div>';
            break;
        case 'divider':
            html = '<hr class="border-slate-300">';
            break;
        case 'columns':
            html = '<div class="grid grid-cols-2 gap-4"><div>Column 1</div><div>Column 2</div></div>';
            break;
    }

    container.innerHTML = html + '<button class="absolute top-2 right-2 text-red-500" onclick="deleteBlock(' + block.id + ')">×</button>';
    document.getElementById('canvasContent').appendChild(container);
}

function selectBlock(id) {
    selectedBlock = blocks.find(b => b.id === id);
    showProperties(selectedBlock);
}

function showProperties(block) {
    const panel = document.getElementById('propertiesPanel');
    let html = '<div class="space-y-3">';

    switch(block.type) {
        case 'header':
        case 'text':
            html += '<div><label class="block text-sm font-medium">Content</label><textarea class="w-full border rounded p-2" onchange="updateBlock(\'content\', this.value)">' + block.content + '</textarea></div>';
            break;
        case 'image':
            html += '<div><label class="block text-sm font-medium">Image URL</label><input type="text" class="w-full border rounded p-2" value="' + (block.url || '') + '" onchange="updateBlock(\'url\', this.value)"></div>';
            break;
        case 'button':
            html += '<div><label class="block text-sm font-medium">Button Text</label><input type="text" class="w-full border rounded p-2" value="' + block.text + '" onchange="updateBlock(\'text\', this.value)"></div>';
            html += '<div><label class="block text-sm font-medium">Link URL</label><input type="text" class="w-full border rounded p-2" value="' + (block.url || '') + '" onchange="updateBlock(\'url\', this.value)"></div>';
            break;
    }

    html += '</div>';
    panel.innerHTML = html;
}

function updateBlock(key, value) {
    if (selectedBlock) {
        selectedBlock[key] = value;
        renderAllBlocks();
        selectBlock(selectedBlock.id);
    }
}

function deleteBlock(id) {
    blocks = blocks.filter(b => b.id !== id);
    renderAllBlocks();
}

function renderAllBlocks() {
    document.getElementById('canvasContent').innerHTML = blocks.length ? '' : '<div class="text-center text-slate-400 py-20" id="emptyState">Drag blocks here to build your email</div>';
    blocks.forEach(renderBlock);
}

function saveTemplate() {
    const name = document.getElementById('emailName').value;
    if (!name) { alert('Please enter a template name'); return; }

    document.getElementById('saveName').value = name;
    document.getElementById('saveSubject').value = name + ' Email';
    document.getElementById('saveTemplateData').value = JSON.stringify({ blocks });
    document.getElementById('saveForm').submit();
}

function previewEmail() {
    const data = JSON.stringify({ blocks });
    fetch('{{ route("admin.marketing.email-builder.preview") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: 'template_data=' + encodeURIComponent(data)
    }).then(r => r.text()).then(html => {
        const win = window.open('', '_blank');
        win.document.write(html);
    });
}
</script>
@endsection