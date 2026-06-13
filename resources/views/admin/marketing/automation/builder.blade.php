@extends('layouts.admin')

@section('title', 'Automation Builder')

@section('content')
<div class="h-screen flex flex-col">
    <div class="bg-slate-800 text-white px-6 py-3 flex items-center justify-between">
        <div>
            <a href="/admin/marketing/funnels/{{ $funnel->id }}" class="text-slate-400 hover:text-white text-sm">
                <i class="fas fa-arrow-left mr-1"></i>Back to Funnel
            </a>
            <h1 class="text-lg font-bold">Automation Builder: {{ $funnel->name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="clearCanvas()" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 rounded text-sm">
                Clear
            </button>
            <button type="button" onclick="saveWorkflow()" class="px-4 py-1.5 bg-green-600 hover:bg-green-700 rounded text-sm font-medium">
                Save Workflow
            </button>
        </div>
    </div>

    <div class="flex-1 flex overflow-hidden">
        <div class="w-64 bg-slate-900 border-r border-slate-700 flex flex-col">
            <div class="p-4 border-b border-slate-700">
                <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wide">Triggers</h3>
                <p class="text-xs text-slate-500 mt-1">Start workflows</p>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                <button type="button" onclick="addNode('trigger', 'lead_enters_funnel')" 
                    class="w-full text-left px-3 py-2 bg-blue-900 hover:bg-blue-800 text-blue-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-bolt w-4"></i>
                    <span>Lead enters funnel</span>
                </button>
                <button type="button" onclick="addNode('trigger', 'email_opened')" 
                    class="w-full text-left px-3 py-2 bg-blue-900 hover:bg-blue-800 text-blue-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-envelope-open w-4"></i>
                    <span>Email opened</span>
                </button>
                <button type="button" onclick="addNode('trigger', 'email_clicked')" 
                    class="w-full text-left px-3 py-2 bg-blue-900 hover:bg-blue-800 text-blue-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-link w-4"></i>
                    <span>Email clicked</span>
                </button>
                <button type="button" onclick="addNode('trigger', 'purchase_made')" 
                    class="w-full text-left px-3 py-2 bg-blue-900 hover:bg-blue-800 text-blue-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-shopping-cart w-4"></i>
                    <span>Purchase made</span>
                </button>
                <button type="button" onclick="addNode('trigger', 'tag_added')" 
                    class="w-full text-left px-3 py-2 bg-blue-900 hover:bg-blue-800 text-blue-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-tag w-4"></i>
                    <span>Tag added</span>
                </button>
                <button type="button" onclick="addNode('trigger', 'score_reached')" 
                    class="w-full text-left px-3 py-2 bg-blue-900 hover:bg-blue-800 text-blue-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-chart-line w-4"></i>
                    <span>Score reached</span>
                </button>
                <button type="button" onclick="addNode('trigger', 'page_visited')" 
                    class="w-full text-left px-3 py-2 bg-blue-900 hover:bg-blue-800 text-blue-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-globe w-4"></i>
                    <span>Page visited</span>
                </button>
                <button type="button" onclick="addNode('trigger', 'form_submitted')" 
                    class="w-full text-left px-3 py-2 bg-blue-900 hover:bg-blue-800 text-blue-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-clipboard-list w-4"></i>
                    <span>Form submitted</span>
                </button>
            </div>
        </div>

        <div class="flex-1 bg-slate-950 relative overflow-auto" id="canvas">
            <svg id="connectionsSvg" class="absolute inset-0 w-full h-full pointer-events-none">
                <defs>
                    <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="9" refY="3.5" orient="auto">
                        <polygon points="0 0, 10 3.5, 0 7" fill="#6366f1"/>
                    </marker>
                </defs>
            </svg>
            <div id="nodesContainer" class="min-h-full p-8">
                <div id="emptyState" class="text-center py-20">
                    <div class="text-slate-600 mb-4">
                        <i class="fas fa-project-diagram text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-slate-400">Build Your Workflow</h3>
                    <p class="text-slate-500 mt-2">Click items from the sidebars to add nodes</p>
                </div>
            </div>
        </div>

        <div class="w-64 bg-slate-900 border-l border-slate-700 flex flex-col">
            <div class="p-4 border-b border-slate-700">
                <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wide">Actions</h3>
                <p class="text-xs text-slate-500 mt-1">What happens next</p>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                <button type="button" onclick="addNode('action', 'send_email')" 
                    class="w-full text-left px-3 py-2 bg-green-900 hover:bg-green-800 text-green-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-paper-plane w-4"></i>
                    <span>Send email</span>
                </button>
                <button type="button" onclick="addNode('action', 'add_tag')" 
                    class="w-full text-left px-3 py-2 bg-green-900 hover:bg-green-800 text-green-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-plus-circle w-4"></i>
                    <span>Add tag</span>
                </button>
                <button type="button" onclick="addNode('action', 'remove_tag')" 
                    class="w-full text-left px-3 py-2 bg-green-900 hover:bg-green-800 text-green-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-minus-circle w-4"></i>
                    <span>Remove tag</span>
                </button>
                <button type="button" onclick="addNode('action', 'update_score')" 
                    class="w-full text-left px-3 py-2 bg-green-900 hover:bg-green-800 text-green-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-trophy w-4"></i>
                    <span>Update score</span>
                </button>
                <button type="button" onclick="addNode('action', 'enroll_sequence')" 
                    class="w-full text-left px-3 py-2 bg-green-900 hover:bg-green-800 text-green-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-list-ol w-4"></i>
                    <span>Enroll in sequence</span>
                </button>
                <button type="button" onclick="addNode('action', 'wait')" 
                    class="w-full text-left px-3 py-2 bg-purple-900 hover:bg-purple-800 text-purple-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-clock w-4"></i>
                    <span>Wait / Delay</span>
                </button>
                <button type="button" onclick="addNode('action', 'webhook')" 
                    class="w-full text-left px-3 py-2 bg-green-900 hover:bg-green-800 text-green-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-broadcast-tower w-4"></i>
                    <span>Trigger webhook</span>
                </button>
                <button type="button" onclick="addNode('action', 'notify')" 
                    class="w-full text-left px-3 py-2 bg-green-900 hover:bg-green-800 text-green-200 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-bell w-4"></i>
                    <span>Send notification</span>
                </button>
            </div>

            <div class="p-4 border-t border-slate-700">
                <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wide mb-2">Logic</h3>
                <div class="space-y-2">
                    <button type="button" onclick="addNode('logic', 'if_condition')" 
                        class="w-full text-left px-3 py-2 bg-yellow-900 hover:bg-yellow-800 text-yellow-200 rounded text-sm flex items-center gap-2">
                        <i class="fas fa-code-branch w-4"></i>
                        <span>If/Else</span>
                    </button>
                    <button type="button" onclick="addNode('logic', 'split')" 
                        class="w-full text-left px-3 py-2 bg-yellow-900 hover:bg-yellow-800 text-yellow-200 rounded text-sm flex items-center gap-2">
                        <i class="fas fa-project-diagram w-4"></i>
                        <span>A/B Split</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="workflowForm" method="POST" action="/admin/marketing/funnels/{{ $funnel->id }}">
    @csrf
    @method('PUT')
    <input type="hidden" name="automation_workflows" id="workflowData">
</form>

<script>
var sequencesData = @json($sequences ?? []);
var tagsData = @json($tags ?? []);
var webhooksData = @json($webhooks ?? []);
var existingWorkflow = @json($funnel->automation_workflows ?? null);

var triggerLabels = {
    'lead_enters_funnel': 'Lead enters funnel',
    'email_opened': 'Email opened',
    'email_clicked': 'Email clicked',
    'purchase_made': 'Purchase made',
    'tag_added': 'Tag added',
    'score_reached': 'Score reached',
    'page_visited': 'Page visited',
    'form_submitted': 'Form submitted'
};

var actionLabels = {
    'send_email': 'Send email',
    'add_tag': 'Add tag',
    'remove_tag': 'Remove tag',
    'update_score': 'Update score',
    'enroll_sequence': 'Enroll in sequence',
    'wait': 'Wait / Delay',
    'webhook': 'Trigger webhook',
    'notify': 'Send notification'
};

var logicLabels = {
    'if_condition': 'If/Else Condition',
    'split': 'A/B Split'
};

var nodes = [];
var connections = [];
var nodeIdCounter = 0;
var isConnecting = false;
var connectionStart = null;

function addNode(kind, type) {
    var emptyState = document.getElementById('emptyState');
    if (emptyState) emptyState.remove();

    var id = 'node_' + (++nodeIdCounter);
    var labels = kind === 'trigger' ? triggerLabels : (kind === 'action' ? actionLabels : logicLabels);
    var label = labels[type] || type;

    var node = {
        id: id,
        kind: kind,
        type: type,
        label: label,
        x: 150 + (nodes.length % 3) * 280,
        y: 100 + Math.floor(nodes.length / 3) * 180,
        config: {}
    };

    nodes.push(node);
    renderNode(node);
    updateConnections();
}

function renderNode(node) {
    var container = document.getElementById('nodesContainer');
    var el = document.createElement('div');
    el.id = node.id;
    el.className = 'node absolute w-56 bg-slate-800 rounded-lg border-2 shadow-lg cursor-move select-none';
    el.style.left = node.x + 'px';
    el.style.top = node.y + 'px';

    if (node.kind === 'trigger') {
        el.classList.add('border-blue-500');
    } else if (node.kind === 'action') {
        el.classList.add('border-green-500');
    } else {
        el.classList.add('border-yellow-500');
    }

    var bgClass = node.kind === 'trigger' ? 'bg-blue-600' : (node.kind === 'action' ? 'bg-green-600' : 'bg-yellow-600');
    var iconClass = node.kind === 'trigger' ? 'fa-bolt' : (node.kind === 'action' ? 'fa-play' : 'fa-code-branch');

    el.innerHTML = 
        '<div class="' + bgClass + ' text-white px-3 py-2 rounded-t flex items-center justify-between">' +
            '<span class="flex items-center gap-2 text-sm font-medium">' +
                '<i class="fas fa-' + iconClass + '"></i> ' +
                node.label +
            '</span>' +
            '<button type="button" onclick="startConnection(\'' + node.id + '\')" class="text-white hover:text-slate-200 text-xs">' +
                '<i class="fas fa-link"></i>' +
            '</button>' +
        '</div>' +
        '<div class="p-3 text-xs text-slate-300">' +
            getNodeConfigHtml(node) +
        '</div>' +
        '<button type="button" onclick="deleteNode(\'' + node.id + '\')" ' +
            'class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full text-xs">' +
            '<i class="fas fa-times"></i>' +
        '</button>';

    makeDraggable(el, node);
    container.appendChild(el);
}

function getNodeConfigHtml(node) {
    if (node.kind === 'trigger') {
        return getTriggerConfigHtml(node.type);
    } else if (node.kind === 'action') {
        return getActionConfigHtml(node.type);
    }
    return '<input type="text" placeholder="Value" class="w-full bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1">';
}

function getTriggerConfigHtml(type) {
    if (type === 'email_opened' || type === 'email_clicked') {
        return buildSelect('sequence', sequencesData);
    }
    if (type === 'tag_added') {
        return buildSelect('tag', tagsData);
    }
    if (type === 'score_reached') {
        return '<select class="w-full bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1 mb-2">' +
            '<option value="10">10 points</option>' +
            '<option value="25">25 points</option>' +
            '<option value="50">50 points</option>' +
            '<option value="100">100 points</option></select>';
    }
    if (type === 'page_visited') {
        return '<input type="text" placeholder="Page URL" class="w-full bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1">';
    }
    return '<input type="text" placeholder="Value" class="w-full bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1">';
}

function getActionConfigHtml(type) {
    if (type === 'send_email' || type === 'enroll_sequence') {
        return buildSelect('sequence', sequencesData);
    }
    if (type === 'add_tag' || type === 'remove_tag') {
        return buildSelect('tag', tagsData);
    }
    if (type === 'update_score') {
        return '<select class="w-full bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1 mb-2">' +
            '<option value="add">Add points</option>' +
            '<option value="set">Set to</option>' +
            '<option value="subtract">Subtract</option></select>' +
            '<input type="number" placeholder="Points" class="w-full bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1">';
    }
    if (type === 'wait') {
        return '<div class="flex gap-2"><input type="number" placeholder="1" value="1" class="w-16 bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1">' +
            '<select class="flex-1 bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1">' +
            '<option value="days">Days</option>' +
            '<option value="hours">Hours</option>' +
            '<option value="minutes">Minutes</option></select></div>';
    }
    if (type === 'webhook') {
        return buildSelect('webhook', webhooksData);
    }
    if (type === 'notify') {
        return '<input type="text" placeholder="Notification message" class="w-full bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1">';
    }
    return '<input type="text" placeholder="Value" class="w-full bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1">';
}

function buildSelect(name, items) {
    var html = '<select onchange="updateNodeConfig(\'' + name + '\', this.value)" class="w-full bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1 mb-2">' +
        '<option value="">Select...</option>';
    for (var i = 0; i < items.length; i++) {
        html += '<option value="' + items[i].id + '">' + items[i].name + '</option>';
    }
    html += '</select>';
    html += '<input type="text" placeholder="Or enter value" onchange="updateNodeConfig(\'' + name + '\', this.value)" class="w-full bg-slate-700 text-slate-200 border border-slate-600 rounded px-2 py-1">';
    return html;
}

function makeDraggable(el, node) {
    var isDragging = false;
    var startX, startY, nodeX, nodeY;

    el.addEventListener('mousedown', function(e) {
        if (e.target.tagName === 'SELECT' || e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') return;
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        nodeX = node.x;
        nodeY = node.y;
        el.style.zIndex = 100;
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        var dx = e.clientX - startX;
        var dy = e.clientY - startY;
        node.x = nodeX + dx;
        node.y = nodeY + dy;
        el.style.left = node.x + 'px';
        el.style.top = node.y + 'px';
        updateConnections();
    });

    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            el.style.zIndex = '';
        }
    });
}

function startConnection(nodeId) {
    if (isConnecting && connectionStart) {
        if (connectionStart !== nodeId) {
            connections.push({ from: connectionStart, to: nodeId });
            updateConnections();
        }
        isConnecting = false;
        connectionStart = null;
    } else {
        isConnecting = true;
        connectionStart = nodeId;
    }
}

function updateConnections() {
    var svg = document.getElementById('connectionsSvg');
    var existing = svg.querySelectorAll('line');
    for (var i = 0; i < existing.length; i++) {
        existing[i].parentNode.removeChild(existing[i]);
    }

    for (var j = 0; j < connections.length; j++) {
        var conn = connections[j];
        var fromNode = document.getElementById(conn.from);
        var toNode = document.getElementById(conn.to);
        if (!fromNode || !toNode) continue;

        var canvas = document.getElementById('canvas');
        var fromRect = fromNode.getBoundingClientRect();
        var toRect = toNode.getBoundingClientRect();
        var canvasRect = canvas.getBoundingClientRect();

        var x1 = fromRect.right - canvasRect.left;
        var y1 = fromRect.top + fromRect.height / 2 - canvasRect.top;
        var x2 = toRect.left - canvasRect.left;
        var y2 = toRect.top + toRect.height / 2 - canvasRect.top;

        var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x1);
        line.setAttribute('y1', y1);
        line.setAttribute('x2', x2);
        line.setAttribute('y2', y2);
        line.setAttribute('stroke', '#6366f1');
        line.setAttribute('stroke-width', '2');
        line.setAttribute('marker-end', 'url(#arrowhead)');
        svg.appendChild(line);
    }
}

function deleteNode(nodeId) {
    var newNodes = [];
    for (var i = 0; i < nodes.length; i++) {
        if (nodes[i].id !== nodeId) {
            newNodes.push(nodes[i]);
        }
    }
    nodes = newNodes;

    var newConns = [];
    for (var j = 0; j < connections.length; j++) {
        if (connections[j].from !== nodeId && connections[j].to !== nodeId) {
            newConns.push(connections[j]);
        }
    }
    connections = newConns;

    var el = document.getElementById(nodeId);
    if (el) el.remove();
    updateConnections();

    if (nodes.length === 0) {
        var container = document.getElementById('nodesContainer');
        container.innerHTML = '<div id="emptyState" class="text-center py-20">' +
            '<div class="text-slate-600 mb-4"><i class="fas fa-project-diagram text-6xl"></i></div>' +
            '<h3 class="text-lg font-medium text-slate-400">Build Your Workflow</h3>' +
            '<p class="text-slate-500 mt-2">Click items from the sidebars to add nodes</p></div>';
    }
}

function updateNodeConfig(nodeId, key, value) {
    for (var i = 0; i < nodes.length; i++) {
        if (nodes[i].id === nodeId) {
            nodes[i].config[key] = value;
            break;
        }
    }
}

function clearCanvas() {
    nodes = [];
    connections = [];
    document.getElementById('nodesContainer').innerHTML = '<div id="emptyState" class="text-center py-20">' +
        '<div class="text-slate-600 mb-4"><i class="fas fa-project-diagram text-6xl"></i></div>' +
        '<h3 class="text-lg font-medium text-slate-400">Build Your Workflow</h3>' +
        '<p class="text-slate-500 mt-2">Click items from the sidebars to add nodes</p></div>';
    updateConnections();
}

function saveWorkflow() {
    var nodeData = [];
    for (var i = 0; i < nodes.length; i++) {
        nodeData.push({
            id: nodes[i].id,
            kind: nodes[i].kind,
            type: nodes[i].type,
            label: nodes[i].label,
            x: nodes[i].x,
            y: nodes[i].y,
            config: nodes[i].config
        });
    }

    var workflow = {
        nodes: nodeData,
        connections: connections
    };

    document.getElementById('workflowData').value = JSON.stringify(workflow);
    document.getElementById('workflowForm').submit();
}

if (existingWorkflow && existingWorkflow.nodes) {
    for (var k = 0; k < existingWorkflow.nodes.length; k++) {
        var n = existingWorkflow.nodes[k];
        var node = {
            id: n.id,
            kind: n.kind,
            type: n.type,
            label: n.label,
            x: n.x || 150,
            y: n.y || 100,
            config: n.config || {}
        };
        var numId = parseInt(n.id.split('_')[1]) || 0;
        if (numId > nodeIdCounter) nodeIdCounter = numId;
        nodes.push(node);
        renderNode(node);
    }
    if (existingWorkflow.connections) {
        connections = existingWorkflow.connections;
    }
    setTimeout(updateConnections, 100);
}
</script>
@endsection