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
        Schema::table('showtimes', function (Blueprint $table) {
            $table->renameColumn('translation_id', 'subtitle')->string('subtitle');
        });
        Schema::table('showtimes', function (Blueprint $table) {
            $table->string('subtitle')->change();
        });
        Schema::table('showtimes', function (Blueprint $table) {
            $table->time('show_time')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showtimes', function (Blueprint $table) {
            $table->renameColumn('subtitle', 'translation_id');
        });
        Schema::table('showtimes', function (Blueprint $table) {
            $table->integer('translation_id')->change();
        });
        Schema::table('showtimes', function (Blueprint $table) {
            $table->dateTime('show_time')->change();
        });
    }
};
