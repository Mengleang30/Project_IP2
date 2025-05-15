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
        Schema::create('coupon_customer', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->constrained('users')->onDelete('cascade');
            $table->bigInteger('coupon_id')->constrained('coupons')->onDelete('cascade');
            $table->integer("used_times")->default(0);
            $table->integer('limit')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_customer');
    }
};
