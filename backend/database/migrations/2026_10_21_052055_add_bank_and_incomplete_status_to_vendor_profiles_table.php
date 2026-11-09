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
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('website');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_holder')->nullable()->after('bank_account_number');
        });

        // Update enum to include incomplete and default to incomplete
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE vendor_profiles MODIFY COLUMN status ENUM('incomplete', 'pending', 'needs_revision', 'rejected', 'active', 'suspended', 'inactive') NOT NULL DEFAULT 'incomplete'");
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE vendor_profiles MODIFY COLUMN status ENUM('pending', 'needs_revision', 'rejected', 'active', 'suspended', 'inactive') NOT NULL DEFAULT 'pending'");

        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_number', 'bank_account_holder']);
        });
    }
};
