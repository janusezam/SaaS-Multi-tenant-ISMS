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
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->string('branding_logo_path')->nullable()->after('use_custom_theme');
            $table->string('login_brand_badge', 120)->nullable()->after('branding_logo_path');
            $table->string('login_brand_heading', 160)->nullable()->after('login_brand_badge');
            $table->text('login_brand_description')->nullable()->after('login_brand_heading');
            $table->string('login_brand_feature_1', 160)->nullable()->after('login_brand_description');
            $table->string('login_brand_feature_2', 160)->nullable()->after('login_brand_feature_1');
            $table->string('login_brand_feature_3', 160)->nullable()->after('login_brand_feature_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->dropColumn([
                'branding_logo_path',
                'login_brand_badge',
                'login_brand_heading',
                'login_brand_description',
                'login_brand_feature_1',
                'login_brand_feature_2',
                'login_brand_feature_3',
            ]);
        });
    }
};
