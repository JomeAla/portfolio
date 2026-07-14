@extends('layouts.app')

@section('title', 'Terms and Conditions')
@section('meta_description', 'JoAla Ventures terms and conditions governing the use of our website, services, and digital products. Read our terms before purchasing.')

@section('content')
<div class="py-20 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 md:p-12">
            <h1 class="text-3xl font-bold text-slate-900 mb-4">Terms and Conditions</h1>
            <p class="text-slate-500 mb-8">Last updated: {{ now()->format('F j, Y') }}</p>

            <div class="prose prose-slate max-w-none">
                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">1. Acceptance of Terms</h2>
                <p class="text-slate-600 mb-4">By accessing and using Joala Ventures' website, you accept and agree to be bound by the terms and provision of this agreement. Additionally, when using Joala Ventures' services, you will be subject to the rules, guidelines, policies, terms, and conditions applicable to such services.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">2. Description of Services</h2>
                <p class="text-slate-600 mb-4">Joala Ventures provides digital services including but not limited to web development, software solutions, digital consulting, and technology training. Our services are designed to help businesses establish and enhance their digital presence.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">3. Intellectual Property</h2>
                <p class="text-slate-600 mb-4">All content, materials, designs, and intellectual property displayed on this website are the exclusive property of Joala Ventures. This includes but is not limited to logos, graphics, text, software, and code. No part of this site may be reproduced, distributed, or modified without prior written permission.</p>
                <p class="text-slate-600 mb-4">Client deliverables become the property of clients upon full payment, as specifically outlined in project agreements.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">4. User Accounts and Registration</h2>
                <p class="text-slate-600 mb-4">To access certain features, you may be required to register an account. You agree to:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li>Provide accurate and complete registration information</li>
                    <li>Maintain the security of your account credentials</li>
                    <li>Promptly update any changes to your information</li>
                    <li>Accept responsibility for all activities under your account</li>
                </ul>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">5. Payment and Billing</h2>
                <p class="text-slate-600 mb-4">For paid services:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li>Payment terms will be specified in project quotes or service agreements</li>
                    <li>Standard payment terms require 50% upfront for new projects</li>
                    <li>Final payment due upon project completion before delivery</li>
                    <li>Payments are non-refundable unless otherwise specified</li>
                </ul>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">6. Limitation of Liability</h2>
                <p class="text-slate-600 mb-4">Joala Ventures shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of or inability to use our services. Our total liability shall not exceed the amount paid by you for the specific service giving rise to the claim.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">7. Disclaimer of Warranties</h2>
                <p class="text-slate-600 mb-4">Services are provided "as is" without warranty of any kind. Joala Ventures disclaims all warranties, express or implied, including but not limited to implied warranties of merchantability and fitness for a particular purpose.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">8. Termination</h2>
                <p class="text-slate-600 mb-4">Either party may terminate this agreement with written notice. Upon termination:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li>Client retains all completed deliverables paid for</li>
                    <li>Work in progress will be delivered pro-rata based on payment received</li>
                    <li>Confidentiality provisions survive termination</li>
                </ul>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">9. Governing Law</h2>
                <p class="text-slate-600 mb-4">These terms shall be governed by and construed in accordance with the laws of Nigeria, without regard to its conflict of law provisions.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">10. Contact Information</h2>
                <p class="text-slate-600 mb-4">For questions about these Terms and Conditions, please contact us:</p>
                <ul class="list-disc pl-6 text-slate-600">
                    <li>Email: hello@joala.com.ng</li>
                    <li>Phone: Available via contact form</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection