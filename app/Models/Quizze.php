<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Quizze extends Model
{

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(QuizQuestion::class, 'quiz_question_pivot', 'quiz_id', 'question_id')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('quiz_question_pivot.position');
    }
}
