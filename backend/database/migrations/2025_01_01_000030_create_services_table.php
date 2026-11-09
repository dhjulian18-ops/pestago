<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('price_unit')->default('per_acara'); // per_acara, per_orang, per_hari, per_paket, penawaran
            $table->decimal('price_min', 15, 2)->nullable();
            $table->decimal('price_max', 15, 2)->nullable();
            $table->integer('capacity_min')->nullable();
            $table->integer('capacity_max')->nullable();
            $table->json('facilities')->nullable();
            $table->json('requirements')->nullable();
            $table->text('cancellation_policy')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            // Status: draft, published, suspended
            $table->enum('status', ['draft', 'published', 'suspended'])->default('draft');
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->integer('rating_count')->default(0);
            $table->integer('booking_count')->default(0);
            $table->timestamps();
        });

        Schema::create('service_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('service_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            // Type: fixed (venue), mobile (datang ke customer), area
            $table->enum('location_type', ['fixed', 'mobile', 'area'])->default('fixed');
            $table->json('service_area')->nullable(); // untuk mobile/area
            $table->timestamps();
        });

        Schema::create('service_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->date('date');
            // Status: available, booked, blocked, pending
            $table->enum('status', ['available', 'booked', 'blocked', 'pending'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_availabilities');
        Schema::dropIfExists('service_locations');
        Schema::dropIfExists('service_images');
        Schema::dropIfExists('services');
    }
};
