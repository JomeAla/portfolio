@extends('front.customer.layout')

@section('customer-content')
<h1 class="text-3xl font-bold text-slate-800 mb-8">My Downloads</h1>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if(count($downloads ?? []) > 0)
    <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Product</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Order #</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Date</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($downloads as $download)
            <tr>
                <td class="px-6 py-4 text-slate-800">{{ $download['product_name'] ?? 'Product' }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $download['order_number'] }}</td>
                <td class="px-6 py-4 text-slate-600">{{ date('M d, Y', strtotime($download['created_at'] ?? now())) }}</td>
                <td class="px-6 py-4">
                    @if($download['download_token'])
                    <a href="/order/download/{{ $download['download_token'] }}" class="text-blue-600 hover:underline">Download</a>
                    @elseif($download['file_path'])
                    <a href="{{ $download['file_path'] }}" class="text-blue-600 hover:underline" target="_blank">Download</a>
                    @else
                    <span class="text-slate-400">No file</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="p-8 text-center">
        <p class="text-slate-600">No downloads available yet.</p>
        <a href="/" class="text-blue-600 hover:underline mt-2 inline-block">Browse products</a>
    </div>
    @endif
</div>
@endsection
