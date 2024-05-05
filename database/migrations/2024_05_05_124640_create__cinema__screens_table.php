<?php

use App\Models\Cinema;
use App\Models\Screen;
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
        Schema::create('_cinema__screens', function (Blueprint $table) {
            Schema::create('cinema_screens', function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(Cinema::class)->constrained();
                $table->foreignIdFor(Screen::class)->constrained();
                $table->integer('deleted')->default(0);
                $table->timestamps();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_cinema__screens');
    }
};
