<?php

namespace App\Http\Resources\Admin\QuizQuestion;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'image_path' => $this->image_path,
            'points' => $this->points,
            'is_required' => $this->is_required,
            'show_points' => $this->show_points,
            'explanation' => $this->explanation,
            'settings' => $this->settings,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
