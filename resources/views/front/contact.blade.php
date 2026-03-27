@extends('layouts.app')

@section('title', 'Contact')

@section('content')
<section class="py-20 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900">Get in Touch</h1>
            <p class="text-lg text-slate-600 mt-4">Have a project in mind? Let's talk!</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50">
                <h2 class="text-xl font-bold text-slate-900 mb-6">Contact Information</h2>
                
                <div class="space-y-4">
                    <a href="tel:+2349065257784" class="flex items-center gap-4 text-slate-600 hover:text-blue-600 transition-colors">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-phone text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">Phone</p>
                            <p>+2349065257784</p>
                        </div>
                    </a>
                    
                    <a href="https://wa.me/2349065257784" class="flex items-center gap-4 text-slate-600 hover:text-green-600 transition-colors">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fab fa-whatsapp text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">WhatsApp</p>
                            <p>+2349065257784</p>
                        </div>
                    </a>
                    
                    <a href="mailto:jomealawuru@hotmail.com" class="flex items-center gap-4 text-slate-600 hover:text-blue-600 transition-colors">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-envelope text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">Email</p>
                            <p>jomealawuru@hotmail.com</p>
                        </div>
                    </a>
                    
                    <a href="mailto:support@joala.com.ng" class="flex items-center gap-4 text-slate-600 hover:text-blue-600 transition-colors">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-headset text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">Support</p>
                            <p>support@joala.com.ng</p>
                        </div>
                    </a>
                    
                    <a href="https://twitter.com/jomswoks" target="_blank" class="flex items-center gap-4 text-slate-600 hover:text-blue-400 transition-colors">
                        <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                            <i class="fab fa-twitter text-slate-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">Twitter</p>
                            <p>@jomswoks</p>
                        </div>
                    </a>
                    
                    <div class="flex items-start gap-4 text-slate-600">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-amber-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">Office Address</p>
                            <p class="text-sm">132 Ovwian main road, Opposite the Primary School, Ovwian, Delta State, Nigeria</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50">
                <h2 class="text-xl font-bold text-slate-900 mb-6">Send a Message</h2>
                
                @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4">
                    <p class="font-semibold">Thank you!</p>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
                @endif
                
                <form method="POST" action="{{ route('support.submit') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Name</label>
                        <input type="text" name="name" required 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                        <input type="email" name="email" required 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Subject</label>
                        <input type="text" name="subject" required 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Message</label>
                        <textarea name="message" rows="4" required 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition-colors">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
