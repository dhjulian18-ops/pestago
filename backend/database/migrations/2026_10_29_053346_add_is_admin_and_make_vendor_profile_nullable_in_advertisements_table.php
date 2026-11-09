<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            if (!Schema::hasColumn('advertisements', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('id');
            }
            $table->unsignedBigInteger('vendor_profile_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            if (Schema::hasColumn('advertisements', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
            $table->unsignedBigInteger('vendor_profile_id')->nullable(false)->change();
        });
    }
};
