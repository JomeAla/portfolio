<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Services\AffiliateCommissionService;

class AffiliateController extends Controller
{
    public function showRegister()
    {
        return view('front.affiliate.register');
    }

    public function processRegister(Request $request)
    {
        try {
            $pdo = db_pdo();
            
            $check = $pdo->prepare("SELECT id FROM affiliates WHERE email = ?");
            $check->execute([$request->email]);
            if ($check->fetch()) {
                return back()->with('error', 'Email already registered')->withInput();
            }
            
            $referralCode = strtoupper(substr($request->name, 0, 3) . rand(1000, 9999));
            
            $stmt = $pdo->prepare("INSERT INTO affiliates (name, email, referral_code, status, commission_rate, created_at) VALUES (?, ?, ?, 'active', 20, NOW())");
            $stmt->execute([$request->name, $request->email, $referralCode]);
            
            $affiliateId = $pdo->lastInsertId();
            
            session(['affiliate_id' => $affiliateId, 'affiliate_name' => $request->name]);
            
            return redirect('/affiliate/dashboard')->with('success', 'Welcome! Your referral code: ' . $referralCode);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function showLogin()
    {
        return view('front.affiliate.login');
    }

    public function processLogin(Request $request)
    {
        try {
            $pdo = db_pdo();
            
            $stmt = $pdo->prepare("SELECT * FROM affiliates WHERE email = ? AND status = 'active'");
            $stmt->execute([$request->email]);
            $affiliate = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($affiliate) {
                session(['affiliate_id' => $affiliate['id'], 'affiliate_name' => $affiliate['name']]);
                return redirect('/affiliate/dashboard')->with('success', 'Welcome back, ' . $affiliate['name'] . '!');
            }
            
            return back()->with('error', 'Invalid credentials');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function dashboard()
    {
        $affiliateId = session('affiliate_id');
        
        if (!$affiliateId) {
            return redirect('/affiliate/login')->with('error', 'Please login first');
        }
        
        try {
            $pdo = db_pdo();
            
            $stmt = $pdo->prepare("SELECT * FROM affiliates WHERE id = ? AND status = 'active'");
            $stmt->execute([$affiliateId]);
            $affiliate = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$affiliate) {
                session()->forget('affiliate_id');
                return redirect('/affiliate/login')->with('error', 'Affiliate not found');
            }
            
            $stmt = $pdo->prepare("SELECT 
                COALESCE(SUM(amount), 0) as total_earned,
                COUNT(*) as total_conversions
                FROM affiliate_commissions 
                WHERE affiliate_id = ? AND status = 'paid'");
            $stmt->execute([$affiliateId]);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as pending FROM affiliate_commissions WHERE affiliate_id = ? AND status = 'pending'");
            $stmt->execute([$affiliateId]);
            $pending = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $products = $pdo->query("SELECT id, title, slug, sale_price, price, image FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC);
            
            $funnels = $pdo->query("SELECT id, name, slug FROM funnels WHERE is_active = 1 AND affiliate_enabled = 1 ORDER BY created_at DESC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC);
            
            $affiliateStats = [
                'total_earned' => $stats['total_earned'] ?? 0,
                'total_conversions' => $stats['total_conversions'] ?? 0,
                'pending' => $pending['pending'] ?? 0,
            ];
            
            $commissions = $pdo->prepare("SELECT ac.*, o.customer_name as referral_name, p.title as product_name FROM affiliate_commissions ac LEFT JOIN orders o ON ac.order_id = o.id LEFT JOIN products p ON o.product_id = p.id WHERE ac.affiliate_id = ? ORDER BY ac.created_at DESC LIMIT 20");
            $commissions->execute([$affiliateId]);
            $commissions = $commissions->fetchAll(\PDO::FETCH_ASSOC);
            
            return view('front.affiliate.dashboard', compact('affiliate', 'affiliateStats', 'products', 'funnels', 'commissions'));
            
        } catch (\Exception $e) {
            return redirect('/affiliate/login')->with('error', $e->getMessage());
        }
    }

    public function logout()
    {
        session()->forget(['affiliate_id', 'affiliate_name']);
        return redirect('/affiliate')->with('success', 'Logged out successfully');
    }

    public function links()
    {
        $affiliateId = session('affiliate_id');
        if (!$affiliateId) {
            return redirect('/affiliate/login');
        }
        
        try {
            $pdo = db_pdo();
            
            $stmt = $pdo->prepare("SELECT * FROM affiliates WHERE id = ?");
            $stmt->execute([$affiliateId]);
            $affiliate = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $products = $pdo->query("SELECT id, title, slug, sale_price, price, image FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC);
            
            return view('front.affiliate.links', compact('affiliate', 'products'));
        } catch (\Exception $e) {
            return redirect('/affiliate/dashboard')->with('error', $e->getMessage());
        }
    }

    public function payouts()
    {
        $affiliateId = session('affiliate_id');
        if (!$affiliateId) {
            return redirect('/affiliate/login');
        }
        
        try {
            $pdo = db_pdo();
            
            $stmt = $pdo->prepare("SELECT * FROM affiliates WHERE id = ?");
            $stmt->execute([$affiliateId]);
            $affiliate = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $payouts = $pdo->prepare("SELECT * FROM affiliate_payouts WHERE affiliate_id = ? ORDER BY created_at DESC LIMIT 20");
            $payouts->execute([$affiliateId]);
            $payouts = $payouts->fetchAll(\PDO::FETCH_ASSOC);
            
            return view('front.affiliate.payouts', compact('affiliate', 'payouts'));
        } catch (\Exception $e) {
            return redirect('/affiliate/dashboard')->with('error', $e->getMessage());
        }
    }

    public function settings()
    {
        $affiliateId = session('affiliate_id');
        if (!$affiliateId) {
            return redirect('/affiliate/login');
        }
        
        try {
            $pdo = db_pdo();
            
            $stmt = $pdo->prepare("SELECT * FROM affiliates WHERE id = ?");
            $stmt->execute([$affiliateId]);
            $affiliate = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return view('front.affiliate.settings', compact('affiliate'));
        } catch (\Exception $e) {
            return redirect('/affiliate/dashboard')->with('error', $e->getMessage());
        }
    }

    public function requestPayout(Request $request)
    {
        $affiliateId = session('affiliate_id');
        if (!$affiliateId) {
            return redirect('/affiliate/login');
        }
        
        try {
            $pdo = db_pdo();
            $stmt = $pdo->prepare("SELECT * FROM affiliates WHERE id = ?");
            $stmt->execute([$affiliateId]);
            $affiliate = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$affiliate) {
                return back()->with('error', 'Affiliate not found.');
            }
            
            $availableBalance = ($affiliate['total_earned'] ?? 0) - ($affiliate['total_paid'] ?? 0);
            $minPayout = $affiliate['min_payout'] ?? 5000;
            
            if ($availableBalance < $minPayout) {
                return back()->with('error', 'Minimum payout amount is &#8358;' . number_format($minPayout) . '. Your available balance is &#8358;' . number_format($availableBalance));
            }
            
            $commissionService = app(AffiliateCommissionService::class);
            $result = $commissionService->processPayout($affiliateId, $availableBalance, 'bank_transfer', [
                'bank_name' => $affiliate['bank_name'] ?? '',
                'account_number' => $affiliate['bank_account_number'] ?? '',
                'account_name' => $affiliate['bank_account_name'] ?? '',
            ]);
            
            if ($result && !isset($result['error'])) {
                return back()->with('success', 'Payout of &#8358;' . number_format($availableBalance) . ' has been initiated.');
            }
            return back()->with('error', $result['error'] ?? 'Failed to process payout.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function updateBankDetails(Request $request)
    {
        $affiliateId = session('affiliate_id');
        if (!$affiliateId) {
            return redirect('/affiliate/login');
        }
        
        try {
            $pdo = db_pdo();
            
            $stmt = $pdo->prepare("UPDATE affiliates SET bank_name = ?, bank_account_number = ?, bank_account_name = ? WHERE id = ?");
            $stmt->execute([
                $request->bank_name,
                $request->bank_account_number,
                $request->bank_account_name,
                $affiliateId
            ]);
            
            return back()->with('success', 'Bank details updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}