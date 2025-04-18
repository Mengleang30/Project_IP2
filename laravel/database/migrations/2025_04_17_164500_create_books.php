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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(false);
            $table->string('author');
            $table->decimal('price', 8, 2);
            $table->bigInteger('category_id')->unsigned();
            $table->date('published_date');
            $table->decimal('discount', 5, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->string('url_image')->nullable();
            $table->string('description')->nullable();
            $table->string('path_image')->nullable();
            $table->json('languages')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
