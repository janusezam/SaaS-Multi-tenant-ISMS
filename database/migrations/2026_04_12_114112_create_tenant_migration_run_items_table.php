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
        Schema::create('tenant_migration_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_migration_run_id')
                ->constrained('tenant_migration_runs')
                ->cascadeOnDelete();
            $table->string('tenant_id');
            $table->string('tenant_name')->nullable();
            $table->string('status')->default('running');
            $table->longText('migration_output')->nullable();
            $table->longText('rollback_output')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_migration_run_id', 'tenant_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_migration_run_items');
    }
};
