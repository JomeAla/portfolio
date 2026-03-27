<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('product');

        if ($request->status) {
            $query->where('payment_status', $request->status);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%")
                  ->orWhere('customer_email', 'like', "%{$request->search}%");
            });
        }

        $orders = $query->orderBy('id', 'desc')->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('product');
        return view('admin.orders.show', compact('order'));
    }

    public function resendEmail(Order $order)
    {
        if ($order->payment_status !== 'success') {
            return back()->with('error', 'Order payment not completed.');
        }

        $orderController = new \App\Http\Controllers\Front\OrderController();
        $orderController->sendDownloadEmail($order);

        return back()->with('success', 'Download link resent to customer!');
    }
}
