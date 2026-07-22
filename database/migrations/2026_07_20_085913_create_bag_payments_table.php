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
        Schema::create('bag_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bag_id');
            $table->foreign('bag_id')->references('id')->on('bags')->onDelete('cascade');
            $table->unsignedBigInteger('user_payment_info_id');
            $table->foreign('user_payment_info_id')->references('id')->on('user_payment_infos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bag_payments');
    }
};
