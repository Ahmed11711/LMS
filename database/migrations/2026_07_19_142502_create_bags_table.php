<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('title', 255);
            $table->string('short_description', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();

            $table->unsignedBigInteger('category_bag_id')->nullable();
            $table->foreign('category_bag_id')->references('id')->on('category_bags')->onDelete('set null');


            $table->string('type_price', 255)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->string('currency', 10)->default('SAR');

            $table->string('download_type')->default('unlimited');
            $table->unsignedInteger('download_limit')->nullable();
            $table->unsignedInteger('download_validity_days')->nullable();

            // Stats
            $table->unsignedInteger('count_view')->default(0);

            $table->string('status')->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bags');
    }
};
