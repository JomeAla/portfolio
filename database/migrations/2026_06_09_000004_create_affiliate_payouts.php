<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('affiliate_payouts')) {
            Schema::create('affiliate_payouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->string('method')->default('bank_transfer');
                $table->string('status')->default('pending');
                $table->string('reference')->nullable();
                $table->text('bank_details')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->index('affiliate_id');
                $table->index('status');
            });
        }

        if (!Schema::hasColumn('affiliates', 'bank_name')) {
            Schema::table('affiliates', function (Blueprint $table) {
                $table->string('bank_name')->nullable()->after('total_paid');
                $table->string('bank_account_number')->nullable()->after('bank_name');
                $table->string('bank_account_name')->nullable()->after('bank_account_number');
                $table->decimal('min_payout', 10, 2)->default(5000.00)->after('bank_account_name');
                $table->decimal('commission_rate', 5, 2)->default(20.00)->after('min_payout');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('affiliates', 'bank_name')) {
            Schema::table('affiliates', function (Blueprint $table) {
                $table->dropColumn(['bank_name', 'bank_account_number', 'bank_account_name', 'min_payout', 'commission_rate']);
            });
        }
        Schema::dropIfExists('affiliate_payouts');
    }
};