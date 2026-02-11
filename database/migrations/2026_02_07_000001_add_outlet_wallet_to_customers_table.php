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
            // Add outlet_id if it doesn't exist
            if (!Schema::hasColumn('customers', 'outlet_id')) {
                $table->unsignedBigInteger('outlet_id')->nullable()->after('id');
                $table->index('outlet_id');
            }

            // Add wallet_id if it doesn't exist
            if (!Schema::hasColumn('customers', 'wallet_id')) {
                $table->unsignedBigInteger('wallet_id')->nullable()->after('outlet_id');
                $table->index('wallet_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'wallet_id')) {
                $table->dropIndex(['wallet_id']);
                $table->dropColumn('wallet_id');
            }
        });
    }
};
