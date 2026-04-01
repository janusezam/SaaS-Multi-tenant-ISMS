<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = 'subscription_upgrade_requests';

        $hasBillingCycle = Schema::hasColumn($tableName, 'billing_cycle');
        $hasCouponId = Schema::hasColumn($tableName, 'coupon_id');
        $hasCouponCode = Schema::hasColumn($tableName, 'coupon_code');
        $hasBasePrice = Schema::hasColumn($tableName, 'base_price');
        $hasDiscountAmount = Schema::hasColumn($tableName, 'discount_amount');
        $hasFinalPrice = Schema::hasColumn($tableName, 'final_price');
        $hasRequestedByUserId = Schema::hasColumn($tableName, 'requested_by_user_id');
        $hasProcessedBySuperAdminId = Schema::hasColumn($tableName, 'processed_by_super_admin_id');
        $hasRejectedAt = Schema::hasColumn($tableName, 'rejected_at');
        $hasRejectionReason = Schema::hasColumn($tableName, 'rejection_reason');
        $hasPricingSnapshot = Schema::hasColumn($tableName, 'pricing_snapshot');

        Schema::table($tableName, function (Blueprint $table) use (
            $hasBillingCycle,
            $hasCouponId,
            $hasCouponCode,
            $hasBasePrice,
            $hasDiscountAmount,
            $hasFinalPrice,
            $hasRequestedByUserId,
            $hasProcessedBySuperAdminId,
            $hasRejectedAt,
            $hasRejectionReason,
            $hasPricingSnapshot,
        ): void {
            if (! $hasBillingCycle) {
                $table->string('billing_cycle', 20)->default('monthly')->after('requested_plan');
            }

            if (! $hasCouponId) {
                $table->unsignedBigInteger('coupon_id')->nullable()->after('billing_cycle');
            }

            if (! $hasCouponCode) {
                $table->string('coupon_code', 80)->nullable()->after('coupon_id');
            }

            if (! $hasBasePrice) {
                $table->decimal('base_price', 10, 2)->nullable()->after('coupon_code');
            }

            if (! $hasDiscountAmount) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('base_price');
            }

            if (! $hasFinalPrice) {
                $table->decimal('final_price', 10, 2)->nullable()->after('discount_amount');
            }

            if (! $hasRequestedByUserId) {
                $table->unsignedBigInteger('requested_by_user_id')->nullable()->after('requested_by_email');
            }

            if (! $hasProcessedBySuperAdminId) {
                $table->unsignedBigInteger('processed_by_super_admin_id')->nullable()->after('approved_at');
            }

            if (! $hasRejectedAt) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }

            if (! $hasRejectionReason) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }

            if (! $hasPricingSnapshot) {
                $table->json('pricing_snapshot')->nullable()->after('rejection_reason');
            }
        });

        Schema::table($tableName, function (Blueprint $table): void {
            if (! $this->constraintExists('sub_up_req_coupon_fk')) {
                $table->foreign('coupon_id', 'sub_up_req_coupon_fk')->references('id')->on('coupons')->nullOnDelete();
            }

            if (! $this->constraintExists('sub_up_req_super_admin_fk')) {
                $table->foreign('processed_by_super_admin_id', 'sub_up_req_super_admin_fk')->references('id')->on('super_admins')->nullOnDelete();
            }

            if (! $this->indexExists('sub_up_req_status_plan_idx')) {
                $table->index(['tenant_id', 'status', 'requested_plan'], 'sub_up_req_status_plan_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_upgrade_requests', function (Blueprint $table) {
            if ($this->constraintExists('sub_up_req_coupon_fk')) {
                $table->dropForeign('sub_up_req_coupon_fk');
            }

            if ($this->constraintExists('sub_up_req_super_admin_fk')) {
                $table->dropForeign('sub_up_req_super_admin_fk');
            }

            if ($this->indexExists('sub_up_req_status_plan_idx')) {
                $table->dropIndex('sub_up_req_status_plan_idx');
            }

            $columns = array_filter([
                Schema::hasColumn('subscription_upgrade_requests', 'billing_cycle') ? 'billing_cycle' : null,
                Schema::hasColumn('subscription_upgrade_requests', 'coupon_id') ? 'coupon_id' : null,
                Schema::hasColumn('subscription_upgrade_requests', 'coupon_code') ? 'coupon_code' : null,
                Schema::hasColumn('subscription_upgrade_requests', 'base_price') ? 'base_price' : null,
                Schema::hasColumn('subscription_upgrade_requests', 'discount_amount') ? 'discount_amount' : null,
                Schema::hasColumn('subscription_upgrade_requests', 'final_price') ? 'final_price' : null,
                Schema::hasColumn('subscription_upgrade_requests', 'requested_by_user_id') ? 'requested_by_user_id' : null,
                Schema::hasColumn('subscription_upgrade_requests', 'processed_by_super_admin_id') ? 'processed_by_super_admin_id' : null,
                Schema::hasColumn('subscription_upgrade_requests', 'rejected_at') ? 'rejected_at' : null,
                Schema::hasColumn('subscription_upgrade_requests', 'rejection_reason') ? 'rejection_reason' : null,
                Schema::hasColumn('subscription_upgrade_requests', 'pricing_snapshot') ? 'pricing_snapshot' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    private function constraintExists(string $constraintName): bool
    {
        $driver = DB::getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return false;
        }

        $databaseName = DB::getDatabaseName();

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', $databaseName)
            ->where('TABLE_NAME', 'subscription_upgrade_requests')
            ->where('CONSTRAINT_NAME', $constraintName)
            ->exists();
    }

    private function indexExists(string $indexName): bool
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $databaseName = DB::getDatabaseName();

            return DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', $databaseName)
                ->where('TABLE_NAME', 'subscription_upgrade_requests')
                ->where('INDEX_NAME', $indexName)
                ->exists();
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('subscription_upgrade_requests')");

            return collect($indexes)
                ->pluck('name')
                ->contains($indexName);
        }

        return false;

    }
};
