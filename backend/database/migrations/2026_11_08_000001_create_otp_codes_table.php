<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 25)->index();
            $table->string('code', 10);
            $table->string('action', 50)->default('register_customer'); // register_customer, register_vendor, forgot_password
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->longText('payload')->nullable(); // JSON data (e.g. pending registration data)
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};

