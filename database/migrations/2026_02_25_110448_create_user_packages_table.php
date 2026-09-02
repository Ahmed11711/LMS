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
        Schema::create('user_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id');
            $table->string('package_name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->string('transaction_id')->nullable();
            $table->string('status')->default('pending');
            // $table->enum('status', ['pending', 'active', 'expired', 'cancelled', 'failed'])->default('pending'); // m SQL
            $table->decimal('price', 8, 2);
            $table->string('payment_proof')->nullable()->after('price');
            $table->decimal('amount', 10, 2)->nullable()->after('payment_proof');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_packages');
    }
};
