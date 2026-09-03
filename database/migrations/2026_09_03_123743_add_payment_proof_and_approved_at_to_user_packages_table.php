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
        Schema::connection('tenant')->table('user_packages', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('price');
            $table->timestamp('approved_at')->nullable()->after('payment_proof');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('user_packages', function (Blueprint $table) {
            $table->dropColumn(['payment_proof', 'approved_at', 'approved_by']);
        });
    }
};
