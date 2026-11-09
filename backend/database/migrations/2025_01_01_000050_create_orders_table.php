<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keranjang per acara
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->date('booking_date')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('price_snapshot', 15, 2); // harga saat ditambah ke keranjang
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Pesanan utama (multi-vendor)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g., PG-20241201-0001
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('platform_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            // Status keseluruhan order
            $table->enum('status', [
                'pending_payment', 'payment_processing', 'paid',
                'partially_confirmed', 'confirmed', 'in_progress',
                'completed', 'cancelled', 'refunded'
            ])->default('pending_payment');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Sub-pesanan per vendor
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->constrained();
            $table->foreignId('service_id')->constrained();
            $table->string('service_name_snapshot'); // snapshot nama layanan saat checkout
            $table->decimal('price_snapshot', 15, 2); // snapshot harga saat checkout
            $table->string('price_unit_snapshot')->default('per_acara');
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 15, 2);
            $table->date('booking_date')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('vendor_notes')->nullable();
            // Status per vendor
            $table->enum('status', [
                'pending_confirmation', 'confirmed', 'rejected',
                'in_progress', 'completed', 'cancelled'
            ])->default('pending_confirmation');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('vendor_responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('carts');
    }
};
