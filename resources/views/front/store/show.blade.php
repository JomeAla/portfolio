@extends('layouts.app')

@section('title', $product->title)

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <a href="{{ route('store') }}" class="text-blue-600 hover:underline mb-6 inline-flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back to Store
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}" class="w-full rounded-2xl mb-6">
                @else
                <div class="w-full h-96 bg-gray-200 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-box text-6xl text-gray-400"></i>
                </div>
                @endif

                @if($product->images)
                <div class="grid grid-cols-4 gap-2">
                    @foreach(json_decode($product->images) as $image)
                    <img src="{{ asset('storage/' . $image) }}" alt="Gallery" class="w-full h-24 object-cover rounded-lg">
                    @endforeach
                </div>
                @endif

                <div class="bg-white rounded-2xl p-6 mt-6">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">Description</h2>
                    <div class="prose max-w-none">
                        {{ $product->description }}
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 sticky top-6">
                    <span class="text-sm font-medium text-blue-600 uppercase">{{ $product->type }}</span>
                    <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ $product->title }}</h1>
                    
                    <div class="mt-4">
                        @if($product->isOnSale())
                        <span class="text-gray-400 line-through text-lg">₦{{ number_format($product->price) }}</span>
                        <span class="text-3xl font-bold text-emerald-600">₦{{ number_format($product->sale_price) }}</span>
                        @else
                        <span class="text-3xl font-bold text-slate-900">₦{{ number_format($product->price) }}</span>
                        @endif
                    </div>

                    <form id="purchaseForm" class="mt-6">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                            <input type="text" name="name" required 
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" required 
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <input type="tel" name="phone" required 
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                                placeholder="+234...">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Coupon Code</label>
                            <div class="flex gap-2">
                                <input type="text" name="coupon_code" id="couponCode" 
                                    class="flex-1 px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                                    placeholder="Have a coupon?">
                                <button type="button" id="applyCoupon" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300">
                                    Apply
                                </button>
                            </div>
                            <p id="couponMessage" class="text-sm mt-1 hidden"></p>
                        </div>

                        <button type="submit" id="payButton" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-shopping-cart"></i>
                            Buy Now - ₦{{ number_format($product->getCurrentPrice()) }}
                        </button>
                    </form>

                    <p class="text-sm text-gray-500 mt-4 text-center">
                        <i class="fas fa-lock"></i> Secure payment via Paystack
                    </p>
                </div>
            </div>
        </div>

        @if($relatedProducts->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Related Products</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                <a href="{{ route('store.show', $related->slug) }}" class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow overflow-hidden">
                    @if($related->image)
                    <img src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->title }}" class="w-full h-40 object-cover">
                    @else
                    <div class="w-full h-40 bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-box text-2xl text-gray-400"></i>
                    </div>
                    @endif
                    <div class="p-4">
                        <h3 class="font-semibold text-slate-900">{{ $related->title }}</h3>
                        <p class="text-blue-600 font-bold mt-1">₦{{ number_format($related->getCurrentPrice()) }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
let finalAmount = {{ $product->getCurrentPrice() }};
let currentCoupon = null;

document.getElementById('applyCoupon').addEventListener('click', function() {
    const code = document.getElementById('couponCode').value;
    if (!code) return;

    fetch('{{ route('store.coupon') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ code: code })
    })
    .then(res => res.json())
    .then(data => {
        const msg = document.getElementById('couponMessage');
        msg.classList.remove('hidden');
        
        if (data.error) {
            msg.textContent = data.error;
            msg.className = 'text-sm mt-1 text-red-600';
            currentCoupon = null;
        } else {
            msg.textContent = data.message;
            msg.className = 'text-sm mt-1 text-emerald-600';
            currentCoupon = data;
            
            if (data.discount_type === 'percentage') {
                finalAmount = finalAmount - (finalAmount * data.discount_value / 100);
            } else {
                finalAmount = finalAmount - data.discount_value;
            }
            
            document.getElementById('payButton').innerHTML = '<i class="fas fa-shopping-cart"></i> Buy Now - ₦' + finalAmount.toLocaleString();
        }
    });
});

document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    if (currentCoupon) {
        formData.append('coupon_code', currentCoupon.code);
    }

    fetch('{{ route('order.initiate') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const paystack = new Paystack(data.paystack_public_key);
        paystack.newTransaction({
            key: data.paystack_public_key,
            email: data.email,
            amount: data.amount,
            reference: data.order.order_number,
            onSuccess: function(transaction) {
                window.location.href = '{{ route('order.success') }}?reference=' + transaction.reference + '&trxref=' + transaction.reference;
            },
            onCancel: function() {
                alert('Payment cancelled');
            }
        });
    });
});
</script>
@endsection
