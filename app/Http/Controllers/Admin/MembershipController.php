<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\SubscriptionPlan;
use App\Services\PaystackSubscriptionService;
use PDO;

class MembershipController extends Controller
{
    public function notifications()
    {
        $pdo = db_pdo();
        $stmt = $pdo->query("SELECT * FROM customer_notifications ORDER BY created_at DESC LIMIT 100");
        $notifications = $stmt->fetchAll();
        return view('admin.notifications.index', compact('notifications'));
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'title' => 'required',
            'message' => 'required',
            'scope' => 'required'
        ]);

        $pdo = db_pdo();
        
        if ($request->scope === 'all') {
            $stmt = $pdo->query("SELECT DISTINCT email FROM customer_accounts");
            $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } elseif ($request->scope === 'customers') {
            $stmt = $pdo->query("SELECT DISTINCT customer_email FROM orders WHERE payment_status = 'paid'");
            $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            return back()->with('error', 'Invalid scope selected');
        }

        $insert = $pdo->prepare("INSERT INTO customer_notifications (customer_email, type, title, message, link, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        
        $count = 0;
        foreach ($emails as $email) {
            try {
                $link = $request->link ?? '';
                $insert->execute([$email, $request->type, $request->title, $request->message, $link]);
                $count++;
            } catch (\Exception $e) {}
        }

        return back()->with('success', "Notification sent to $count customers!");
    }

    public function courses()
    {
        $pdo = db_pdo();
        $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
        $courses = $stmt->fetchAll();
        return view('admin.courses.index', compact('courses'));
    }

    public function createCourse()
    {
        return view('admin.courses.create');
    }

    public function storeCourse(Request $request)
    {
        $pdo = db_pdo();
        $stmt = $pdo->prepare("INSERT INTO courses (title, slug, description, image, price, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $slug = strtolower(str_replace(' ', '-', $request->title));
        $stmt->execute([$request->title, $slug, $request->description, $request->image ?? '', $request->price ?? 0, $request->is_active ? 1 : 0]);
        return redirect('/admin/courses')->with('success', 'Course created!');
    }

public function editCourse($id)
    {
        $pdo = db_pdo();
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get lessons
        $lessonsStmt = $pdo->prepare("SELECT * FROM course_lessons WHERE course_id = ? ORDER BY lesson_order");
        $lessonsStmt->execute([$id]);
        $lessons = $lessonsStmt->fetchAll(PDO::FETCH_OBJ);
        
        return view('admin.courses.edit', compact('course', 'lessons'));
    }

    public function updateCourse(Request $request, $id)
    {
        $pdo = db_pdo();
        $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, image = ?, price = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$request->title, $request->description, $request->image ?? '', $request->price ?? 0, $request->is_active ? 1 : 0, $id]);
        return redirect('/admin/courses')->with('success', 'Course updated!');
    }

    public function destroyCourse($id)
    {
        $pdo = db_pdo();
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        return back()->with('success', 'Course deleted!');
    }

    public function showCourse($id)
    {
        $pdo = db_pdo();
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $lessonsStmt = $pdo->prepare("SELECT * FROM course_lessons WHERE course_id = ? ORDER BY lesson_order");
        $lessonsStmt->execute([$id]);
        $lessons = $lessonsStmt->fetchAll(PDO::FETCH_OBJ);
        
        $enrollStmt = $pdo->prepare("SELECT COUNT(*) as total FROM course_enrollments WHERE course_id = ?");
        $enrollStmt->execute([$id]);
        $enrollment = $enrollStmt->fetch(PDO::FETCH_ASSOC);
        
        return view('admin.courses.show', compact('course', 'lessons', 'enrollment'));
    }

    // Course Lessons
    public function addLesson(Request $request)
    {
        $pdo = db_pdo();
        $stmt = $pdo->prepare("INSERT INTO course_lessons (course_id, title, description, video_url, lesson_order, is_published, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $request->course_id,
            $request->title,
            $request->description ?? '',
            $request->video_url ?? '',
            $request->lesson_order ?? 0,
            $request->is_published ? 1 : 0
        ]);
        return back()->with('success', 'Lesson added!');
    }

    public function deleteLesson($id)
    {
        $pdo = db_pdo();
        $stmt = $pdo->prepare("DELETE FROM course_lessons WHERE id = ?");
        $stmt->execute([$id]);
        return back()->with('success', 'Lesson deleted!');
    }

    public function tiers()
    {
        $pdo = db_pdo();
        $stmt = $pdo->query("SELECT * FROM membership_tiers ORDER BY sort_order, price");
        $tiers = $stmt->fetchAll(PDO::FETCH_OBJ);
        return view('admin.membership.tiers.index', compact('tiers'));
    }

    public function createTier()
    {
        return view('admin.membership.tiers.create');
    }

    public function storeTier(Request $request)
    {
        $pdo = db_pdo();
        $stmt = $pdo->prepare("INSERT INTO membership_tiers (name, description, price, billing_period, features, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$request->name, $request->description, $request->price, $request->billing_period ?? 'monthly', $request->features, $request->is_active ? 1 : 0]);

        $features = $request->features
            ? array_filter(array_map('trim', explode("\n", $request->features)))
            : [];
        $interval = match($request->billing_period ?? 'monthly') {
            'yearly' => 'yearly', 'quarterly' => 'quarterly', 'weekly' => 'weekly',
            default => 'monthly',
        };
        $plan = SubscriptionPlan::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description ?? '',
            'price' => $request->price ?? 0,
            'interval' => $interval,
            'features' => $features,
            'is_active' => $request->has('is_active'),
        ]);
        try { app(PaystackSubscriptionService::class)->createPlan($plan); } catch (\Exception $e) {}

        return redirect('/admin/membership/tiers')->with('success', 'Tier created!');
    }

    public function editTier($id)
    {
        $pdo = db_pdo();
        $stmt = $pdo->prepare("SELECT * FROM membership_tiers WHERE id = ?");
        $stmt->execute([$id]);
        $tier = $stmt->fetch();
        return view('admin.membership.tiers.edit', compact('tier'));
    }

    public function updateTier(Request $request, $id)
    {
        $pdo = db_pdo();
        $stmt = $pdo->prepare("UPDATE membership_tiers SET name = ?, description = ?, price = ?, billing_period = ?, features = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$request->name, $request->description, $request->price, $request->billing_period ?? 'monthly', $request->features, $request->is_active ? 1 : 0, $id]);

        $features = $request->features
            ? array_filter(array_map('trim', explode("\n", $request->features)))
            : [];
        $interval = match($request->billing_period ?? 'monthly') {
            'yearly' => 'yearly', 'quarterly' => 'quarterly', 'weekly' => 'weekly',
            default => 'monthly',
        };
        $plan = SubscriptionPlan::updateOrCreate(
            ['slug' => Str::slug($request->name)],
            [
                'name' => $request->name,
                'description' => $request->description ?? '',
                'price' => $request->price ?? 0,
                'interval' => $interval,
                'features' => $features,
                'is_active' => $request->has('is_active'),
            ]
        );
        if (!$plan->paystack_plan_code) {
            try { app(PaystackSubscriptionService::class)->createPlan($plan); } catch (\Exception $e) {}
        }

        return redirect('/admin/membership/tiers')->with('success', 'Tier updated!');
    }

    public function destroyTier($id)
    {
        $pdo = db_pdo();
        $stmt = $pdo->prepare("SELECT name FROM membership_tiers WHERE id = ?");
        $stmt->execute([$id]);
        $tier = $stmt->fetch();
        if ($tier) {
            SubscriptionPlan::where('slug', Str::slug($tier['name']))->delete();
        }
        $stmt = $pdo->prepare("DELETE FROM membership_tiers WHERE id = ?");
        $stmt->execute([$id]);
        return back()->with('success', 'Tier deleted!');
    }
}