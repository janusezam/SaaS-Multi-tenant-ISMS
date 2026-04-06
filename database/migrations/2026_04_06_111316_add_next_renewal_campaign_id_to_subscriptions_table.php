<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('next_renewal_campaign_id')->nullable()->after('coupon_id');

            $table->foreign('next_renewal_campaign_id')
                ->references('id')
                ->on('promotion_campaigns')
                ->nullOnDelete();

            $table->index(['status', 'plan', 'billing_cycle', 'next_renewal_campaign_id'], 'subs_status_plan_cycle_campaign_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['next_renewal_campaign_id']);
            $table->dropIndex('subs_status_plan_cycle_campaign_idx');
            $table->dropColumn('next_renewal_campaign_id');
        });
    }
};
