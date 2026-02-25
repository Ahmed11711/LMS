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
        Schema::create('feature_packages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('feature_id');

            $table->string('value')->nullable();
            $table->string('lable')->nullable();
            $table->string('key_feature')->nullable();

            $table->timestamps();

            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->foreign('feature_id')->references('id')->on('features')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_packages');
    }
};
