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
        Schema::create('plan_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('code', 30);
            $table->string('name', 80);
            $table->string('marketing_tagline', 160)->nullable();
            $table->string('badge_label', 40)->nullable();
            $table->string('cta_label', 40)->nullable();
            $table->text('marketing_points')->nullable();
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('yearly_price', 10, 2);
            $table->decimal('yearly_discount_percent', 5, 2)->default(0);
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_teams')->nullable();
            $table->unsignedInteger('max_sports')->nullable();
            $table->json('feature_flags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order');
            $table->foreignId('changed_by_super_admin_id')->nullable()->constrained('super_admins')->nullOnDelete();
            $table->timestamps();

            $table->unique(['plan_id', 'version_number']);
            $table->index(['plan_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_versions');
    }
};
