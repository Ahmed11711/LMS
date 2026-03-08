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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('type', ['recorded', 'online', 'physical']);
            $table->foreignId('category_id')->nullable()->constrained();
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            $table->enum('price_type', ['free', 'paid'])->default('paid');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2)->default(0);
            $table->enum('status', ['published', 'draft'])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
