@extends('layouts.app')

@section('title', 'Privacy Policy')
@section('meta_description', 'JoAla Ventures privacy policy. Learn how we collect, use, and protect your personal information when you use our services.')

@section('content')
<div class="py-20 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 md:p-12">
            <h1 class="text-3xl font-bold text-slate-900 mb-4">Privacy Policy</h1>
            <p class="text-slate-500 mb-8">Last updated: {{ now()->format('F j, Y') }}</p>

            <div class="prose prose-slate max-w-none">
                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">1. Introduction</h2>
                <p class="text-slate-600 mb-4">Joala Ventures ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">2. Information We Collect</h2>
                <p class="text-slate-600 mb-4"><strong>Personal Information:</strong></p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li>Name and email address (when you subscribe to our newsletter)</li>
                    <li>Contact information you provide through forms</li>
                    <li>Account credentials when you register</li>
                </ul>
                <p class="text-slate-600 mb-4"><strong>Automatically Collected Information:</strong></p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li>IP address and browser type</li>
                    <li>Pages visited and time spent</li>
                    <li>Referring website addresses</li>
                </ul>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">3. How We Use Your Information</h2>
                <p class="text-slate-600 mb-4">We use your information to:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li>Provide and improve our services</li>
                    <li>Send you newsletters and updates (with your consent)</li>
                    <li>Respond to your inquiries</li>
                    <li>Analyze website usage to enhance user experience</li>
                    <li>Comply with legal obligations</li>
                </ul>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">4. GDPR Compliance (For EU Visitors)</h2>
                <p class="text-slate-600 mb-4">If you are located in the European Economic Area (EEA), you have additional rights under GDPR:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li><strong>Right to Access:</strong> Request a copy of your personal data</li>
                    <li><strong>Right to Rectification:</strong> Correct inaccurate personal data</li>
                    <li><strong>Right to Erasure:</strong> Request deletion of your data ("right to be forgotten")</li>
                    <li><strong>Right to Restrict Processing:</strong> Limit how we use your data</li>
                    <li><strong>Right to Data Portability:</strong> Receive your data in a portable format</li>
                    <li><strong>Right to Object:</strong> Object to processing for direct marketing</li>
                    <li><strong>Rights Related to Automated Decision-Making:</strong> Not be subject to solely automated decisions</li>
                </ul>
                <p class="text-slate-600 mb-4">To exercise these rights, contact us at hello@joala.com.ng</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">5. Data Retention</h2>
                <p class="text-slate-600 mb-4">We retain your personal data only as long as necessary for the purposes outlined in this policy. Newsletter subscribers may unsubscribe at any time, which will result in deletion of their data within 30 days.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">6. Cookies and Tracking</h2>
                <p class="text-slate-600 mb-4">We use essential cookies for website functionality. For analytics, we use anonymized data that cannot identify you personally.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">7. Third-Party Disclosure</h2>
                <p class="text-slate-600 mb-4">We do not sell, trade, or transfer your personal information to third parties without your consent, except as required by law or for service delivery (e.g., email delivery services).</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">8. Data Security</h2>
                <p class="text-slate-600 mb-4">We implement appropriate technical and organizational measures to protect your personal data, including SSL encryption for data in transit.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">9. Children's Privacy</h2>
                <p class="text-slate-600 mb-4">Our website is not intended for children under 13. We do not knowingly collect information from children under 13.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">10. Changes to This Policy</h2>
                <p class="text-slate-600 mb-4">We may update this policy periodically. We will notify you of material changes via email or prominent notice on our website.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">11. Contact Us</h2>
                <p class="text-slate-600 mb-4">For privacy concerns or to exercise your rights, contact:</p>
                <ul class="list-disc pl-6 text-slate-600">
                    <li>Email: hello@joala.com.ng</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection