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
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 80);
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('yearly_price', 10, 2);
            $table->decimal('yearly_discount_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(10);
            $table->timestamps();
        });

        DB::table('plans')->insert([
            [
                'code' => 'basic',
                'name' => 'Basic',
                'monthly_price' => 19,
                'yearly_price' => 190,
                'yearly_discount_percent' => 16.67,
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'pro',
                'name' => 'Pro',
                'monthly_price' => 49,
                'yearly_price' => 490,
                'yearly_discount_percent' => 16.67,
                'is_active' => true,
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
