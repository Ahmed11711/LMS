<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('access_duration_type', 20)
                ->default('lifetime')
                ->after('status');

            $table->unsignedInteger('access_days')->nullable()->after('access_duration_type');
            $table->date('access_until_date')->nullable()->after('access_days');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['access_duration_type', 'access_days', 'access_until_date']);
        });
    }
};
