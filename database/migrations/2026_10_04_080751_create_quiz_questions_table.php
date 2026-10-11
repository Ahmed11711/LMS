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
            $table->jsonb('tags')->nullable();            // ["grammar", "Unit 3"]
            $table->text('question_text');
            $table->text('explanation')->nullable();      // explanation answer after user submit the answer
            $table->string('difficulty', 10)->nullable(); // easy | medium | hard
            $table->decimal('points', 6, 2)->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("
            ALTER TABLE quiz_questions
            ADD CONSTRAINT quiz_questions_type_check
            CHECK (type IN ('true_false','multiple_choice','fill_blanks','short_answer','matching','image_answer'))
        ");

        DB::statement("
            ALTER TABLE quiz_questions
            ADD CONSTRAINT quiz_questions_difficulty_check
            CHECK (difficulty IS NULL OR difficulty IN ('easy','medium','hard'))
        ");

        DB::statement("ALTER TABLE quiz_questions ADD CONSTRAINT quiz_questions_points_check CHECK (points >= 0)");

        // للبحث والفلترة بالوسوم
        DB::statement("CREATE INDEX quiz_questions_tags_gin ON quiz_questions USING GIN (tags)");
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
