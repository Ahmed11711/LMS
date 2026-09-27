<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_bag_downloads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('bag_id')
                ->constrained('bags')
                ->cascadeOnDelete();

            $table->foreignId('bag_item_id')
                ->nullable()
                ->constrained('bag_items')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['user_id', 'bag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bag_downloads');
    }
};
