<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', true);
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }
        
        $products = $query->orderBy('order')->paginate(12);
        
        return view('front.store.index', compact('products'));
    }

    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }
        
        $relatedProducts = Product::where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where('type', $product->type)
            ->limit(4)
            ->get();
        
        return view('front.store.show', compact('product', 'relatedProducts'));
    }

    public function validateCoupon(Request $request)
    {
        $coupon = Coupon::where('code', strtoupper($request->code))->first();
        
        if (!$coupon) {
            return response()->json(['error' => 'Invalid coupon code'], 404);
        }
        
        if (!$coupon->isValid()) {
            return response()->json(['error' => 'Coupon is expired or invalid'], 404);
        }
        
        return response()->json([
            'code' => $coupon->code,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $coupon->discount_value,
            'message' => 'Coupon applied!'
        ]);
    }
}
