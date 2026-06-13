<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'badge_color',
        'trigger_type', 'trigger_value', 'points', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public static function triggerTypes(): array
    {
        return [
            'first_purchase' => 'First Purchase',
            'total_spent' => 'Total Amount Spent',
            'total_orders' => 'Number of Orders',
            'referral_count' => 'Number of Referrals',
            'course_completed' => 'Course Completed',
            'account_age_days' => 'Account Age (Days)',
            'courses_enrolled' => 'Courses Enrolled',
            'achievement_count' => 'Number of Achievements',
        ];
    }

    public static function checkAndAward(string $email): array
    {
        $awarded = [];

        $activeAchievements = self::where('is_active', true)->get();

        foreach ($activeAchievements as $achievement) {
            $existing = \DB::table('customer_achievements')
                ->where('customer_email', $email)
                ->where('achievement_id', $achievement->id)
                ->first();

            if ($existing && $existing->awarded) {
                continue;
            }

            if (self::meetsCondition($email, $achievement)) {
                \DB::table('customer_achievements')->updateOrInsert(
                    ['customer_email' => $email, 'achievement_id' => $achievement->id],
                    ['awarded' => true, 'awarded_at' => now(), 'updated_at' => now()]
                );
                $awarded[] = $achievement;
            }
        }

        return $awarded;
    }

    private static function meetsCondition(string $email, self $achievement): bool
    {
        $value = $achievement->trigger_value;

        switch ($achievement->trigger_type) {
            case 'first_purchase':
                return \DB::table('orders')
                    ->where('customer_email', $email)
                    ->where('payment_status', 'success')
                    ->exists();

            case 'total_spent':
                $total = \DB::table('orders')
                    ->where('customer_email', $email)
                    ->where('payment_status', 'success')
                    ->sum('final_amount');
                return $total >= (float) $value;

            case 'total_orders':
                $count = \DB::table('orders')
                    ->where('customer_email', $email)
                    ->where('payment_status', 'success')
                    ->count();
                return $count >= (int) $value;

            case 'referral_count':
                $count = \DB::table('referral_uses')
                    ->where('referral_code', function($q) use ($email) {
                        $q->select('referral_code')
                            ->from('customer_referrals')
                            ->where('customer_email', $email)
                            ->limit(1);
                    })
                    ->where('status', 'completed')
                    ->count();
                return $count >= (int) $value;

            case 'course_completed':
                return \DB::table('course_enrollments')
                    ->where('customer_email', $email)
                    ->where('course_id', (int) $value)
                    ->where('progress', '>=', 100)
                    ->exists();

            case 'account_age_days':
                $customer = \DB::table('customer_accounts')
                    ->where('email', $email)
                    ->first();
                if (!$customer) return false;
                return now()->diffInDays($customer->created_at) >= (int) $value;

            case 'courses_enrolled':
                $count = \DB::table('course_enrollments')
                    ->where('customer_email', $email)
                    ->count();
                return $count >= (int) $value;

            case 'achievement_count':
                $count = \DB::table('customer_achievements')
                    ->where('customer_email', $email)
                    ->where('awarded', true)
                    ->count();
                return $count >= (int) ($value - 1);

            default:
                return false;
        }
    }

    public static function seedDefaults(): void
    {
        $defaults = [
            ['name' => 'First Purchase', 'slug' => 'first-purchase', 'description' => 'Made your first purchase', 'icon' => 'fa-shopping-bag', 'badge_color' => 'emerald', 'trigger_type' => 'first_purchase', 'trigger_value' => '1', 'points' => 10, 'sort_order' => 1],
            ['name' => 'Big Spender', 'slug' => 'big-spender', 'description' => 'Spent over ₦50,000 total', 'icon' => 'fa-naira-sign', 'badge_color' => 'amber', 'trigger_type' => 'total_spent', 'trigger_value' => '50000', 'points' => 25, 'sort_order' => 2],
            ['name' => 'Repeat Customer', 'slug' => 'repeat-customer', 'description' => 'Made 3 or more purchases', 'icon' => 'fa-receipt', 'badge_color' => 'blue', 'trigger_type' => 'total_orders', 'trigger_value' => '3', 'points' => 15, 'sort_order' => 3],
            ['name' => 'Referral Master', 'slug' => 'referral-master', 'description' => 'Referred 5 friends who made a purchase', 'icon' => 'fa-users', 'badge_color' => 'purple', 'trigger_type' => 'referral_count', 'trigger_value' => '5', 'points' => 30, 'sort_order' => 4],
            ['name' => 'Course Graduate', 'slug' => 'course-graduate', 'description' => 'Completed a course', 'icon' => 'fa-graduation-cap', 'badge_color' => 'indigo', 'trigger_type' => 'courses_enrolled', 'trigger_value' => '1', 'points' => 20, 'sort_order' => 5],
            ['name' => 'Loyal Member', 'slug' => 'loyal-member', 'description' => 'Account active for 30+ days', 'icon' => 'fa-heart', 'badge_color' => 'rose', 'trigger_type' => 'account_age_days', 'trigger_value' => '30', 'points' => 10, 'sort_order' => 6],
            ['name' => 'Premium Buyer', 'slug' => 'premium-buyer', 'description' => 'Spent over ₦200,000 total', 'icon' => 'fa-crown', 'badge_color' => 'yellow', 'trigger_type' => 'total_spent', 'trigger_value' => '200000', 'points' => 50, 'sort_order' => 7],
            ['name' => 'Whale', 'slug' => 'whale', 'description' => 'Spent over ₦500,000 total', 'icon' => 'fa-gem', 'badge_color' => 'violet', 'trigger_type' => 'total_spent', 'trigger_value' => '500000', 'points' => 100, 'sort_order' => 8],
        ];

        foreach ($defaults as $achievement) {
            self::updateOrCreate(
                ['slug' => $achievement['slug']],
                $achievement
            );
        }
    }
}