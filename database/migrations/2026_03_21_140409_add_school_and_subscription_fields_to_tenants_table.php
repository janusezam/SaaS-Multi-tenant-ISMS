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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('school_address')->nullable()->after('name');
            $table->string('tenant_admin_name')->nullable()->after('school_address');
            $table->string('tenant_admin_email')->nullable()->after('tenant_admin_name');
            $table->timestamp('subscription_starts_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'school_address',
                'tenant_admin_name',
                'tenant_admin_email',
                'subscription_starts_at',
            ]);
        });
    }
};
