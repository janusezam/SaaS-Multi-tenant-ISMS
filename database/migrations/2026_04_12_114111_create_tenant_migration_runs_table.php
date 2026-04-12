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
        Schema::create('tenant_migration_runs', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('running');
            $table->string('triggered_by')->default('console');
            $table->json('options')->nullable();
            $table->unsignedInteger('target_tenant_count')->default(0);
            $table->unsignedInteger('successful_tenant_count')->default(0);
            $table->unsignedInteger('failed_tenant_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_migration_runs');
    }
};
