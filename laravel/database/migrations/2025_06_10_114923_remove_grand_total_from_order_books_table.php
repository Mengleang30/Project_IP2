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
        Schema::table('order_books', function (Blueprint $table) {
            if (Schema::hasColumn('order_books', 'grand_total')) {
                $table->dropColumn('grand_total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_books', function (Blueprint $table) {
            $table->decimal('grand_total', 8, 2)->nullable();
        });
    }
};
