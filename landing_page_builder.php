<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page Builder - Joala Ventures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2563eb; --primary-dark: #1d4ed8; }
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .sidebar { width: 280px; background: #1e293b; color: white; height: 100vh; overflow-y: auto; padding: 20px; box-sizing: border-box; position: fixed; left: 0; top: 0; box-shadow: 2px 0 10px rgba(0,0,0,0.1); z-index: 100; }
        .sidebar h2 { margin: 0 0 20px 0; font-size: 20px; }
        .sidebar h3 { margin: 20px 0 10px 0; font-size: 12px; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; }
        .comp-btn { display: block; width: 100%; padding: 12px 15px; margin-bottom: 8px; border: none; border-radius: 8px; background: #334155; color: #e2e8f0; cursor: pointer; text-align: left; box-sizing: border-box; font-size: 14px; transition: all 0.2s; }
        .comp-btn:hover { background: var(--primary); transform: translateX(3px); }
        .main { margin-left: 280px; height: 100vh; overflow-y: auto; padding: 30px; box-sizing: border-box; background: #f1f5f9; }
        .canvas { background: white; min-height: calc(100vh - 100px); padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .canvas-el { position: relative; margin: 15px 0; border: 2px dashed transparent; padding: 10px; transition: border 0.2s; }
        .canvas-el:hover { border-color: var(--primary); }
        .del-btn { position: absolute; top: 10px; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; opacity: 0; transition: opacity 0.2s; }
        .del-btn.delete { right: 10px; background: #ef4444; color: white; }
        .del-btn.edit { left: 10px; right: auto; background: #2563eb; color: white; }
        .canvas-el:hover .del-btn { opacity: 1; }
        .empty-state { text-align: center; color: #94a3b8; padding: 80px 40px; border: 2px dashed #e2e8f0; border-radius: 12px; }
        .empty-state i { font-size: 64px; margin-bottom: 20px; }
        
        /* Form Styles */
        .lead-form { max-width: 450px; margin: 0 auto; }
        .lead-form input { width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 16px; margin-bottom: 12px; box-sizing: border-box; transition: border 0.2s; }
        .lead-form input:focus { outline: none; border-color: var(--primary); }
        .lead-form button { width: 100%; padding: 16px; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        .lead-form button:hover { background: var(--primary-dark); }
        
        /* Hero Section Styles */
        .hero-section { padding: 100px 40px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .hero-section h1 { font-size: 56px; font-weight: 800; color: white; margin: 0 0 24px; line-height: 1.1; }
        .hero-section p { font-size: 22px; color: rgba(255,255,255,0.85); margin: 0 0 36px; line-height: 1.6; }
        .hero-section .btn { display: inline-block; padding: 18px 40px; background: white; color: #667eea; font-size: 18px; font-weight: 700; border-radius: 8px; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; }
        .hero-section .btn:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        
        /* Features Section */
        .features-section { padding: 100px 40px; background: white; }
        .features-section h2 { font-size: 42px; font-weight: 800; text-align: center; margin: 0 0 60px; color: #1e293b; }
        .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; }
        .feature-card { text-align: center; padding: 40px 30px; border-radius: 16px; }
        .feature-card i { font-size: 48px; margin-bottom: 24px; }
        .feature-card h3 { font-size: 22px; font-weight: 700; margin: 0 0 16px; color: #1e293b; }
        .feature-card p { font-size: 16px; color: #64748b; line-height: 1.6; margin: 0; }
        
        /* CTA Section */
        .cta-section { padding: 80px 40px; background: #1e293b; text-align: center; }
        .cta-section h2 { font-size: 42px; font-weight: 800; color: white; margin: 0 0 20px; }
        .cta-section p { font-size: 20px; color: rgba(255,255,255,0.7); margin: 0 0 36px; }
        .cta-section .btn { display: inline-block; padding: 18px 48px; background: #22c55e; color: white; font-size: 18px; font-weight: 700; border-radius: 8px; text-decoration: none; }
        
        /* Social Proof */
        .social-proof { padding: 60px 40px; background: #f8fafc; text-align: center; }
        .social-proof p { font-size: 18px; color: #64748b; margin: 0 0 24px; }
        .avatar-stack { display: flex; justify-content: center; margin-bottom: 16px; }
        .avatar-stack img { width: 50px; height: 50px; border-radius: 50%; border: 3px solid white; margin-left: -15px; }
        .avatar-stack .count { width: 50px; height: 50px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; margin-left: -15px; border: 3px solid white; }
        
        /* Stats Section */
        .stats-section { padding: 80px 40px; background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%); }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; text-align: center; }
        .stat-item { color: white; }
        .stat-item .number { font-size: 56px; font-weight: 800; }
        .stat-item .label { font-size: 18px; opacity: 0.85; }
        
        /* Footer */
        .footer-section { padding: 40px; background: #0f172a; text-align: center; color: #64748b; }
        
        /* Preview Modal */
        #previewModal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.9); z-index: 9999; }
        #previewModal .modal-content { background: white; margin: 40px auto; max-width: 100%; width: 100%; max-height: 90vh; overflow: auto; border-radius: 12px; }
        #previewModal .modal-header { background: #1e293b; color: white; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; }
        #previewModal .close-btn { cursor: pointer; font-size: 24px; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2><i class="fas fa-magic"></i> Page Builder</h2>
    
    <h3>Basic Sections</h3>
    <button class="comp-btn" onclick="addComponent('hero')"><i class="fas fa-heading"></i> Hero Section</button>
    <button class="comp-btn" onclick="addComponent('features')"><i class="fas fa-th-large"></i> Features Grid</button>
    <button class="comp-btn" onclick="addComponent('cta')"><i class="fas fa-bullseye"></i> Call to Action</button>
    <button class="comp-btn" onclick="addComponent('lead-form')"><i class="fas fa-form"></i> Lead Capture Form</button>
    <button class="comp-btn" onclick="addComponent('testimonials')"><i class="fas fa-quote-left"></i> Testimonials</button>
    <button class="comp-btn" onclick="addComponent('footer')"><i class="fas fa-footer"></i> Footer</button>
    
    <h3>Social Proof</h3>
    <button class="comp-btn" onclick="addComponent('social-proof')"><i class="fas fa-users"></i> Social Proof</button>
    <button class="comp-btn" onclick="addComponent('logos')"><i class="fas fa-building"></i> Client Logos</button>
    <button class="comp-btn" onclick="addComponent('stats')"><i class="fas fa-chart-bar"></i> Stats Counter</button>
    
    <h3>Interactive</h3>
    <button class="comp-btn" onclick="addComponent('video')"><i class="fas fa-play-circle"></i> Video Section</button>
    <button class="comp-btn" onclick="addComponent('faq')"><i class="fas fa-question-circle"></i> FAQ Accordion</button>
    
    <h3>Templates</h3>
    <button class="comp-btn" onclick="loadTemplate('lead-capture')"><i class="fas fa-hand-pointer"></i> Lead Capture</button>
    <button class="comp-btn" onclick="loadTemplate('webinar')"><i class="fas fa-video"></i> Webinar</button>
    
    <h3>Settings</h3>
    <div style="margin-bottom: 15px;">
        <label style="display:block; font-size:12px; color:#94a3b8; margin-bottom:5px;">Accent Color</label>
        <input type="color" id="accentColor" value="#2563eb" style="width:100%; height:40px; border:none; border-radius:6px; cursor:pointer;">
    </div>
    <div style="margin-top: 30px; display:flex; gap:10px;">
        <button onclick="previewPage()" style="flex:1; padding:12px; background:#475569; color:white; border:none; border-radius:6px; cursor:pointer;">Preview</button>
        <button onclick="savePage()" style="flex:1; padding:12px; background:var(--primary); color:white; border:none; border-radius:6px; cursor:pointer;">Save</button>
    </div>
</div>

<!-- Main Canvas -->
<div class="main">
    <div class="canvas">
        <div id="emptyState" class="empty-state">
            <i class="fas fa-plus-circle"></i>
            <h2 style="color:#475569; margin:0 0 10px;">Start Building Your Page</h2>
            <p style="color:#94a3b8; margin:0;">Click any component above to add it to your page</p>
        </div>
        <div id="pageElements"></div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal">
    <div class="modal-content">
        <div class="modal-header">
            <span><i class="fas fa-eye"></i> Preview</span>
            <span class="close-btn" onclick="document.getElementById('previewModal').style.display='none'">&times;</span>
        </div>
        <div id="previewContent"></div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.8); z-index:10000; overflow:auto;">
    <div style="background:white; margin:40px auto; max-width:900px; border-radius:12px; overflow:hidden;">
        <div style="background:#1e293b; color:white; padding:15px 25px; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:18px; font-weight:600;"><i class="fas fa-edit"></i> Edit Component HTML</span>
            <span onclick="closeEditModal()" style="cursor:pointer; font-size:24px;">&times;</span>
        </div>
        <div style="padding:25px;">
            <p style="margin:0 0 15px; color:#64748b;">Edit the HTML below. Changes will be reflected on your page.</p>
            <textarea id="editContent" style="width:100%; height:400px; padding:15px; border:2px solid #e2e8f0; border-radius:8px; font-family:monospace; font-size:14px; line-height:1.5; box-sizing:border-box; resize:vertical;"></textarea>
        </div>
        <div style="padding:15px 25px; background:#f8fafc; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #e2e8f0;">
            <button onclick="closeEditModal()" style="padding:12px 25px; border:2px solid #e2e8f0; background:white; border-radius:8px; cursor:pointer;">Cancel</button>
            <button onclick="saveEdit()" style="padding:12px 25px; background:var(--primary); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Save Changes</button>
        </div>
    </div>
</div>

<script>
var components = {
    'hero': {
        template: '<section class="hero-section" id="hero-{id}">' +
            '<h1>Build Your Dream Business Online</h1>' +
            '<p>Transform your ideas into a thriving online business with our proven system. Join thousands of entrepreneurs who have already taken the first step.</p>' +
            '<a href="#" class="btn">Get Started Free <i class="fas fa-arrow-right" style="margin-left:8px;"></i></a>' +
        '</section>'
    },
    'features': {
        template: '<section class="features-section" id="features-{id}">' +
            '<h2>Why Choose Us</h2>' +
            '<div class="features-grid">' +
                '<div class="feature-card">' +
                    '<i class="fas fa-rocket" style="color:#667eea;"></i>' +
                    '<h3>Fast Results</h3>' +
                    '<p>See results within days, not months. Our system is designed for quick implementation.</p>' +
                '</div>' +
                '<div class="feature-card">' +
                    '<i class="fas fa-shield-alt" style="color:#667eea;"></i>' +
                    '<h3>Secure & Safe</h3>' +
                    '<p>Enterprise-grade security to keep your data safe and protected always.</p>' +
                '</div>' +
                '<div class="feature-card">' +
                    '<i class="fas fa-headset" style="color:#667eea;"></i>' +
                    '<h3>24/7 Support</h3>' +
                    '<p>Round-the-clock support team ready to help you succeed.</p>' +
                '</div>' +
            '</div>' +
        '</section>'
    },
    'cta': {
        template: '<section class="cta-section" id="cta-{id}">' +
            '<h2>Ready to Get Started?</h2>' +
            '<p>Join over 10,000+ happy customers today.</p>' +
            '<a href="#" class="btn">Yes! I Want to Start <i class="fas fa-arrow-right" style="margin-left:8px;"></i></a>' +
        '</section>'
    },
    'lead-form': {
        template: '<section style="padding:100px 40px; background:linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);" id="leadform-{id}">' +
            '<div class="lead-form">' +
                '<h2 style="text-align:center; margin:0 0 10px; font-size:36px; color:#1e293b;">Get Your Free Report</h2>' +
                '<p style="text-align:center; margin:0 0 30px; color:#64748b;">Enter your details below to unlock your free resources.</p>' +
                '<form onsubmit="handleFormSubmit(event)">' +
                    '<input type="text" name="name" placeholder="Full Name" required>' +
                    '<input type="email" name="email" placeholder="Email Address" required>' +
                    '<input type="tel" name="phone" placeholder="Phone Number (Optional)">' +
                    '<button type="submit">Get My Free Report <i class="fas fa-arrow-right" style="margin-left:8px;"></i></button>' +
                    '<p style="text-align:center; margin:15px 0 0; font-size:13px; color:#94a3b8;"><i class="fas fa-lock"></i> Your information is 100% secure. We hate spam too.</p>' +
                '</form>' +
            '</div>' +
        '</section>'
    },
    'testimonials': {
        template: '<section style="padding:100px 40px; background:white;" id="testimonials-{id}">' +
            '<h2 style="text-align:center; font-size:42px; font-weight:800; margin:0 0 50px;">What Our Clients Say</h2>' +
            '<div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:30px;">' +
                '<div style="background:#f8fafc; padding:40px; border-radius:16px;">' +
                    '<div style="color:#f59e0b; font-size:18px; margin:0 0 20px;">★★★★★</div>' +
                    '<p style="font-size:18px; color:#475569; line-height:1.7; margin:0 0 25px;">"This completely transformed our business. Within 3 months we saw 300% growth. The system just works."</p>' +
                    '<div style="display:flex; align-items:center; gap:15px;">' +
                        '<img src="https://i.pravatar.cc/60?img=32" style="width:50px; height:50px; border-radius:50%;">' +
                        '<div><div style="font-weight:700; color:#1e293b;">Sarah Johnson</div><div style="color:#64748b; font-size:14px;">CEO, TechCorp</div></div>' +
                    '</div>' +
                '</div>' +
                '<div style="background:#f8fafc; padding:40px; border-radius:16px;">' +
                    '<div style="color:#f59e0b; font-size:18px; margin:0 0 20px;">★★★★★</div>' +
                    '<p style="font-size:18px; color:#475569; line-height:1.7; margin:0 0 25px;">"Best investment we have made. The results speak for themselves.Highly recommend to anyone serious about growth."</p>' +
                    '<div style="display:flex; align-items:center; gap:15px;">' +
                        '<img src="https://i.pravatar.cc/60?img=12" style="width:50px; height:50px; border-radius:50%;">' +
                        '<div><div style="font-weight:700; color:#1e293b;">Michael Chen</div><div style="color:#64748b; font-size:14px;">Founder, StartupX</div></div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</section>'
    },
    'social-proof': {
        template: '<section class="social-proof" id="social-{id}">' +
            '<div class="avatar-stack">' +
                '<img src="https://i.pravatar.cc/50?img=1">' +
                '<img src="https://i.pravatar.cc/50?img=2">' +
                '<img src="https://i.pravatar.cc/50?img=3">' +
                '<img src="https://i.pravatar.cc/50?img=4">' +
                '<img src="https://i.pravatar.cc/50?img=5">' +
                '<div class="count">+10k</div>' +
            '</div>' +
            '<p style="font-size:22px; font-weight:700; margin:0;">Join 10,000+ happy customers worldwide</p>' +
            '<p style="font-size:16px; color:#94a3b8; margin:10px 0 0;">4.9/5 rating from 2,500+ reviews</p>' +
        '</section>'
    },
    'logos': {
        template: '<section style="padding:50px 40px; background:white; text-align:center;" id="logos-{id}">' +
            '<p style="color:#94a3b8; font-size:14px; letter-spacing:2px; text-transform:uppercase; margin:0 0 30px;">TRUSTED BY 500+ COMPANIES</p>' +
            '<div style="display:flex; justify-content:center; gap:50px; flex-wrap:wrap; opacity:0.6;">' +
                '<span style="font-size:24px; font-weight:800; color:#94a3b8;">COMPANY</span>' +
                '<span style="font-size:24px; font-weight:800; color:#94a3b8;">BRAND</span>' +
                '<span style="font-size:24px; font-weight:800; color:#94a3b8;">BUSINESS</span>' +
                '<span style="font-size:24px; font-weight:800; color:#94a3b8;">COMPANY</span>' +
            '</div>' +
        '</section>'
    },
    'stats': {
        template: '<section class="stats-section" id="stats-{id}">' +
            '<div class="stats-grid">' +
                '<div class="stat-item"><div class="number">500+</div><div class="label">Clients Worldwide</div></div>' +
                '<div class="stat-item"><div class="number">10+</div><div class="label">Years Experience</div></div>' +
                '<div class="stat-item"><div class="number">98%</div><div class="label">Satisfaction</div></div>' +
                '<div class="stat-item"><div class="number">24/7</div><div class="label">Support</div></div>' +
            '</div>' +
        '</section>'
    },
    'video': {
        template: '<section style="padding:100px 40px; background:#0f172a; text-align:center;" id="video-{id}">' +
            '<h2 style="color:white; font-size:42px; margin:0 0 10px;">Watch How It Works</h2>' +
            '<p style="color:#94a3b8; font-size:18px; margin:0 0 40px;">See why thousands choose our solution</p>' +
            '<div style="background:#1e293b; border-radius:16px; height:400px; display:flex; align-items:center; justify-content:center; cursor:pointer; max-width:800px; margin:0 auto;">' +
                '<div style="text-align:center; color:white;">' +
                    '<i class="fas fa-play-circle" style="font-size:80px; color:#667eea; margin-bottom:20px;"></i>' +
                    '<p style="margin:0;">Click to play video</p>' +
                '</div>' +
            '</div>' +
        '</section>'
    },
    'faq': {
        template: '<section style="padding:100px 40px; background:#f8fafc;" id="faq-{id}">' +
            '<h2 style="text-align:center; font-size:42px; font-weight:800; margin:0 0 50px;">Frequently Asked Questions</h2>' +
            '<div style="max-width:700px; margin:0 auto;">' +
                '<div style="background:white; border-radius:12px; margin:0 0 15px; overflow:hidden;">' +
                    '<div style="padding:20px 25px; background:#f1f5f9; font-weight:600; cursor:pointer; display:flex; justify-content:space-between;">' +
                        '<span>How long does it take to get started?</span><i class="fas fa-chevron-down"></i>' +
                    '</div>' +
                    '<div style="padding:20px 25px; color:#64748b;">Most customers are up and running within 24 hours. We provide full setup support at no extra cost.</div>' +
                '</div>' +
                '<div style="background:white; border-radius:12px; margin:0 0 15px; overflow:hidden;">' +
                    '<div style="padding:20px 25px; background:#f1f5f9; font-weight:600; cursor:pointer; display:flex; justify-content:space-between;">' +
                        '<span>Is there a free trial?</span><i class="fas fa-chevron-down"></i>' +
                    '</div>' +
                    '<div style="padding:20px 25px; color:#64748b;">Yes! We offer a 14-day free trial with full access to all features. No credit card required.</div>' +
                '</div>' +
                '<div style="background:white; border-radius:12px; overflow:hidden;">' +
                    '<div style="padding:20px 25px; background:#f1f5f9; font-weight:600; cursor:pointer; display:flex; justify-content:space-between;">' +
                        '<span>Can I cancel anytime?</span><i class="fas fa-chevron-down"></i>' +
                    '</div>' +
                    '<div style="padding:20px 25px; color:#64748b;">Absolutely. No long-term contracts. Cancel anytime with no penalties.</div>' +
                '</div>' +
            '</div>' +
        '</section>'
    },
    'footer': {
        template: '<section class="footer-section" id="footer-{id}">' +
            '<p style="margin:0;">&copy; 2026 Your Company. All rights reserved.</p>' +
            '<div style="margin-top:10px;">' +
                '<a href="#" style="color:#64748b; margin:0 15px; text-decoration:none;">Privacy Policy</a>' +
                '<a href="#" style="color:#64748b; margin:0 15px; text-decoration:none;">Terms of Service</a>' +
                '<a href="#" style="color:#64748b; margin:0 15px; text-decoration:none;">Contact Us</a>' +
            '</div>' +
        '</section>'
    }
};

var templates = {
    'lead-capture': ['hero', 'social-proof', 'lead-form', 'cta'],
    'webinar': ['hero', 'video', 'features', 'testimonials', 'cta']
};

function handleFormSubmit(e) {
    e.preventDefault();
    alert('Thank you! Your submission has been received. In a real implementation, this would send data to your database/CRM.');
}

var pageElements = [];
var componentCounter = 0;

function addComponent(type) {
    var target = document.getElementById('pageElements');
    if (!target) return;
    if (!components[type]) { alert('Component not found: ' + type); return; }
    
    document.getElementById('emptyState').style.display = 'none';
    componentCounter++;
    var id = componentCounter;
    var color = document.getElementById('accentColor').value.replace('#', '');
    var html = components[type].template.replace(/{id}/g, 'comp-' + id).replace(/#667eea/g, '#' + color);
    html += '<span class="del-btn edit" onclick="editComponent(this)"><i class="fas fa-edit"></i> Edit</span>';
    html += '<span class="del-btn delete" onclick="this.parentElement.remove(); checkEmpty();"><i class="fas fa-times"></i> Delete</span>';
    
    var wrapper = document.createElement('div');
    wrapper.className = 'canvas-el';
    wrapper.innerHTML = html;
    
    target.appendChild(wrapper);
    pageElements.push(type);
}

function loadTemplate(name) {
    var els = templates[name];
    if (!els) return;
    if (confirm('This will replace your current page. Continue?')) {
        document.getElementById('pageElements').innerHTML = '';
        pageElements = [];
        els.forEach(function(type) { addComponent(type); });
    }
}

function checkEmpty() {
    var target = document.getElementById('pageElements');
    if (target && target.children.length === 0) {
        document.getElementById('emptyState').style.display = 'block';
    }
}

var currentEditWrapper = null;

function editComponent(btn) {
    var wrapper = btn.parentElement;
    currentEditWrapper = wrapper;
    var innerHTML = wrapper.innerHTML;
    // Remove the buttons to get the actual content
    var content = innerHTML.replace(/<span class="del-btn[^>]*>[^<]*<\/span>/g, '');
    document.getElementById('editContent').value = content.trim();
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    currentEditWrapper = null;
}

function saveEdit() {
    if (currentEditWrapper) {
        var newContent = document.getElementById('editContent').value;
        var deleteBtn = '<span class="del-btn edit" onclick="editComponent(this)"><i class="fas fa-edit"></i> Edit</span>';
        var deleteBtn2 = '<span class="del-btn delete" onclick="this.parentElement.remove(); checkEmpty();"><i class="fas fa-times"></i> Delete</span>';
        currentEditWrapper.innerHTML = newContent + deleteBtn + deleteBtn2;
    }
    closeEditModal();
}

function previewPage() {
    var color = document.getElementById('accentColor').value.replace('#', '');
    var html = '';
    document.getElementById('pageElements').querySelectorAll('.canvas-el').forEach(function(el) {
        var content = el.innerHTML.replace(/<span class="del-btn"[^>]*>[^<]*<\/span>/g, '');
        html += content;
    });
    document.getElementById('previewContent').innerHTML = html;
    document.getElementById('previewModal').style.display = 'block';
}

function savePage() {
    var color = document.getElementById('accentColor').value.replace('#', '');
    var html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Landing Page</title><script src="https://cdn.tailwindcss.com"></' + 'script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head><body>';
    
    document.getElementById('pageElements').querySelectorAll('.canvas-el').forEach(function(el) {
        var content = el.innerHTML.replace(/<span class="del-btn"[^>]*>[^<]*<\/span>/g, '');
        html += content;
    });
    
    html += '</body></html>';
    var blob = new Blob([html], { type: 'text/html' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'landing-page.html';
    a.click();
    alert('Page saved as landing-page.html!');
}
</script>

</body>
</html>