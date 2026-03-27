@extends('layouts.app')

@section('title', 'Project Brief')

@section('content')
<section class="py-20 bg-slate-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-slate-900">Start Your Project</h1>
            <p class="text-lg text-slate-600 mt-4">Tell me about your project and I'll get back to you within 24 hours.</p>
        </div>
        
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-xl mb-8">
            {{ session('success') }}
        </div>
        @endif
        
        <form method="POST" action="{{ route('brief.store') }}" class="space-y-6">
            @csrf
            
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50">
                <h2 class="text-xl font-semibold text-slate-900 mb-6">Personal Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Full Name *</label>
                        <input type="text" name="name" required 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                            placeholder="John Doe">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email Address *</label>
                        <input type="email" name="email" required 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                            placeholder="john@example.com">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Phone Number *</label>
                        <input type="text" name="phone" required 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                            placeholder="+234 906 525 7784">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Company (Optional)</label>
                        <input type="text" name="company" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                            placeholder="Your Company Ltd">
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50">
                <h2 class="text-xl font-semibold text-slate-900 mb-6">Project Details</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Project Type *</label>
                        <select name="project_type" required 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                            <option value="">Select Project Type</option>
                            <option value="web">Web Application</option>
                            <option value="mobile">Mobile Application</option>
                            <option value="design">UI/UX Design</option>
                            <option value="api">API Development</option>
                            <option value="automation">Business Automation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Project Description *</label>
                        <textarea name="description" rows="5" required 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                            placeholder="Describe your project, goals, and any specific requirements..."></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Budget Range</label>
                            <select name="budget_range" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                                <option value="">Select Budget</option>
                                <option value="50k-100k">#50,000 - #100,000</option>
                                <option value="100k-250k">#100,000 - #250,000</option>
                                <option value="250k-500k">#250,000 - #500,000</option>
                                <option value="500k+">#500,000+</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Timeline</label>
                            <select name="timeline" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                                <option value="">Select Timeline</option>
                                <option value="asap">ASAP (Urgent)</option>
                                <option value="1-month">Within 1 Month</option>
                                <option value="1-3-months">1-3 Months</option>
                                <option value="3-6-months">3-6 Months</option>
                                <option value="flexible">Flexible</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-xl transition-all hover:scale-[1.02]">
                Submit Project Brief
            </button>
            
            <p class="text-center text-sm text-slate-500">I typically respond within 24 hours.</p>
        </form>
    </div>
</section>
@endsection