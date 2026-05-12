<?php

namespace App\Http\Controllers\User\Lesson;

use App\Http\Controllers\Controller;
use App\Models\LessonNote;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class LessonNoteController extends Controller
{
    use ApiResponseTrait;

    public function index($lessonId, Request $request)
    {
        $user = $request->get('tenant_user');

        $notes = LessonNote::where('lesson_id', $lessonId)
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return $this->successResponse($notes, 'Notes retrieved');
    }

    public function store($lessonId, Request $request)
    {
        $user = $request->get('tenant_user');

        $request->validate([
            'body'       => 'required|string',
            'video_time' => 'nullable|integer',
        ]);

        $note = LessonNote::create([
            'user_id'    => $user->id,
            'lesson_id'  => $lessonId,
            'body'       => $request->body,
            'video_time' => $request->video_time,
        ]);

        return $this->successResponse($note, 'Note created', 201);
    }

    public function update($lessonId, $noteId, Request $request)
    {
        $user = $request->get('tenant_user');

        $note = LessonNote::where('id', $noteId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $note->update($request->only(['body', 'video_time']));

        return $this->successResponse($note, 'Note updated');
    }

    public function destroy($lessonId, $noteId, Request $request)
    {
        $user = $request->get('tenant_user');

        $note = LessonNote::where('id', $noteId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $note->delete();

        return $this->successResponse(null, 'Note deleted');
    }
}
