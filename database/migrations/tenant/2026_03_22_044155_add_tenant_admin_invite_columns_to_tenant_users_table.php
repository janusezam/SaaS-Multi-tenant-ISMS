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
            $table->string('invite_token_hash', 64)->nullable()->after('must_change_password');
            $table->timestamp('invite_expires_at')->nullable()->after('invite_token_hash');
            $table->timestamp('invite_sent_at')->nullable()->after('invite_expires_at');

            $table->index('invite_token_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['invite_token_hash']);
            $table->dropColumn(['invite_token_hash', 'invite_expires_at', 'invite_sent_at']);
        });
    }
};
