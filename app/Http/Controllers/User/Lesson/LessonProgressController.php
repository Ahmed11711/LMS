<?php

namespace App\Http\Controllers\User\Lesson;

use App\Http\Controllers\Controller;
use App\Models\LessonProgress;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class LessonProgressController extends Controller
{
    use ApiResponseTrait;

    public function update($lessonId, Request $request)
    {
        $user = $request->get('tenant_user');

        $request->validate([
            'watched_seconds' => 'required|integer',
            'is_completed'    => 'nullable|boolean',
        ]);

        $progress = LessonProgress::updateOrCreate(
            [
                'user_id'   => $user->id,
                'lesson_id' => $lessonId,
            ],
            [
                'watched_seconds' => $request->watched_seconds,
                'is_completed'    => $request->is_completed ?? false,
            ]
        );

        return $this->successResponse($progress, 'Progress updated');
    }
}
