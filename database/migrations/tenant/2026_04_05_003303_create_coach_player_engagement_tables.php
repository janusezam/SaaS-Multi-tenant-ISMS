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
        Schema::create('game_team_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->timestamp('coach_confirmed_at')->nullable();
            $table->foreignId('coach_confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('coach_note')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'team_id']);
        });

        Schema::create('game_player_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->boolean('is_starter')->default(false);
            $table->string('attendance_status')->default('pending');
            $table->dateTime('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'player_id']);
            $table->index(['team_id', 'game_id']);
        });

        Schema::create('team_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_announcements');
        Schema::dropIfExists('game_player_assignments');
        Schema::dropIfExists('game_team_participations');
    }
};
