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
        Schema::create('subscription_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('university_id')->index();
            $table->string('recipient_email');
            $table->string('notification_type');
            $table->string('notification_key')->nullable()->unique();
            $table->string('subject');
            $table->json('details')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_notification_logs');
    }
};
