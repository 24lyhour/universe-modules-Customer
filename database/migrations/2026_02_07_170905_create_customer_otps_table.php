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
        Schema::create('customer_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('phone')->index();
            $table->string('code', 6);
            $table->enum('type', ['registration', 'login', 'password_reset', 'phone_verification'])->default('registration');
            $table->boolean('verified')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->index(['phone', 'code', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_otps');
    }
};
