<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Achievement;

class AchievementService
{
    public function checkAndAward(string $email): array
    {
        return Achievement::checkAndAward($email);
    }

    public function getAchievementsForCustomer(string $email): array
    {
        $allAchievements = Achievement::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $awardedIds = DB::table('customer_achievements')
            ->where('customer_email', $email)
            ->where('awarded', true)
            ->pluck('achievement_id')
            ->toArray();

        $result = [];
        foreach ($allAchievements as $achievement) {
            $isAwarded = in_array($achievement->id, $awardedIds);
            $awardedAt = null;

            if ($isAwarded) {
                $record = DB::table('customer_achievements')
                    ->where('customer_email', $email)
                    ->where('achievement_id', $achievement->id)
                    ->first();
                $awardedAt = $record?->awarded_at;
            }

            $result[] = [
                'id' => $achievement->id,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'icon' => $achievement->icon,
                'badge_color' => $achievement->badge_color,
                'points' => $achievement->points,
                'trigger_type' => $achievement->trigger_type,
                'trigger_value' => $achievement->trigger_value,
                'is_awarded' => $isAwarded,
                'awarded_at' => $awardedAt,
            ];
        }

        return $result;
    }

    public function getTotalPoints(string $email): int
    {
        return DB::table('customer_achievements')
            ->where('customer_email', $email)
            ->where('awarded', true)
            ->join('achievements', 'achievements.id', '=', 'customer_achievements.achievement_id')
            ->sum('achievements.points');
    }

    public function getProgressForTrigger(string $email, string $triggerType): array
    {
        switch ($triggerType) {
            case 'total_spent':
                $total = DB::table('orders')
                    ->where('customer_email', $email)
                    ->where('payment_status', 'success')
                    ->sum('final_amount');
                return ['current' => $total, 'unit' => 'NGN'];

            case 'total_orders':
                $count = DB::table('orders')
                    ->where('customer_email', $email)
                    ->where('payment_status', 'success')
                    ->count();
                return ['current' => $count, 'unit' => 'orders'];

            case 'referral_count':
                $code = DB::table('customer_referrals')
                    ->where('customer_email', $email)
                    ->value('referral_code');
                $count = $code
                    ? DB::table('referral_uses')->where('referral_code', $code)->where('status', 'completed')->count()
                    : 0;
                return ['current' => $count, 'unit' => 'referrals'];

            case 'courses_enrolled':
                $count = DB::table('course_enrollments')
                    ->where('customer_email', $email)
                    ->count();
                return ['current' => $count, 'unit' => 'courses'];

            default:
                return ['current' => 0, 'unit' => ''];
        }
    }

    public function onOrderSuccess(string $email, float $amount): void
    {
        $newAchievements = $this->checkAndAward($email);

        foreach ($newAchievements as $achievement) {
            $this->notifyAchievement($email, $achievement);
        }
    }

    public function onCourseComplete(string $email, int $courseId): void
    {
        $newAchievements = $this->checkAndAward($email);

        foreach ($newAchievements as $achievement) {
            $this->notifyAchievement($email, $achievement);
        }
    }

    private function notifyAchievement(string $email, $achievement): void
    {
        try {
            $title = is_object($achievement) ? $achievement->name : 'Achievement Unlocked!';
            $message = is_object($achievement) ? $achievement->description : 'You earned a new achievement!';

            DB::table('customer_notifications')->insert([
                'customer_email' => $email,
                'type' => 'achievement',
                'title' => '🏆 ' . $title,
                'message' => $message,
                'link' => '/customer/achievements',
                'is_read' => false,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('AchievementService: Failed to notify: ' . $e->getMessage());
        }
    }
}