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
        Schema::create('cart_books', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cart_id')->unsigned();
            $table->bigInteger('book_id')->unsigned();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 8, 2);
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_books');
    }
};
