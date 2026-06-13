<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Product;
use App\Models\Affiliate;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays((int)$period);
        $previousStartDate = now()->subDays((int)$period * 2);
        $previousEndDate = $startDate;

        $revenueData = $this->getRevenueData($startDate, $previousStartDate, $previousEndDate);
        $customerData = $this->getCustomerData($startDate, $previousStartDate, $previousEndDate);
        $conversionData = $this->getConversionData($startDate, $previousStartDate, $previousEndDate);
        $productData = $this->getProductData($startDate);
        $trafficData = $this->getTrafficData($startDate);
        $funnelData = $this->getFunnelData($startDate);
        $affiliateData = $this->getAffiliateData();
        $orderData = $this->getOrderData($startDate);
        $geoData = $this->getGeoData($startDate);

        return view('admin.analytics.index', compact(
            'revenueData', 'customerData', 'conversionData', 'productData',
            'trafficData', 'funnelData', 'affiliateData', 'orderData', 'geoData'
        ));
    }

    private function getRevenueData($startDate, $previousStartDate, $previousEndDate)
    {
        $currentRevenue = Order::where('payment_status', 'success')
            ->where('created_at', '>=', $startDate)
            ->sum('final_amount') ?? 0;

        $previousRevenue = Order::where('payment_status', 'success')
            ->where('created_at', '>=', $previousStartDate)
            ->where('created_at', '<', $previousEndDate)
            ->sum('final_amount') ?? 0;

        $revenueChange = $previousRevenue > 0
            ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : ($currentRevenue > 0 ? 100 : 0);

        $today = Order::where('payment_status', 'success')
            ->whereDate('created_at', today())
            ->sum('final_amount') ?? 0;

        $thisWeek = Order::where('payment_status', 'success')
            ->where('created_at', '>=', now()->startOfWeek())
            ->sum('final_amount') ?? 0;

        $thisMonth = Order::where('payment_status', 'success')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('final_amount') ?? 0;

        return [
            'total_revenue' => $currentRevenue,
            'revenue_change' => $revenueChange,
            'today' => $today,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
        ];
    }

    private function getCustomerData($startDate, $previousStartDate, $previousEndDate)
    {
        $current = Order::where('payment_status', 'success')
            ->where('created_at', '>=', $startDate)
            ->distinct('customer_email')
            ->count('customer_email') ?? 0;

        $previous = Order::where('payment_status', 'success')
            ->where('created_at', '>=', $previousStartDate)
            ->where('created_at', '<', $previousEndDate)
            ->distinct('customer_email')
            ->count('customer_email') ?? 0;

        $change = $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : ($current > 0 ? 100 : 0);

        return [
            'total_customers' => $current,
            'customers_change' => $change,
        ];
    }

    private function getConversionData($startDate, $previousStartDate, $previousEndDate)
    {
        $leads = Lead::where('created_at', '>=', $startDate)->count() ?? 0;
        $orders = Order::where('payment_status', 'success')
            ->where('created_at', '>=', $startDate)
            ->count() ?? 0;

        $currentRate = $leads > 0 ? round(($orders / $leads) * 100, 1) : 0;

        $prevLeads = Lead::where('created_at', '>=', $previousStartDate)
            ->where('created_at', '<', $previousEndDate)->count() ?? 0;
        $prevOrders = Order::where('payment_status', 'success')
            ->where('created_at', '>=', $previousStartDate)
            ->where('created_at', '<', $previousEndDate)->count() ?? 0;

        $prevRate = $prevLeads > 0 ? round(($prevOrders / $prevLeads) * 100, 1) : 0;
        $change = $prevRate > 0 ? round($currentRate - $prevRate, 1) : $currentRate;

        return [
            'conversion_rate' => $currentRate,
            'conversion_change' => $change,
        ];
    }

    private function getProductData($startDate)
    {
        $topProducts = Order::where('payment_status', 'success')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('product_id')
            ->selectRaw('product_id, SUM(final_amount) as revenue, COUNT(*) as orders')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $product = Product::find($row->product_id);
                return [
                    'title' => $product?->title ?? 'Product #' . $row->product_id,
                    'revenue' => $row->revenue ?? 0,
                    'orders' => $row->orders ?? 0,
                ];
            });

        return ['top_products' => $topProducts->toArray()];
    }

    private function getTrafficData($startDate)
    {
        $sources = Lead::where('created_at', '>=', $startDate)
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->orderByDesc('count')
            ->pluck('count', 'source')
            ->toArray();

        $totalVisits = array_sum($sources);

        return [
            'sources' => $sources,
            'total_visits' => $totalVisits,
        ];
    }

    private function getFunnelData($startDate)
    {
        $visitors = Lead::where('created_at', '>=', $startDate)->count() ?? 0;
        $leads = $visitors;
        $customers = Order::where('payment_status', 'success')
            ->where('created_at', '>=', $startDate)
            ->distinct('customer_email')
            ->count('customer_email') ?? 0;

        $visitorToLead = $visitors > 0 ? round(($leads / $visitors) * 100, 1) : 0;
        $leadToCustomer = $leads > 0 ? round(($customers / $leads) * 100, 1) : 0;

        return [
            'visitors' => $visitors,
            'leads' => $leads,
            'customers' => $customers,
            'visitor_to_lead' => $visitorToLead,
            'lead_to_customer' => $leadToCustomer,
        ];
    }

    private function getAffiliateData()
    {
        return [
            'total_affiliates' => Affiliate::count() ?? 0,
            'active_affiliates' => Affiliate::where('status', 'active')->count() ?? 0,
            'total_referrals' => Order::whereNotNull('affiliate_id')->count() ?? 0,
            'commission_paid' => Order::where('payment_status', 'success')
                ->whereNotNull('affiliate_id')
                ->sum('affiliate_commission') ?? 0,
            'pending_payout' => 0,
        ];
    }

    private function getOrderData($startDate)
    {
        $recent = Order::where('created_at', '>=', $startDate)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'customer_name' => $order->customer_name ?? $order->customer_email ?? 'Guest',
                    'created_at' => $order->created_at->format('M d, Y H:i'),
                    'final_amount' => $order->final_amount ?? 0,
                    'payment_status' => $order->payment_status,
                ];
            });

        return ['recent' => $recent->toArray()];
    }

    private function getGeoData($startDate)
    {
        return ['locations' => []];
    }
}