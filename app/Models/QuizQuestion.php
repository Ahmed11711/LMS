<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
