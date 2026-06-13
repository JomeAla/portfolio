@extends('layouts.admin')

@section('title', 'Create Sales Funnel')

@section('content')
<form method="POST" action="/admin/marketing/funnels">
    @csrf
    <div class="mb-6">
        <a href="/admin/marketing/funnels" class="text-blue-600 hover:text-blue-800">&larr; Back to Funnels</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Funnel Details</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Funnel Name</label>
                    <input type="text" name="name" required class="w-full border border-slate-300 rounded-lg px-4 py-2" placeholder="e.g., Product Launch Funnel">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2" placeholder="What does this funnel achieve?"></textarea>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Choose a Template</h2>
                <p class="text-sm text-slate-500 mb-4">Select a proven funnel template to get started:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Lead Magnet -->
                    <label class="funnel-template relative border-2 border-slate-200 rounded-xl p-5 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all group">
                        <input type="radio" name="funnel_type" value="lead_magnet" class="hidden radio-input">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-gift text-yellow-600 text-lg"></i>
                            </div>
                            <div class="check-icon hidden w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                        </div>
                        <h3 class="font-bold text-slate-800 mb-1">Lead Magnet</h3>
                        <p class="text-xs text-slate-500">Capture emails with a free resource</p>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <span class="text-xs text-slate-400">4 stages: Landing → Download → Welcome → Follow-up</span>
                        </div>
                    </label>
                    
                    <!-- Webinar -->
                    <label class="funnel-template relative border-2 border-slate-200 rounded-xl p-5 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all group">
                        <input type="radio" name="funnel_type" value="webinar" class="hidden radio-input">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-video text-purple-600 text-lg"></i>
                            </div>
                            <div class="check-icon hidden w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                        </div>
                        <h3 class="font-bold text-slate-800 mb-1">Webinar</h3>
                        <p class="text-xs text-slate-500">Host live or automated webinars</p>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <span class="text-xs text-slate-400">5 stages: Registration → Reminders → Live → Replay → Upsell</span>
                        </div>
                    </label>
                    
                    <!-- Product Launch -->
                    <label class="funnel-template relative border-2 border-slate-200 rounded-xl p-5 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all group">
                        <input type="radio" name="funnel_type" value="product_launch" class="hidden radio-input">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-rocket text-red-600 text-lg"></i>
                            </div>
                            <div class="check-icon hidden w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                        </div>
                        <h3 class="font-bold text-slate-800 mb-1">Product Launch</h3>
                        <p class="text-xs text-slate-500">Launch products with maximum impact</p>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <span class="text-xs text-slate-400">5 stages: Teaser → Preview → Launch → Cart → Thank You</span>
                        </div>
                    </label>
                    
                    <!-- Tripwire -->
                    <label class="funnel-template relative border-2 border-slate-200 rounded-xl p-5 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all group">
                        <input type="radio" name="funnel_type" value="tripwire" class="hidden radio-input">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-bolt text-green-600 text-lg"></i>
                            </div>
                            <div class="check-icon hidden w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                        </div>
                        <h3 class="font-bold text-slate-800 mb-1">Tripwire</h3>
                        <p class="text-xs text-slate-500">Low-ticket offer then upsell</p>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <span class="text-xs text-slate-400">4 stages: Landing → $9 Offer → Upsell → Thank You</span>
                        </div>
                    </label>

                    <!-- Free + Shipping -->
                    <label class="funnel-template relative border-2 border-slate-200 rounded-xl p-5 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all group">
                        <input type="radio" name="funnel_type" value="free_shipping" class="hidden radio-input">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-truck text-blue-600 text-lg"></i>
                            </div>
                            <div class="check-icon hidden w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                        </div>
                        <h3 class="font-bold text-slate-800 mb-1">Free + Shipping</h3>
                        <p class="text-xs text-slate-500">Free product, paid shipping</p>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <span class="text-xs text-slate-400">4 stages: Landing → checkout → Shipping → Upsell</span>
                        </div>
                    </label>

                    <!-- VSL Sales -->
                    <label class="funnel-template relative border-2 border-slate-200 rounded-xl p-5 cursor-pointer hover:border-blue-500 hover-bg-blue-50 transition-all group">
                        <input type="radio" name="funnel_type" value="vsl_sales" class="hidden radio-input">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-play text-pink-600 text-lg"></i>
                            </div>
                            <div class="check-icon hidden w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                        </div>
                        <h3 class="font-bold text-slate-800 mb-1">VSL Sales Page</h3>
                        <p class="text-xs text-slate-500">Video sales letter funnel</p>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <span class="text-xs text-slate-400">3 stages: Video → Cart → Thank You</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Stage Preview -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-700 rounded-xl p-6 text-white hidden" id="stagePreview">
                <h3 class="font-bold text-lg mb-4">Preview: <span id="templateName"></span></h3>
                <div class="flex items-center gap-2 overflow-x-auto pb-2" id="stagesFlow">
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Settings</h2>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" class="rounded border-slate-300 text-blue-600">
                        <span class="ml-2 text-sm text-slate-700">Activate immediately</span>
                    </label>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6">
                <h3 class="font-bold text-blue-800 mb-3">Need Help?</h3>
                <p class="text-sm text-blue-700 mb-3">Each funnel type is optimized for specific goals. Choose based on your offer.</p>
                <ul class="text-xs text-blue-600 space-y-1">
                    <li><i class="fas fa-check mr-1"></i><strong>Lead Magnet:</strong> Build email list</li>
                    <li><i class="fas fa-check mr-1"></i><strong>Webinar:</strong> Sell courses/coaching</li>
                    <li><i class="fas fa-check mr-1"></i><strong>Product Launch:</strong> New product release</li>
                    <li><i class="fas fa-check mr-1"></i><strong>Tripwire:</strong> Quick sales + upsells</li>
                    <li><i class="fas fa-check mr-1"></i><strong>VSL:</strong> High-ticket offers</li>
                </ul>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-medium text-lg shadow-lg">
                Create Funnel
            </button>
        </div>
    </div>
</form>

<script>
const templates = {
    lead_magnet: {
        name: 'Lead Magnet Funnel',
        stages: [
            { name: 'Landing Page', type: 'landing' },
            { name: 'Free Download', type: 'landing' },
            { name: 'Welcome Sequence', type: 'email' },
            { name: 'Follow-up', type: 'email' }
        ]
    },
    webinar: {
        name: 'Webinar Funnel',
        stages: [
            { name: 'Registration Page', type: 'landing' },
            { name: 'Reminder 1', type: 'email' },
            { name: 'Reminder 2', type: 'email' },
            { name: 'Live/Webinar', type: 'sales_page' },
            { name: 'Replay + Offer', type: 'sales_page' }
        ]
    },
    product_launch: {
        name: 'Product Launch',
        stages: [
            { name: 'Teaser', type: 'landing' },
            { name: 'Preview', type: 'landing' },
            { name: 'Launch', type: 'sales_page' },
            { name: 'Cart', type: 'checkout' },
            { name: 'Thank You', type: 'thank_you' }
        ]
    },
    tripwire: {
        name: 'Tripwire Funnel',
        stages: [
            { name: 'Landing Page', type: 'landing' },
            { name: '$9 Offer', type: 'checkout' },
            { name: 'Upsell', type: 'upsell' },
            { name: 'Thank You', type: 'thank_you' }
        ]
    },
    free_shipping: {
        name: 'Free + Shipping',
        stages: [
            { name: 'Landing Page', type: 'landing' },
            { name: 'Checkout', type: 'checkout' },
            { name: 'Shipping Info', type: 'email' },
            { name: 'Upsell', type: 'upsell' }
        ]
    },
    vsl_sales: {
        name: 'VSL Sales Page',
        stages: [
            { name: 'Video Sales Letter', type: 'sales_page' },
            { name: 'Checkout', type: 'checkout' },
            { name: 'Thank You', type: 'thank_you' }
        ]
    }
};

const typeLabels = {
    landing: { icon: 'fa-laptop-code', color: 'bg-blue-100 text-blue-600' },
    email: { icon: 'fa-envelope', color: 'bg-purple-100 text-purple-600' },
    sales_page: { icon: 'fa-shopping-cart', color: 'bg-green-100 text-green-600' },
    checkout: { icon: 'fa-credit-card', color: 'bg-yellow-100 text-yellow-600' },
    upsell: { icon: 'fa-plus-circle', color: 'bg-pink-100 text-pink-600' },
    thank_you: { icon: 'fa-check-circle', color: 'bg-green-100 text-green-600' }
};

document.querySelectorAll('.funnel-template').forEach(label => {
    label.addEventListener('click', () => {
        // Clear all selections
        document.querySelectorAll('.funnel-template').forEach(l => {
            l.classList.remove('border-blue-500', 'bg-blue-50');
            l.querySelector('.check-icon')?.classList.add('hidden');
        });
        
        // Select this one
        label.classList.add('border-blue-500', 'bg-blue-50');
        label.querySelector('.check-icon')?.classList.remove('hidden');
        
        // Show preview
        const type = label.querySelector('input').value;
        const template = templates[type];
        
        if (template) {
            document.getElementById('templateName').textContent = template.name;
            
            const flow = document.getElementById('stagesFlow');
            flow.innerHTML = template.stages.map((stage, i) => {
                const t = typeLabels[stage.type] || typeLabels.landing;
                return `
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 ${t.color} rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas ${t.icon}"></i>
                        </div>
                        <div class="text-sm text-white whitespace-nowrap">${stage.name}</div>
                        ${i < template.stages.length - 1 ? '<i class="fas fa-arrow-right text-slate-500"></i>' : ''}
                    </div>
                `;
            }).join('');
            
            document.getElementById('stagePreview').classList.remove('hidden');
        }
    });
});
</script>
@endsection