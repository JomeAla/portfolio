<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template Builder - Joala Ventures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .editor-toolbar button { @apply p-2 hover:bg-slate-100 rounded; }
        .editor-toolbar button.active { @apply bg-indigo-100 text-indigo-600; }
        #emailPreview iframe { @apply w-full h-full border-none; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">
<nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4">
    <div class="flex items-center justify-between max-w-7xl mx-auto">
        <div class="flex items-center gap-4">
            <a href="/admin/marketing" class="text-slate-600 hover:text-slate-800"><i class="fas fa-arrow-left"></i></a>
            <h1 class="text-xl font-bold text-slate-800">Email Template Builder</h1>
        </div>
        <div class="flex gap-3">
            <button onclick="saveTemplate()" class="bg-slate-600 text-white px-4 py-2 rounded-lg hover:bg-slate-700">
                <i class="fas fa-save mr-2"></i>Save Template
            </button>
            <button onclick="previewTemplate()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-eye mr-2"></i>Preview
            </button>
        </div>
    </div>
</nav>

<div class="flex max-w-7xl mx-auto mt-6 gap-6 px-6">
    <!-- Left: Editor -->
    <div class="w-1/2">
        <div class="bg-white rounded-lg shadow">
            <div class="border-b border-slate-200 p-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Template Name</label>
                        <input type="text" id="templateName" class="w-full border border-slate-300 rounded-lg px-3 py-2" placeholder="Welcome Email">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Subject Line</label>
                        <input type="text" id="templateSubject" class="w-full border border-slate-300 rounded-lg px-3 py-2" placeholder="Welcome to {{name}}">
                    </div>
                </div>
            </div>
            
            <!-- Toolbar -->
            <div class="border-b border-slate-200 p-2 flex gap-1 overflow-x-auto">
                <button onclick="formatDoc('bold')" title="Bold" class="editor-toolbar"><i class="fas fa-bold"></i></button>
                <button onclick="formatDoc('italic')" title="Italic" class="editor-toolbar"><i class="fas fa-italic"></i></button>
                <button onclick="formatDoc('underline')" title="Underline" class="editor-toolbar"><i class="fas fa-underline"></i></button>
                <span class="w-px h-6 bg-slate-300 mx-1"></span>
                <button onclick="formatDoc('justifyLeft')" title="Left Align" class="editor-toolbar"><i class="fas fa-align-left"></i></button>
                <button onclick="formatDoc('justifyCenter')" title="Center Align" class="editor-toolbar"><i class="fas fa-align-center"></i></button>
                <button onclick="formatDoc('justifyRight')" title="Right Align" class="editor-toolbar"><i class="fas fa-align-right"></i></button>
                <span class="w-px h-6 bg-slate-300 mx-1"></span>
                <button onclick="formatDoc('insertUnorderedList')" title="Bullet List" class="editor-toolbar"><i class="fas fa-list-ul"></i></button>
                <button onclick="formatDoc('insertOrderedList')" title="Numbered List" class="editor-toolbar"><i class="fas fa-list-ol"></i></button>
                <span class="w-px h-6 bg-slate-300 mx-1"></span>
                <button onclick="insertLink()" title="Insert Link" class="editor-toolbar"><i class="fas fa-link"></i></button>
                <button onclick="insertImage()" title="Insert Image" class="editor-toolbar"><i class="fas fa-image"></i></button>
                <button onclick="insertVariable('{{name}}')" title="Insert Name Variable" class="editor-toolbar text-xs">[[NAME]]</button>
                <button onclick="insertVariable('{{unsubscribe_url}}')" title="Insert Unsubscribe Link" class="editor-toolbar text-xs">[[UNSUB]]</button>
            </div>
            
            <!-- Editor Area -->
            <div id="editor" contenteditable="true" class="p-4 min-h-[400px] outline-none prose max-w-none" style="min-height: 400px;">
                <div style="font-family: -apple-system, BlinkMacSystemFont, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #2563eb;">Hi [[NAME]],</h2>
                    <p>Welcome to Joala Ventures! We're excited to have you on board.</p>
                    <p>Here is what you can expect:</p>
                    <ul>
                        <li>Regular updates and insights</li>
                        <li>Exclusive content</li>
                        <li>Special offers</li>
                    </ul>
                    <p>Best regards,<br>The Joala Team</p>
                    <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
                    <p style="font-size: 12px; color: #666;">[[UNSUB]]</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right: Preview -->
    <div class="w-1/2">
        <div class="bg-white rounded-lg shadow sticky top-6">
            <div class="border-b border-slate-200 p-4">
                <h2 class="font-bold text-slate-800">Live Preview</h2>
            </div>
            <div id="emailPreview" class="p-4 bg-slate-50 min-h-[500px]">
            </div>
        </div>
    </div>
</div>

<!-- Save Modal -->
<div id="saveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Save Template</h3>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
            <textarea id="templateDescription" class="w-full border border-slate-300 rounded-lg px-3 py-2" rows="3" placeholder="Brief description of this template..."></textarea>
        </div>
        <div class="mb-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" id="templateActive" checked class="rounded">
                <span class="text-sm text-slate-700">Set as Active</span>
            </label>
        </div>
        <div class="flex gap-3">
            <button onclick="saveToDatabase()" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Save</button>
            <button onclick="closeModal()" class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</button>
        </div>
    </div>
</div>

<script>
let templateId = null;

function formatDoc(cmd) {
    document.execCommand(cmd, false, null);
    document.getElementById('editor').focus();
}

function insertLink() {
    const url = prompt('Enter URL:');
    if (url) {
        document.execCommand('createLink', false, url);
    }
}

function insertImage() {
    const url = prompt('Enter image URL:');
    if (url) {
        document.execCommand('insertImage', false, url);
    }
}

function insertVariable(varName) {
    const editor = document.getElementById('editor');
    editor.focus();
    document.execCommand('insertText', false, varName);
}

function previewTemplate() {
    const editor = document.getElementById('editor');
    const subject = document.getElementById('templateSubject').value || 'Subject Line';
    const html = editor.innerHTML;
    
    const previewHtml = `
        <div style="font-family: -apple-system, BlinkMacSystemFont, sans-serif; max-width: 100%; background: white; padding: 20px;">
            <div style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
                <strong>Subject:</strong> ${subject.replace(/{{name}}/g, '<span style="color:#2563eb;">John Doe</span>')}
            </div>
            <div>${html.replace(/\[\[NAME\]\]/g, '<strong style="color:#2563eb;">John Doe</strong>').replace(/\[\[UNSUB\]\]/g, '<a href="#" style="color:#666;font-size:12px;">Unsubscribe</a>')}</div>
        </div>
    `;
    document.getElementById('emailPreview').innerHTML = previewHtml;
}

function saveTemplate() {
    document.getElementById('saveModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('saveModal').classList.add('hidden');
}

function saveToDatabase() {
    const name = document.getElementById('templateName').value;
    const subject = document.getElementById('templateSubject').value;
    const body = document.getElementById('editor').innerHTML;
    const description = document.getElementById('templateDescription').value;
    const is_active = document.getElementById('templateActive').checked ? 1 : 0;
    
    if (!name) {
        alert('Please enter a template name');
        return;
    }
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('subject', subject);
    formData.append('body', body);
    formData.append('description', description);
    formData.append('is_active', is_active);
    
    fetch('/email_templates.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text())
    .then(data => {
        alert('Template saved successfully!');
        closeModal();
        window.location.href = '/email_templates.php';
    })
    .catch(err => {
        alert('Error saving template');
    });
}

// Auto-preview on input
document.getElementById('templateSubject').addEventListener('input', previewTemplate);
document.getElementById('editor').addEventListener('input', previewTemplate);

// Initial preview
setTimeout(previewTemplate, 500);
</script>
</body>
</html>