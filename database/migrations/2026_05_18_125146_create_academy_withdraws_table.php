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
        Schema::create('academy_withdraws', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academy_id');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'rejected', 'approved'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(); // super admin ID
            $table->string('payment_method'); // bank / vodafone_cash / instapay etc
            $table->json('payment_details')->nullable();
            $table->string('receipt_image')->nullable();
            $table->string('transaction_number')->nullable()->unique();
            $table->string('transaction_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academy_withdraws');
    }
};
