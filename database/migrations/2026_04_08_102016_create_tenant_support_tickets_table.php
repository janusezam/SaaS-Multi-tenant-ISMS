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
        Schema::create('tenant_support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('tenant_name');
            $table->unsignedBigInteger('reported_by_user_id')->nullable();
            $table->string('reported_by_name');
            $table->string('reported_by_email');
            $table->string('reported_by_role', 80)->nullable();
            $table->string('category', 40);
            $table->string('subject', 160);
            $table->text('message');
            $table->string('status', 32)->default('open')->index();
            $table->text('central_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_support_tickets');
    }
};
