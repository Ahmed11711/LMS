<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chapter_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();

            $table->string('type', 10);       // lesson | quiz
            $table->unsignedBigInteger('content_id');

            $table->unsignedInteger('position')->default(0);

            $table->timestamps();

            $table->unique(['chapter_id', 'type', 'content_id']);
            $table->index(['chapter_id', 'position']);
            $table->index(['type', 'content_id']);
        });

        DB::statement("
            ALTER TABLE chapter_contents
            ADD CONSTRAINT chapter_contents_type_check
            CHECK (type IN ('lesson','quiz'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_contents');
    }
};
