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
        Schema::create('customer_movice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('movice_id')->constrained('movices')->onDelete('cascade');
            $table->timestamp('rented_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'movice_id', 'rented_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_movice');
    }
};
