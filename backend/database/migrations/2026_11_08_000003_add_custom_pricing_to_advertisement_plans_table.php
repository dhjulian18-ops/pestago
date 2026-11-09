<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertisement_plans', function (Blueprint $table) {
            $table->decimal('price_per_day', 12, 2)->default(15000)->after('price_business');
            $table->unsignedSmallInteger('min_days_custom')->default(1)->after('price_per_day');
            $table->unsignedSmallInteger('max_days_custom')->default(90)->after('min_days_custom');
        });
    }

    public function down(): void
    {
        Schema::table('advertisement_plans', function (Blueprint $table) {
            $table->dropColumn(['price_per_day', 'min_days_custom', 'max_days_custom']);
        });
    }
};

