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
        Schema::create('system_updates', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->text('summary')->nullable();
            $table->string('version', 60)->nullable();
            $table->string('source', 20)->default('manual');
            $table->boolean('is_published')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by_super_admin_id')->nullable()->constrained('super_admins')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_updates');
    }
};
