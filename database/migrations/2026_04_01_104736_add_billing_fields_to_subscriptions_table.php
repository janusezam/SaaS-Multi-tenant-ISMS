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
            $table->string('billing_cycle', 20)->default('monthly')->after('plan');
            $table->decimal('base_price', 10, 2)->nullable()->after('billing_cycle');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('base_price');
            $table->decimal('final_price', 10, 2)->nullable()->after('discount_amount');
            $table->unsignedBigInteger('coupon_id')->nullable()->after('final_price');
            $table->string('coupon_code', 80)->nullable()->after('coupon_id');
            $table->json('pricing_snapshot')->nullable()->after('coupon_code');

            $table->foreign('coupon_id')->references('id')->on('coupons')->nullOnDelete();
            $table->index(['billing_cycle', 'coupon_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropIndex(['billing_cycle', 'coupon_id']);
            $table->dropColumn([
                'billing_cycle',
                'base_price',
                'discount_amount',
                'final_price',
                'coupon_id',
                'coupon_code',
                'pricing_snapshot',
            ]);
        });
    }
};
