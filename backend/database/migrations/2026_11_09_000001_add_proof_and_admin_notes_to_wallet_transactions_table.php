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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('proof_image')->nullable()->after('status');
            $table->text('admin_notes')->nullable()->after('proof_image');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete()->after('admin_notes');
            $table->timestamp('processed_at')->nullable()->after('processed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['proof_image', 'admin_notes', 'processed_by', 'processed_at']);
        });
    }
};

