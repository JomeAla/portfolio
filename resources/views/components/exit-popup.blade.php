@props([
    'enabled' => true,
    'offerText' => "Wait! Don't go yet!",
    'discount' => 10,
    'discountType' => 'percent',
    'buttonText' => 'Claim My Discount'
])

@if($enabled)
    <div x-data="exitPopup({
        enabled: {{ $enabled ? 'true' : 'false' }},
        offerText: {{ Js::from($offerText) }},
        discount: {{ $discount }},
        discountType: {{ Js::from($discountType) }},
        buttonText: {{ Js::from($buttonText) }}
    })" x-init="init()" @unless(true) @endunless"></div>
@endif

<div id="exit-popup-overlay" class="fixed inset-0 bg-black/70 z-[999998] opacity-0 transition-opacity duration-300 pointer-events-none hidden"></div>

<div id="exit-popup" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-[0.9] bg-white rounded-2xl shadow-2xl z-[999999] max-w-md w-[90%] p-10 text-center opacity-0 transition-all duration-300 pointer-events-none hidden">
    <button id="exit-popup-close" class="absolute top-4 right-4 bg-transparent border-none text-[28px] cursor-pointer text-gray-500 hover:text-gray-700 transition-colors leading-none p-1">&times;</button>
    
    <div class="text-xs text-gray-400 uppercase tracking-[2px] mb-2">Special Offer</div>
    <h2 id="exit-popup-title" class="text-3xl font-bold text-gray-800 mb-5">{{ $offerText }}</h2>
    
    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-6 rounded-xl mb-6">
        <div id="exit-popup-discount" class="text-5xl font-bold">
            @if($discountType === 'fixed')
                ${{ $discount }}
            @else
                {{ $discount }}%
            @endif
        </div>
        <div class="text-base mt-1">OFF discount applied at checkout</div>
    </div>
    
    <form id="exit-popup-form" action="/newsletter/subscribe" method="POST" class="mb-5">
        @csrf
        <input type="email" id="exit-popup-email" name="email" placeholder="Enter your email" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg text-base mb-4 focus:border-indigo-500 focus:outline-none transition-colors">
        <button type="submit" id="exit-popup-submit" class="w-full px-4 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white border-none rounded-lg text-lg font-semibold cursor-pointer hover:scale-[1.02] hover:shadow-lg transition-all">
            {{ $buttonText }}
        </button>
    </form>
    
    <p class="text-gray-400 text-sm">No spam, unsubscribe anytime</p>
</div>

@once
    @push('scripts')
        <script src="{{ asset('js/exit-popup.js') }}"></script>
        <script>
            function exitPopup(config) {
                return {
                    config: {
                        enabled: config.enabled,
                        offerText: config.offerText,
                        discount: config.discount,
                        discountType: config.discountType,
                        buttonText: config.buttonText,
                        delay: 5000,
                        cookieDays: 1
                    },
                    popupShown: false,
                    sessionCookieName: 'exit_popup_session',
                    persistentCookieName: 'exit_popup_dismissed',

                    init() {
                        if (!this.config.enabled || this.hasSeenPopupThisSession()) return;
                        this.bindEvents();
                    },

                    hasSeenPopupThisSession() {
                        return document.cookie.indexOf(this.sessionCookieName + '=') !== -1;
                    },

                    setSessionCookie() {
                        document.cookie = `${this.sessionCookieName}=true; path=/; max-age=${60 * 60 * 24}`;
                    },

                    bindEvents() {
                        const closeBtn = document.getElementById('exit-popup-close');
                        const form = document.getElementById('exit-popup-form');
                        const emailInput = document.getElementById('exit-popup-email');
                        const overlay = document.getElementById('exit-popup-overlay');
                        const popup = document.getElementById('exit-popup');

                        closeBtn?.addEventListener('click', () => this.closePopup());
                        overlay?.addEventListener('click', () => this.closePopup());

                        form?.addEventListener('submit', (e) => {
                            e.preventDefault();
                            this.handleFormSubmit(emailInput.value);
                        });

                        document.addEventListener('mousemove', (e) => {
                            if (e.clientY <= 5 && !this.popupShown) {
                                this.showPopup();
                            }
                        });

                        document.addEventListener('keydown', (e) => {
                            if (e.key === 'Escape') this.closePopup();
                        });

                        if ('ontouchstart' in window) {
                            let touchStartY = 0;
                            document.addEventListener('touchstart', (e) => {
                                touchStartY = e.touches[0].clientY;
                            });
                            document.addEventListener('touchend', (e) => {
                                const touchEndY = e.changedTouches[0].clientY;
                                if (touchStartY < 50 && touchEndY < touchStartY) {
                                    this.showPopup();
                                }
                            });
                        }
                    },

                    showPopup() {
                        this.popupShown = true;
                        this.setSessionCookie();
                        
                        const overlay = document.getElementById('exit-popup-overlay');
                        const popup = document.getElementById('exit-popup');

                        setTimeout(() => {
                            overlay?.classList.remove('opacity-0', 'pointer-events-none', 'hidden');
                            overlay?.classList.add('opacity-100');
                            popup?.classList.remove('opacity-0', 'pointer-events-none', 'hidden', 'scale-[0.9]');
                            popup?.classList.add('opacity-100', 'scale-100');
                            document.body.style.overflow = 'hidden';
                        }, this.config.delay);
                    },

                    closePopup() {
                        const overlay = document.getElementById('exit-popup-overlay');
                        const popup = document.getElementById('exit-popup');

                        overlay?.classList.add('opacity-0', 'pointer-events-none');
                        overlay?.classList.remove('opacity-100');
                        popup?.classList.add('opacity-0', 'scale-[0.9]', 'pointer-events-none');
                        popup?.classList.remove('opacity-100', 'scale-100');

                        setTimeout(() => {
                            overlay?.classList.add('hidden');
                            popup?.classList.add('hidden');
                            document.body.style.overflow = '';
                        }, 300);
                    },

                    handleFormSubmit(email) {
                        const form = document.getElementById('exit-popup-form');
                        const submitBtn = document.getElementById('exit-popup-submit');
                        const originalText = submitBtn.textContent;

                        submitBtn.textContent = 'Saving...';
                        submitBtn.disabled = true;

                        const formData = new FormData(form);
                        
                        fetch('/newsletter/subscribe', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            submitBtn.textContent = 'Discount Applied!';
                            submitBtn.style.background = '#22c55e';
                            setTimeout(() => this.closePopup(), 1500);
                        })
                        .catch(() => {
                            submitBtn.textContent = 'Error. Try Again.';
                            submitBtn.style.background = '#ef4444';
                            setTimeout(() => {
                                submitBtn.textContent = originalText;
                                submitBtn.disabled = false;
                            }, 2000);
                        });
                    }
                }
            }
        </script>
    @endpush
@endonce
