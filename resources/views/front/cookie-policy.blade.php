@extends('layouts.app')

@section('title', 'Cookie Policy')
@section('meta_description', 'JoAla Ventures cookie policy. Learn about the cookies we use, why we use them, and how to manage your preferences.')

@section('content')
<div class="py-20 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 md:p-12">
            <h1 class="text-3xl font-bold text-slate-900 mb-4">Cookie Policy</h1>
            <p class="text-slate-500 mb-8">Last updated: {{ now()->format('F j, Y') }}</p>

            <div class="prose prose-slate max-w-none">
                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">1. What Are Cookies</h2>
                <p class="text-slate-600 mb-4">Cookies are small text files stored on your device when you visit a website. They help the website remember your preferences, understand how you use the site, and improve your experience.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">2. How We Use Cookies</h2>
                <p class="text-slate-600 mb-4">We use cookies for the following purposes:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li><strong>Essential/Strictly Necessary:</strong> These cookies are required for the website to function properly. They enable core functionality such as security, session management, and account access.</li>
                    <li><strong>Functional:</strong> These cookies remember your preferences (e.g., language, region) to provide a personalized experience.</li>
                    <li><strong>Analytics:</strong> We use anonymized analytics to understand how visitors interact with our website, which pages are most visited, and how we can improve.</li>
                    <li><strong>Marketing:</strong> These cookies track your browsing habits to deliver relevant advertisements. We only use these with your explicit consent.</li>
                </ul>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">3. Types of Cookies We Use</h2>
                <div class="overflow-x-auto mb-4">
                    <table class="w-full text-sm text-slate-600 border-collapse">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="text-left p-3 border border-slate-200 font-semibold">Cookie Name</th>
                                <th class="text-left p-3 border border-slate-200 font-semibold">Type</th>
                                <th class="text-left p-3 border border-slate-200 font-semibold">Duration</th>
                                <th class="text-left p-3 border border-slate-200 font-semibold">Purpose</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="p-3 border border-slate-200 font-mono text-xs">laravel_session</td>
                                <td class="p-3 border border-slate-200">Essential</td>
                                <td class="p-3 border border-slate-200">Session (2 hours)</td>
                                <td class="p-3 border border-slate-200">Maintains user session and CSRF protection</td>
                            </tr>
                            <tr>
                                <td class="p-3 border border-slate-200 font-mono text-xs">XSRF-TOKEN</td>
                                <td class="p-3 border border-slate-200">Essential</td>
                                <td class="p-3 border border-slate-200">Session</td>
                                <td class="p-3 border border-slate-200">CSRF protection token</td>
                            </tr>
                            <tr>
                                <td class="p-3 border border-slate-200 font-mono text-xs">joala_cookie_consent</td>
                                <td class="p-3 border border-slate-200">Functional</td>
                                <td class="p-3 border border-slate-200">Persistent</td>
                                <td class="p-3 border border-slate-200">Stores your cookie consent preference</td>
                            </tr>
                            <tr>
                                <td class="p-3 border border-slate-200 font-mono text-xs">_ga*</td>
                                <td class="p-3 border border-slate-200">Analytics</td>
                                <td class="p-3 border border-slate-200">2 years</td>
                                <td class="p-3 border border-slate-200">Google Analytics (loaded only after consent)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">4. Third-Party Cookies</h2>
                <p class="text-slate-600 mb-4">We may use third-party services that set their own cookies:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li><strong>Google Analytics:</strong> Tracks page views and user behavior (anonymized)</li>
                    <li><strong>Font Awesome:</strong> Delivers icon fonts via CDN</li>
                    <li><strong>Google Fonts:</strong> Delivers web fonts</li>
                    <li><strong>Tailwind CSS:</strong> Utility CSS framework loaded via CDN</li>
                </ul>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">5. Managing Your Cookie Preferences</h2>
                <p class="text-slate-600 mb-4">You can manage or disable cookies at any time through your browser settings:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li><strong>Chrome:</strong> Settings &rarr; Privacy and security &rarr; Cookies and other site data</li>
                    <li><strong>Firefox:</strong> Options &rarr; Privacy & Security &rarr; Cookies and Site Data</li>
                    <li><strong>Safari:</strong> Preferences &rarr; Privacy &rarr; Cookies and website data</li>
                    <li><strong>Edge:</strong> Settings &rarr; Cookies and site permissions &rarr; Cookies</li>
                </ul>
                <p class="text-slate-600 mb-4">You can also clear the <code class="bg-slate-100 px-1 rounded">joala_cookie_consent</code> entry from your browser's local storage to reset the cookie banner and make a new choice.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">6. Your Rights (GDPR)</h2>
                <p class="text-slate-600 mb-4">If you are located in the European Economic Area, you have the right to:</p>
                <ul class="list-disc pl-6 text-slate-600 mb-4">
                    <li>Withdraw consent at any time by clearing cookies</li>
                    <li>Request a copy of any data we hold about you</li>
                    <li>Request deletion of your data</li>
                    <li>Lodge a complaint with your local data protection authority</li>
                </ul>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">7. Updates to This Policy</h2>
                <p class="text-slate-600 mb-4">We may update this cookie policy from time to time. Any changes will be posted on this page with an updated revision date.</p>

                <h2 class="text-xl font-semibold text-slate-800 mt-8 mb-4">8. Contact Us</h2>
                <p class="text-slate-600 mb-4">If you have any questions about our use of cookies, please contact us:</p>
                <ul class="list-disc pl-6 text-slate-600">
                    <li>Email: support@joala.com.ng</li>
                    <li>Address: 132 Ovwian Main Road, Opposite the Primary School, Ovwian, Delta State, Nigeria</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection