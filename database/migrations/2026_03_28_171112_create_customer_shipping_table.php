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
        Schema::create('customer_shipping', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');

            // Address type
            $table->string('label')->default('Home'); // Home, Office, Other

            // Recipient info
            $table->string('recipient_name');
            $table->string('phone_number');

            // Location details
            $table->string('province');
            $table->string('district');
            $table->string('commune');
            $table->string('street_address');
            $table->string('house_number')->nullable();
            $table->string('floor')->nullable();

            // Additional info
            $table->string('landmark')->nullable();
            $table->text('note')->nullable();

            // Map coordinates
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Default address flag
            $table->boolean('is_default')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('customer_id');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_shipping');
    }
};
