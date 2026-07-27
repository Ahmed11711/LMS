<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bag_purchases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bag_id')
                ->constrained('bags')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('payment_info_id')
                ->nullable()
                ->constrained('instructor_receiver_accounts')
                ->nullOnDelete();

            $table->string('receipt'); // مسار صورة/ملف الإيصال
            $table->decimal('amount', 10, 2)->nullable(); // السعر وقت الشراء (نسخة ثابتة)
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bag_purchases');
    }
};
