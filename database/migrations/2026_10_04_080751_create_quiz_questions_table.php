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

            // mcq | multiple_response | true_false | short_answer | fill_blanks | numeric | essay
            $table->string('type', 30);
            $table->text('question_text');                 // LaTeX allowed
            $table->string('image_path')->nullable();      // question image
            $table->string('difficulty', 10)->nullable();  // easy | medium | hard
            $table->decimal('points', 6, 2)->default(2);
            $table->jsonb('tags')->nullable();             // ["grammar", "Unit 3"]
            $table->text('explanation')->nullable();       // shown to the student after the quiz
            $table->jsonb('meta')->nullable();             // keep_answers_order | numeric{...} | model_answer
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("
            ALTER TABLE quiz_questions
            ADD CONSTRAINT quiz_questions_type_check
            CHECK (type IN ('mcq','multiple_response','true_false','short_answer','fill_blanks','numeric','essay'))
        ");

        DB::statement("
            ALTER TABLE quiz_questions
            ADD CONSTRAINT quiz_questions_difficulty_check
            CHECK (difficulty IS NULL OR difficulty IN ('easy','medium','hard'))
        ");

        DB::statement('ALTER TABLE quiz_questions ADD CONSTRAINT quiz_questions_points_check CHECK (points >= 0)');

        // tag search / filtering
        DB::statement('CREATE INDEX quiz_questions_tags_gin ON quiz_questions USING GIN (tags)');
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
