<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->unsignedInteger('max_users')->nullable()->after('yearly_discount_percent');
            $table->unsignedInteger('max_teams')->nullable()->after('max_users');
            $table->unsignedInteger('max_sports')->nullable()->after('max_teams');
        });

        DB::table('plans')->where('code', 'basic')->update([
            'max_users' => 30,
            'max_teams' => 12,
            'max_sports' => 8,
        ]);

        DB::table('plans')->where('code', 'pro')->update([
            'max_users' => 150,
            'max_teams' => 60,
            'max_sports' => 25,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn([
                'max_users',
                'max_teams',
                'max_sports',
            ]);
        });
    }
};
