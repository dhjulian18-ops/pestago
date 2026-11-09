<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Nama acara
            $table->string('event_type'); // pernikahan, ulang_tahun, wisuda, seminar, khitanan, lainnya
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->text('location_address')->nullable();
            $table->string('location_city')->nullable();
            $table->decimal('location_latitude', 10, 8)->nullable();
            $table->decimal('location_longitude', 11, 8)->nullable();
            $table->integer('guest_count')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->json('requirements')->nullable(); // kategori layanan yang dibutuhkan
            $table->text('notes')->nullable();
            $table->enum('status', ['planning', 'booked', 'completed', 'cancelled'])->default('planning');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
