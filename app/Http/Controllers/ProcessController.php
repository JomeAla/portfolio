<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use App\Models\EmailQueue;

class ProcessController extends Controller
{
    public function processEmails()
    {
        try {
            $exitCode = Artisan::call('email:process', ['--limit' => 20]);
            $output = Artisan::output();

            return response()->json([
                'status' => $exitCode === 0 ? 'ok' : 'error',
                'output' => trim($output),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function processAutomation()
    {
        try {
            $exitCode = Artisan::call('automation:process-workflows', ['--limit' => 20]);
            $output = Artisan::output();

            return response()->json([
                'status' => $exitCode === 0 ? 'ok' : 'error',
                'output' => trim($output),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function emailQueueStatus()
    {
        $stats = [
            'pending' => EmailQueue::where('status', 'pending')->count(),
            'sent' => EmailQueue::where('status', 'sent')->count(),
            'failed' => EmailQueue::where('status', 'failed')->count(),
            'processing' => EmailQueue::where('status', 'processing')->count(),
            'total' => EmailQueue::count(),
        ];

        $dueCount = EmailQueue::where('status', 'pending')
            ->where('scheduled_send_time', '<=', now())
            ->count();

        return response()->json([
            'status' => 'ok',
            'stats' => $stats,
            'due_now' => $dueCount,
        ]);
    }
}
