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
        Schema::create('tenant_runtime_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('route_name')->nullable();
            $table->string('request_path', 255);
            $table->string('request_method', 12);
            $table->unsignedSmallInteger('status_code');
            $table->unsignedInteger('response_time_ms');
            $table->unsignedInteger('db_time_ms')->default(0);
            $table->unsignedInteger('memory_peak_mb')->default(0);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['tenant_id', 'recorded_at']);
            $table->index(['recorded_at']);
            $table->index(['status_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_runtime_metrics');
    }
};
