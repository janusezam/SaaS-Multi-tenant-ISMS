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
        Schema::table('game_player_assignments', function (Blueprint $table) {
            $table->foreignId('assigned_by_user_id')->nullable()->after('player_id')->constrained('users')->nullOnDelete();
            $table->foreignId('attendance_updated_by_user_id')->nullable()->after('responded_at')->constrained('users')->nullOnDelete();

            $table->index(['team_id', 'assigned_by_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_player_assignments', function (Blueprint $table) {
            $table->dropIndex(['team_id', 'assigned_by_user_id']);
            $table->dropConstrainedForeignId('attendance_updated_by_user_id');
            $table->dropConstrainedForeignId('assigned_by_user_id');
        });
    }
};
