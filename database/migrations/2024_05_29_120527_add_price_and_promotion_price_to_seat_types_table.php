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
        Schema::table('seat_types', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->after('name'); 
            $table->decimal('promotion_price', 8, 2)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seat_types', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropColumn('promotion_price');
        });
    }
};
