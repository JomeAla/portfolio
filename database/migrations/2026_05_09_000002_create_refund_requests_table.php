<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('refund_requests')) {
            Schema::create('refund_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->string('user_email');
                $table->text('reason');
                $table->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending');
                $table->decimal('amount', 10, 2);
                $table->timestamps();
                $table->timestamp('processed_at')->nullable();
                $table->text('admin_notes')->nullable();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
