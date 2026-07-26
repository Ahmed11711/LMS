<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bag_payments', function (Blueprint $table) {
            // شيل القيد القديم الغلط
            $table->dropForeign('bag_payments_user_payment_info_id_foreign');

            // ضيف القيد الصحيح اللي بيشاور على instructor_receiver_accounts
            $table->foreign('user_payment_info_id')
                ->references('id')
                ->on('instructor_receiver_accounts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bag_payments', function (Blueprint $table) {
            $table->dropForeign(['user_payment_info_id']);

            $table->foreign('user_payment_info_id')
                ->references('id')
                ->on('user_payment_infos')
                ->cascadeOnDelete();
        });
    }
};
