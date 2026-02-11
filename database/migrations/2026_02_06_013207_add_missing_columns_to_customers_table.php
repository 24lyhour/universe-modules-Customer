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
        Schema::table('customers', function (Blueprint $table) {
            // Foreign key to movice (if customer is associated with a movie)
            $table->foreignId('movice_id')->nullable()->after('id')->constrained('movices')->nullOnDelete();

            // Self-referencing customer_id (if needed for referrals or parent customer)
            $table->foreignId('customer_id')->nullable()->after('movice_id')->constrained('customers')->nullOnDelete();

            // OAuth/Social login fields
            $table->string('provider_name')->nullable()->after('status');
            $table->string('provider_id')->nullable()->after('provider_name');
            $table->string('avatar')->nullable()->after('provider_id');
            $table->text('access_token')->nullable()->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['movice_id']);
            $table->dropForeign(['customer_id']);
            $table->dropColumn([
                'movice_id',
                'customer_id',
                'provider_name',
                'provider_id',
                'avatar',
                'access_token',
            ]);
        });
    }
};
