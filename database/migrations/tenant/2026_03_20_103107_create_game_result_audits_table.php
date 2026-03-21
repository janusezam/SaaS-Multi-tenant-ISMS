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
        Schema::create('game_result_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->string('previous_status');
            $table->string('new_status');
            $table->unsignedInteger('previous_home_score')->nullable();
            $table->unsignedInteger('new_home_score')->nullable();
            $table->unsignedInteger('previous_away_score')->nullable();
            $table->unsignedInteger('new_away_score')->nullable();
            $table->timestamps();

            $table->index('changed_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_result_audits');
    }
};
