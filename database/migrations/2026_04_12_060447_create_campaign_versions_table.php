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
        Schema::create('campaign_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_campaign_id')->constrained('promotion_campaigns')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('name', 120);
            $table->string('status', 20);
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 10, 2);
            $table->json('target_plan_codes')->nullable();
            $table->boolean('is_stackable_with_coupon')->default(false);
            $table->unsignedSmallInteger('priority')->default(100);
            $table->string('lifecycle_policy', 30)->default('next_renewal');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('changed_by_super_admin_id')->nullable()->constrained('super_admins')->nullOnDelete();
            $table->timestamps();

            $table->unique(['promotion_campaign_id', 'version_number']);
            $table->index(['promotion_campaign_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_versions');
    }
};
