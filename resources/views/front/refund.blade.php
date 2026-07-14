@extends('layouts.app')

@section('title', 'Refund Policy')
@section('meta_description', 'JoAla Ventures refund and return policy for digital products. Learn about our satisfaction guarantee and refund process.')

@section('content')
<div class="py-20 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 md:p-12">
            <h1 class="text-3xl font-bold text-slate-900 mb-4">Refund Policy</h1>
            <p class="text-slate-500 mb-8">Last updated: {{ now()->format('F j, Y') }}</p>

            <div class="prose prose-slate max-w-none">
                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">1. Overview</h2>
                <p class="text-slate-600 mb-4">Joala Ventures strives to provide high-quality digital services. This refund policy outlines the terms under which refunds may be requested.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">2. Digital Services - No Refund Policy</h2>
                <p class="text-slate-600 mb-4">Due to the nature of digital services and custom work, all payments for completed services are non-refundable. This includes:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li>Web development and programming services</li>
                    <li>Software solutions and custom development</li>
                    <li>Consulting and advisory services</li>
                    <li>Digital strategy and planning</li>
                    <li>Training and workshops</li>
                </ul>
                <p class="text-slate-600 mb-4">Once work commences or deliverables are provided, no refunds can be issued due to the time and resources invested.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">3. Pre-Project Payments</h2>
                <p class="text-slate-600 mb-4">For new projects, a 50% upfront payment is required to begin work. The upfront payment is:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li><strong>Non-refundable</strong> once work has commenced</li>
                    <li>Transferable to a new project if within 6 months</li>
                    <li>Forfeited if no new project is initiated within 6 months</li>
                </ul>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">4. Cancellation Before Work Begins</h2>
                <p class="text-slate-600 mb-4">If you cancel before work begins:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li>Full refund of any advance payment if cancelled within 48 hours</li>
                    <li>After 48 hours, the advance payment is non-refundable</li>
                </ul>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">5. Dispute Resolution</h2>
                <p class="text-slate-600 mb-4">If you believe the delivered work does not meet the agreed specifications:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li>Contact us within 7 days of delivery</li>
                    <li>Provide specific concerns in writing</li>
                    <li>We will review and address any legitimate issues</li>
                    <li>At our discretion, revisions may be provided</li>
                </ul>
                <p class="text-slate-600 mb-4">This is the sole remedy for disputes regarding deliverables.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">6. Subscription Services</h2>
                <p class="text-slate-600 mb-4">For any recurring subscription services:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li>Cancel anytime with 30 days notice</li>
                    <li>No refunds for partial months</li>
                    <li>Access continues until end of paid period</li>
                </ul>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">7. How to Request Information</h2>
                <p class="text-slate-600 mb-4">For billing inquiries or dispute resolution:</p>
                <ul class="list-disc pl-6 text-slate-600">
                    <li>Email: hello@joala.com.ng</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection