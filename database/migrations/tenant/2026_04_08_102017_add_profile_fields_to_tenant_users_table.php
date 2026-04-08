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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('position_title', 120)->nullable()->after('phone');
            $table->string('profile_photo_path')->nullable()->after('position_title');
            $table->text('bio')->nullable()->after('profile_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'position_title', 'profile_photo_path', 'bio']);
        });
    }
};
