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
        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->text('text')->nullable();                        // نص الخيار أو الإجابة المقبولة
            $table->string('image_path')->nullable();                // صورة الخيار (لو فيه)
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('blank_index')->nullable(); // رقم الفراغ (لنوع الفراغات بس)
            $table->unsignedInteger('position')->default(0);         // ترتيب العرض
            $table->timestamps();

            $table->index(['question_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_answers');
    }
};
