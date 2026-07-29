<?php

namespace App\Http\Resources\Admin\Course;

use App\Http\Resources\Admin\Category\CategoryResource;

use App\Http\Resources\Admin\Chapter\ChapterResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\User\UserResource;

class CourseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'short_description' => $this->short_description ?? null,
            'image' => $this->image
                ? asset(ltrim($this->image, '/'))
                : null,
            'price_type' => $this->price_type,
            'price' => $this->price,
            'final_price' => $this->final_price,
            'status' => $this->status,
            'slug' => $this->slug,
            'currency' => $this->currency,
            'chapters'    => ChapterResource::collection($this->whenLoaded('chapters')),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'user'        => new UserResource($this->whenLoaded('user')),
            'infos'    =>  $this->whenLoaded('infos'),
            'subscribers_count' => $this->active_subscribers_count ?? 0,
            'total_sales' => (float) ($this->total_sales ?? 0),
            'completion_percentage' => $this->completion_percentage ?? 0,
            'grade' => $this->whenLoaded('grade', function () {
                return [
                    'id'   => $this->grade->id,
                    'name' => $this->grade->name,
                ];
            }),
            'term' => $this->whenLoaded('term', function () {
                return [
                    'id'   => $this->term->id,
                    'name' => $this->term->name,
                ];
            }),
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id'   => $this->subject->id,
                    'name' => $this->subject->name,
                ];
            }),
            'academic_year' => $this->whenLoaded('academicYear', function () {
                return [
                    'id'   => $this->academicYear->id,
                    'name' => $this->academicYear->name,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
