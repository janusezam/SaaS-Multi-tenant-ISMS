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
        Schema::create('bracket_match_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bracket_match_id')->constrained('bracket_matches')->cascadeOnDelete();
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->foreignId('previous_winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('new_winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->timestamps();

            $table->index('changed_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bracket_match_audits');
    }
};
