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
        Schema::table('coupon_users', function (Blueprint $table) {
            $table->integer('limit')->default(1)->after('coupon_id');
            $table->integer('used')->default(0)->after('limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupon_users', function (Blueprint $table) {
             $table->dropColumn(['limit', 'used']);
            //
        });
    }
};
