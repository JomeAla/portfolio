<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\OrderController;
use App\Models\Lead;
use App\Models\Tag;
use Illuminate\Support\Facades\Log;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/initiate-payment', [OrderController::class, 'initiatePayment']);
Route::post('/ecom-init', [OrderController::class, 'ecomInitPayment']);

Route::post('/submit-lead', function (Request $request) {
    try {
        $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
        ]);
        
        $source = $request->input('source', 'email_checklist_lead_magnet');
        
        // Create or get lead
        $lead = Lead::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->name ?? 'Subscriber',
                'source' => $source,
                'score' => 10,
            ]
        );
        
        // Enroll in nurture sequence if not already enrolled
        if (!$lead->sequence_id) {
            $sequence = \App\Models\EmailSequence::where('name', 'Checklist Lead Magnet Nurture')->first();
            if ($sequence) {
                \App\Models\Sequence::firstOrCreate(['id' => $sequence->id], [
                    'name' => $sequence->name,
                    'is_active' => true,
                ]);
                $lead->sequence_id = $sequence->id;
                $lead->save();
                
                // Queue the first email (download link) immediately
                $steps = $sequence->steps()->orderBy('step_order')->orderBy('id')->get();
                foreach ($steps as $step) {
                    $scheduledTime = now()->addHours($step->delay_days * 24);
                    \App\Models\EmailQueue::create([
                        'lead_id' => $lead->id,
                        'sequence_step_id' => $step->id,
                        'scheduled_send_time' => $scheduledTime,
                        'status' => 'pending',
                    ]);
                }
                
                Log::info("Lead " . $lead->email . " enrolled in sequence " . $sequence->id);
            }
        }
        
        Log::info("New lead: " . $request->email . " - ID:" . $lead->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Check your inbox for the download link!'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});
