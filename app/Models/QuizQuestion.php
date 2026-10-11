<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuizQuestion extends Model
{
    use SoftDeletes;

    protected $table = 'quiz_questions';

    protected $fillable = [
        'type',
        'tags',
        'question_text',
        'image_path',
        'explanation',
        'difficulty',
        'points',
        'meta',
    ];

    protected $casts = [
        'tags'   => 'array',
        'meta'   => 'array',
        'points' => 'decimal:2',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class, 'question_id')
            ->orderBy('position')
            ->orderBy('id');
    }

    public function quizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quizze::class, 'quiz_question_pivot', 'question_id', 'quiz_id')
            ->withPivot('position')
            ->withTimestamps();
    }
}
