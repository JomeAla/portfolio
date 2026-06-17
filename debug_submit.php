<?php
require __DIR__ . "/vendor/autoload.php";
$app = require __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $page = DB::table("landing_pages")->where("slug", "free-whatsapp-marketing-bundle")->where("is_active", true)->first();
    echo "Page: " . json_encode($page) . "\n";

    $stages = DB::table("funnel_stages")->where("funnel_id", 31)->orderBy("order")->get();
    echo "Stages: " . json_encode($stages) . "\n";

    $funnel = DB::table("funnels")->find(31);
    echo "Funnel: " . json_encode($funnel) . "\n";

    $email = "debug-" . time() . "@example.com";
    echo "Email: $email\n";

    DB::beginTransaction();
    $leadId = DB::table("leads")->insertGetId([
        "email" => $email,
        "name" => "Debug",
        "landing_page_id" => $page->id,
        "sequence_id" => $page->sequence_id,
        "source" => "landing_page",
        "created_at" => now(),
        "updated_at" => now(),
    ]);
    echo "Lead ID: $leadId\n";

    $steps = DB::table("sequence_steps")->where("sequence_id", 71)->get();
    echo "Steps: " . count($steps) . "\n";

    foreach ($steps as $step) {
        DB::table("email_queue")->insert([
            "lead_id" => $leadId,
            "sequence_step_id" => $step->id,
            "scheduled_send_time" => date("Y-m-d H:i:s", strtotime("+" . $step->delay_days . " days")),
            "status" => "pending",
            "created_at" => now(),
            "updated_at" => now(),
        ]);
    }

    $firstStage = DB::table("funnel_stages")->where("funnel_id", 31)->orderBy("order")->first();
    DB::table("funnel_leads")->insert([
        "funnel_id" => 31,
        "lead_id" => $leadId,
        "stage_id" => $firstStage->id,
        "email" => $email,
        "source" => "landing_page",
        "converted" => 0,
        "entered_at" => now(),
        "created_at" => now(),
        "updated_at" => now(),
    ]);

    DB::rollBack();
    echo "ALL OK\n";
} catch (Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
