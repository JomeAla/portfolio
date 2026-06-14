<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class FunnelMigration extends Command
{
    protected $signature = 'funnel:migrate';
    protected $description = 'Run funnel enhancements migration';

    public function handle()
    {
        try {
            DB::connection()->getPdo();
            $this->info("Connected to database.");

            if (!Schema::hasTable('funnel_leads')) {
                $this->info("Creating funnel_leads table...");
                Schema::create('funnel_leads', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('funnel_id')->nullable();
                    $table->unsignedBigInteger('lead_id')->nullable();
                    $table->unsignedBigInteger('stage_id')->nullable();
                    $table->string('email')->nullable();
                    $table->string('source')->nullable();
                    $table->boolean('converted')->default(false);
                    $table->datetime('entered_at')->nullable();
                    $table->datetime('exited_at')->nullable();
                    $table->integer('score')->default(0);
                    $table->datetime('last_activity')->nullable();
                    $table->integer('times_visited')->default(0);
                    $table->integer('pages_viewed')->default(0);
                    $table->integer('email_opens')->default(0);
                    $table->timestamps();
                });
                $this->info("Created funnel_leads table.");
            }

            $this->addFunnelColumns();
            $this->addFunnelLeadColumns();

            $this->info("Migration complete!");
        } catch (\Exception $e) {
            $this->error("Migration error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function addFunnelColumns(): void
    {
        $columns = [
            'order_bumps' => function (Blueprint $table) {
                $table->json('order_bumps')->nullable();
            },
            'refund_policy' => function (Blueprint $table) {
                $table->string('refund_policy', 50)->default('days');
            },
            'refund_period_days' => function (Blueprint $table) {
                $table->integer('refund_period_days')->default(30);
            },
            'affiliate_enabled' => function (Blueprint $table) {
                $table->boolean('affiliate_enabled')->default(false);
            },
            'affiliate_commission' => function (Blueprint $table) {
                $table->decimal('affiliate_commission', 5, 2)->default(20.00);
            },
            'affiliate_cookie_days' => function (Blueprint $table) {
                $table->integer('affiliate_cookie_days')->default(30);
            },
            'score_per_page' => function (Blueprint $table) {
                $table->integer('score_per_page')->default(5);
            },
            'score_per_email' => function (Blueprint $table) {
                $table->integer('score_per_email')->default(10);
            },
            'score_per_checkout' => function (Blueprint $table) {
                $table->integer('score_per_checkout')->default(20);
            },
            'score_hot_threshold' => function (Blueprint $table) {
                $table->integer('score_hot_threshold')->default(100);
            },
        ];

        $this->info("Adding columns to funnels table...");
        Schema::table('funnels', function (Blueprint $table) use ($columns) {
            foreach ($columns as $name => $definition) {
                if (!Schema::hasColumn('funnels', $name)) {
                    $definition($table);
                    $this->line("Added $name to funnels");
                }
            }
        });
    }

    protected function addFunnelLeadColumns(): void
    {
        $columns = [
            'score' => function (Blueprint $table) {
                $table->integer('score')->default(0);
            },
            'last_activity' => function (Blueprint $table) {
                $table->datetime('last_activity')->nullable();
            },
            'times_visited' => function (Blueprint $table) {
                $table->integer('times_visited')->default(0);
            },
            'pages_viewed' => function (Blueprint $table) {
                $table->integer('pages_viewed')->default(0);
            },
            'email_opens' => function (Blueprint $table) {
                $table->integer('email_opens')->default(0);
            },
        ];

        $this->info("Adding columns to funnel_leads table...");
        Schema::table('funnel_leads', function (Blueprint $table) use ($columns) {
            foreach ($columns as $name => $definition) {
                if (!Schema::hasColumn('funnel_leads', $name)) {
                    $definition($table);
                    $this->line("Added $name to funnel_leads");
                }
            }
        });
    }
}
