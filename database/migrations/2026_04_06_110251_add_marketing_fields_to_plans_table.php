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
        Schema::table('plans', function (Blueprint $table) {
            $table->string('marketing_tagline', 160)->nullable()->after('name');
            $table->string('badge_label', 40)->nullable()->after('marketing_tagline');
            $table->string('cta_label', 40)->nullable()->after('badge_label');
            $table->text('marketing_points')->nullable()->after('cta_label');
            $table->boolean('is_featured')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'marketing_tagline',
                'badge_label',
                'cta_label',
                'marketing_points',
                'is_featured',
            ]);
        });
    }
};
