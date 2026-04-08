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
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->unique();
            $table->string('brand_primary_color', 7)->default('#06b6d4');
            $table->string('brand_secondary_color', 7)->default('#6366f1');
            $table->string('theme_preference', 16)->default('system');
            $table->text('privacy_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
