<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bag_galleries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bag_id')
                ->constrained('bags')
                ->cascadeOnDelete();

            $table->string('image');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bag_galleries');
    }
};
