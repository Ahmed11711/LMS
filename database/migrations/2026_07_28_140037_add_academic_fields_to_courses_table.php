<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->after('category_id')
                ->constrained()->nullOnDelete();

            $table->foreignId('term_id')->nullable()->after('grade_id')
                ->constrained()->nullOnDelete();

            $table->foreignId('subject_id')->nullable()->after('term_id')
                ->constrained()->nullOnDelete();

            $table->foreignId('academic_year_id')->nullable()->after('subject_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grade_id');
            $table->dropConstrainedForeignId('term_id');
            $table->dropConstrainedForeignId('subject_id');
            $table->dropConstrainedForeignId('academic_year_id');
        });
    }
};
