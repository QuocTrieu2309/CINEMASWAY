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
            $table->unsignedBigInteger('cinema_screen_id')->after('id');
            $table->foreign('cinema_screen_id')->references('id')->on('cinema_screens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seat_types', function (Blueprint $table) {
            $table->dropForeign(['cinema_screen_id']);
            $table->dropColumn('cinema_screen_id');
        });
    }
};
