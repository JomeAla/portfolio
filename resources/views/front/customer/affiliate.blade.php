@extends('front.customer.layout')

@section('customer-content')
<h1 class="text-3xl font-bold text-slate-800 mb-8">Affiliate Program</h1>

<div class="bg-white rounded-xl shadow-sm p-8 mb-8">
    <h2 class="text-xl font-semibold text-slate-800 mb-4">Join the Affiliate Program</h2>
    <p class="text-slate-600 mb-6">Earn commissions by referring customers to our products. Join the full affiliate program to get your unique links and track your earnings.</p>
    <a href="/affiliate/register" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Become an Affiliate</a>
</div>

<div class="bg-white rounded-xl shadow-sm p-8">
    <h2 class="text-xl font-semibold text-slate-800 mb-4">Already an Affiliate?</h2>
    <p class="text-slate-600 mb-6">Access your affiliate dashboard to track clicks and earnings.</p>
    <a href="/affiliate/dashboard" class="inline-block px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">Go to Affiliate Dashboard</a>
</div>
@endsection