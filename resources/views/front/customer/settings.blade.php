@extends('front.customer.layout')

@section('customer-content')
<h1 class="text-3xl font-bold text-slate-800 mb-8">Settings</h1>

<div class="bg-white rounded-xl shadow-sm p-8">
    <form method="POST" action="/customer/settings">
        @csrf
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Name</label>
            <input type="text" name="name" value="{{ $customer['name'] ?? '' }}" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
            <input type="email" value="{{ $customer['email'] ?? '' }}" disabled class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 text-slate-500">
        </div>
        
        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Changes</button>
    </form>
</div>
@endsection