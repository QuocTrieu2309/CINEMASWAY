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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seat_type_id');
            $table->string('name', 60)->comment('Tên loại vé');
            $table->decimal('price', 8, 2)->comment('Giá loại vé');
            $table->decimal('promotion_price', 8, 2)->comment('Giá vé khi áp dụng khuyến mãi');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('deleted')->default(0);
            $table->timestamps();
            $table->foreign('seat_type_id')->references('id')->on('seat_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
