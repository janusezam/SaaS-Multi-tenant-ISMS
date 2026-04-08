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
        Schema::table('sports', function (Blueprint $table): void {
            $table->string('cover_photo_path')->nullable()->after('description');
        });

        Schema::table('teams', function (Blueprint $table): void {
            $table->string('logo_path')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->dropColumn('logo_path');
        });

        Schema::table('sports', function (Blueprint $table): void {
            $table->dropColumn('cover_photo_path');
        });
    }
};
