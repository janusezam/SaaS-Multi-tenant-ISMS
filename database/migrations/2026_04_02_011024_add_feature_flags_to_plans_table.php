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
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'feature_flags')) {
                $table->json('feature_flags')->nullable()->after('yearly_discount_percent');
            }
        });

        DB::table('plans')->where('code', 'basic')->update([
            'feature_flags' => json_encode([
                'analytics' => false,
                'bracket' => false,
            ], JSON_THROW_ON_ERROR),
        ]);

        DB::table('plans')->where('code', 'pro')->update([
            'feature_flags' => json_encode([
                'analytics' => true,
                'bracket' => true,
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'feature_flags')) {
                $table->dropColumn('feature_flags');
            }
        });
    }
};
