<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();

            // true_false | multiple_choice | fill_blanks | short_answer | matching | image_answer
            $table->string('type', 30);

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            $table->decimal('points', 6, 2)->default(1);
            $table->boolean('is_required')->default(true);
            $table->boolean('show_points')->default(false);
            $table->text('explanation')->nullable();

            // إعدادات خاصة بالنوع: {"multiple_correct": true, "randomize_choices": false}
            $table->jsonb('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("
            ALTER TABLE quiz_questions
            ADD CONSTRAINT quiz_questions_type_check
            CHECK (type IN ('true_false','multiple_choice','fill_blanks','short_answer','matching','image_answer'))
        ");
        DB::statement("ALTER TABLE quiz_questions ADD CONSTRAINT quiz_questions_points_check CHECK (points >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
