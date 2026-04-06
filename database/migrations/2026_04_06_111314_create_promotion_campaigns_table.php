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
        Schema::create('promotion_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('status', 20)->default('draft');
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 10, 2);
            $table->json('target_plan_codes')->nullable();
            $table->boolean('is_stackable_with_coupon')->default(true);
            $table->unsignedSmallInteger('priority')->default(100);
            $table->string('lifecycle_policy', 30)->default('next_renewal');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_campaigns');
    }
};
