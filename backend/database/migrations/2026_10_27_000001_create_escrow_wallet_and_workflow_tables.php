<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Vendor Wallets (Escrow System)
        Schema::create('vendor_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained()->unique();
            $table->decimal('balance', 15, 2)->default(0);          // Saldo tersedia (bisa dicairkan)
            $table->decimal('pending_balance', 15, 2)->default(0);  // Saldo tertahan (menunggu acara selesai)
            $table->decimal('total_earned', 15, 2)->default(0);     // Total pendapatan sepanjang waktu
            $table->timestamps();
        });

        // 2. Wallet Transactions (Log pergerakan saldo)
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_number')->unique();
            $table->enum('type', [
                'escrow_in',       // Customer bayar → saldo masuk pending
                'released',        // Acara selesai → saldo dirilis ke balance
                'refunded',        // Refund → saldo dikurangi
                'withdrawn',       // Vendor tarik saldo (pencairan)
                'tax_deducted',    // Potongan pajak
                'platform_fee',    // Potongan komisi platform
            ]);
            $table->decimal('amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->text('description');
            $table->enum('status', ['completed', 'pending', 'failed'])->default('completed');
            $table->timestamps();
        });

        // 3. Platform Settings (Pajak & Komisi — Diatur Admin)
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('label')->nullable();        // Label tampilan (human-readable)
            $table->string('description')->nullable();
            $table->string('group')->default('general'); // Grouping: general, payment, tax
            $table->timestamps();
        });

        // Seed default settings
        DB::table('platform_settings')->insert([
            [
                'key' => 'tax_percentage',
                'value' => '2.5',
                'label' => 'Persentase Pajak (%)',
                'description' => 'Pajak yang dipotong dari pembayaran vendor saat saldo dirilis',
                'group' => 'tax',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'platform_fee_percentage',
                'value' => '5',
                'label' => 'Komisi Platform (%)',
                'description' => 'Biaya platform yang dikenakan ke customer saat checkout',
                'group' => 'payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'escrow_auto_release_days',
                'value' => '3',
                'label' => 'Auto Release Escrow (hari)',
                'description' => 'Jumlah hari setelah acara selesai, saldo otomatis dirilis jika customer tidak konfirmasi',
                'group' => 'payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 4. Order Progress Proofs (Foto bukti progres acara — anti pemalsuan)
        Schema::create('order_progress_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->enum('type', ['setup', 'in_progress', 'completion']);
            $table->string('image_path');
            $table->text('caption')->nullable();
            $table->timestamps();
        });

        // 5. Alter orders table — tambah payment_type & cash meeting notes
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_type', ['non_tunai', 'tunai'])->default('non_tunai')->after('status');
            $table->text('cash_meeting_notes')->nullable()->after('payment_type');
        });

        // 6. Alter order_items table — tambah kolom workflow
        Schema::table('order_items', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('vendor_responded_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->timestamp('customer_confirmed_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'completed_at', 'customer_confirmed_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'cash_meeting_notes']);
        });

        Schema::dropIfExists('order_progress_proofs');
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('vendor_wallets');
    }
};
