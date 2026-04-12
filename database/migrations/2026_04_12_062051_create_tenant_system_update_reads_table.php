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
        Schema::create('tenant_system_update_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_update_id')->constrained()->cascadeOnDelete();
            $table->string('tenant_id', 100);
            $table->unsignedBigInteger('tenant_user_id')->nullable();
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['system_update_id', 'tenant_id', 'tenant_user_id'], 'tenant_update_reads_unique');
            $table->index(['tenant_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_system_update_reads');
    }
};
