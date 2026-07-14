<div id="cookie-consent" class="fixed bottom-0 left-0 right-0 z-[100] bg-slate-900 border-t border-slate-700 shadow-2xl translate-y-full transition-transform duration-500 ease-in-out">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex-1">
                <p class="text-white text-sm leading-relaxed">
                    This website uses cookies to improve your experience. By continuing to use this site, you accept our use of cookies.
                    <a href="{{ route('cookie.policy') }}" class="text-blue-400 hover:text-blue-300 underline ml-1 whitespace-nowrap">Learn more</a>
                </p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <button id="cookie-reject" class="px-4 py-2 text-sm text-slate-300 hover:text-white border border-slate-600 rounded-lg hover:bg-slate-800 transition-colors">
                    Reject All
                </button>
                <button id="cookie-accept" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                    Accept All
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var key = 'joala_cookie_consent';
    var banner = document.getElementById('cookie-consent');
    var acceptBtn = document.getElementById('cookie-accept');
    var rejectBtn = document.getElementById('cookie-reject');

    function getConsent() {
        try { return localStorage.getItem(key); } catch(e) { return null; }
    }

    function setConsent(value) {
        try { localStorage.setItem(key, value); } catch(e) {}
        hideBanner();
        if (value === 'accepted') {
            loadAnalytics();
        }
    }

    function showBanner() {
        if (!banner) return;
        requestAnimationFrame(function() {
            banner.classList.remove('translate-y-full');
        });
    }

    function hideBanner() {
        if (!banner) return;
        banner.classList.add('translate-y-full');
        setTimeout(function() { banner.style.display = 'none'; }, 500);
    }

    function loadAnalytics() {
        // Placeholder for analytics scripts (e.g., GA4, Facebook Pixel)
        // These scripts will only load after user accepts cookies
    }

    if (getConsent() !== null) {
        banner.style.display = 'none';
        if (getConsent() === 'accepted') {
            loadAnalytics();
        }
    } else {
        showBanner();
    }

    if (acceptBtn) {
        acceptBtn.addEventListener('click', function() { setConsent('accepted'); });
    }
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() { setConsent('rejected'); });
    }
})();
</script>