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
        // 1. Update users table with 'admin' role, admin_role_title, and permissions JSON
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('customer', 'vendor', 'super_admin', 'admin') NOT NULL DEFAULT 'customer'");

        if (!Schema::hasColumn('users', 'admin_role_title')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('admin_role_title', 100)->nullable()->after('role');
            });
        }

        if (!Schema::hasColumn('users', 'permissions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('permissions')->nullable()->after('admin_role_title');
            });
        }

        // 2. Modify conversations vendor_profile_id to be nullable
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `conversations` MODIFY `vendor_profile_id` bigint(20) unsigned NULL DEFAULT NULL");
        
        // Re-add foreign key if not present
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `conversations` ADD CONSTRAINT `conversations_vendor_profile_id_foreign` FOREIGN KEY (`vendor_profile_id`) REFERENCES `vendor_profiles` (`id`) ON DELETE CASCADE");
        } catch (\Exception $e) {}

        // Add support chat fields if not present
        if (!Schema::hasColumn('conversations', 'type')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->enum('type', ['vendor', 'support'])->default('vendor')->after('order_item_id');
            });
        }

        if (!Schema::hasColumn('conversations', 'admin_id')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->foreignId('admin_id')->nullable()->after('vendor_profile_id')->constrained('users')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('conversations', 'support_subject')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->string('support_subject')->nullable()->after('admin_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['type', 'admin_id', 'support_subject']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['admin_role_title', 'permissions']);
        });

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('customer', 'vendor', 'super_admin') NOT NULL DEFAULT 'customer'");
    }
};
