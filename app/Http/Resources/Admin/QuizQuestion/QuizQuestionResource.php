<?php

namespace App\Http\Resources\Admin\QuizQuestion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin view: exposes is_correct, numeric config and model_answer.
 * For the student side, extend this class and override exposeCorrect() => false
 * and publicMeta() (drop numeric.value / model_answer).
 */
class QuizQuestionResource extends JsonResource
{
    protected function exposeCorrect(): bool
    {
        return true;
    }

    protected function publicMeta(): array
    {
        return $this->meta ?? [];
    }

    public function toArray(Request $request): array
    {
        $exposeCorrect = $this->exposeCorrect();

        return [
            'id'            => $this->id,
            'type'          => $this->type,
            'question_text' => $this->question_text,
            'image_path'    => $this->image_path,
            'image_url'     => $this->image_path ? url($this->image_path) : null,
            'difficulty'    => $this->difficulty,
            'points'        => (float) $this->points,
            'tags'          => $this->tags ?? [],
            'explanation'   => $this->explanation,
            'meta'          => $this->publicMeta(),
            'keep_answers_order' => (bool) ($this->meta['keep_answers_order'] ?? false),
            'answers'       => $this->whenLoaded('answers', fn() => $this->answers->map(
                function ($answer) use ($exposeCorrect) {
                    $row = [
                        'id'          => $answer->id,
                        'text'        => $answer->text,
                        'image_path'  => $answer->image_path,
                        'image_url'   => $answer->image_path ? url($answer->image_path) : null,
                        'blank_index' => $answer->blank_index,
                        'position'    => $answer->position,
                    ];

                    if ($exposeCorrect) {
                        $row['is_correct'] = (bool) $answer->is_correct;
                    }

                    return $row;
                }
            )->values()),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'deleted_at'    => $this->deleted_at,
        ];
    }
}
