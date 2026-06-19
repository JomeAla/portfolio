<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('message');
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'failed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->json('log')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->string('phone', 20);
            $table->boolean('opted_in')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
            $table->unique(['lead_id', 'phone']);
        });

        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'phone')) {
                $table->string('phone', 20)->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'phone')) {
                $table->dropColumn('phone');
            }
        });
        Schema::dropIfExists('whatsapp_contacts');
        Schema::dropIfExists('whatsapp_broadcasts');
    }
};
