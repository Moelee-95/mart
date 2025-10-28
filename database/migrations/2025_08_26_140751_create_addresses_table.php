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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('set null');
            
            // GPS coordinates
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            // Location details
            $table->string('area'); // "Shakaraka"
            $table->string('type')->default('apartment'); // apartment, house, office
            
            // Address components
            $table->string('building_name')->nullable(); // "baharan city building 1"
            $table->string('floor')->nullable(); // "1"
            $table->string('apartment_number')->nullable(); // "1"
            $table->string('nearby_landmark')->nullable(); // "psht city center"
            
            // Contact
            $table->string('phone'); // "+964 7708138928"
            
            // Additional info
            $table->text('additional_directions')->nullable();
            $table->string('label')->nullable(); // "Apartment", "Home", "Work", etc.
            
            // Status
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            
            // Index for location queries
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
