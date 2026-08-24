<?php

namespace App\Http\Resources\User\Course;

use App\Http\Resources\Admin\Chapter\ChapterResource;
use App\Http\Resources\Admin\ReceiverAccount\ReceiverAccountResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ShowCoursResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_enrolled' => $this->when(
                auth('api')->check(),
                fn() => (bool) $this->is_enrolled
            ),
            'enrollment_status' => $this->when(
                auth('api')->check(),
                fn() => $this->enrollment_status
            ),
            'title' => $this->title,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'image' => $this->image
                ? asset(ltrim($this->image, '/'))
                : null,
            'price_type' => $this->price_type,
            'price' => $this->price,
            'final_price' => $this->final_price,
            'status' => $this->status,
            'slug' => $this->slug,
            'currency' => $this->currency,
            'rating' => $this->slug,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'instructor' => $this->whenLoaded('user'),
            'infos' => $this->whenLoaded('infos'),

            'chapters'    => ChapterResource::collection($this->whenLoaded('chapters')),
            'receiver_accounts' => $this->whenLoaded('courseReceiverAccounts', function () {
                return $this->courseReceiverAccounts->map(fn($item) => [
                    'id'            => $item->id,
                    'account_value' => $item->instructorReceiverAccount->account_value,
                    'receiver_account' => new ReceiverAccountResource($item->instructorReceiverAccount->receiverAccount),
                ]);
            }),

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

        ];
    }
}
