<?php

namespace App\Http\Controllers\User\Lesson;

use App\Http\Controllers\Controller;
use App\Models\LessonComment;
use App\Models\LessonCommentLike;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class LessonCommentController extends Controller
{
    use ApiResponseTrait;

    public function index($lessonId, Request $request)
    {
        $comments = LessonComment::where('lesson_id', $lessonId)
            ->whereNull('parent_id')
            ->with(['user:id,name,profile_image', 'replies.user:id,name,profile_image', 'likes'])
            ->latest()
            ->get();

        return $this->successResponse($comments, 'Comments retrieved');
    }

    public function store($lessonId, Request $request)
    {
        $user = $request->get('tenant_user');

        $request->validate([
            'body'      => 'required|string',
            'parent_id' => 'nullable|exists:lesson_comments,id',
        ]);

        $comment = LessonComment::create([
            'user_id'   => $user->id,
            'lesson_id' => $lessonId,
            'parent_id' => $request->parent_id,
            'body'      => $request->body,
        ]);

        return $this->successResponse($comment, 'Comment created', 201);
    }

    public function destroy($lessonId, $commentId, Request $request)
    {
        $user = $request->get('tenant_user');

        $comment = LessonComment::where('id', $commentId)->first();

        if (!$comment) {
            return $this->errorResponse('Comment not found', 404);
        }

        if ($comment->user_id !== $user->id) {
            return $this->errorResponse('You are not authorized to delete this comment', 403);
        }

        $comment->delete();

        return $this->successResponse(null, 'Comment deleted');
    }

    public function toggleLike($lessonId, $commentId, Request $request)
    {
        $user = $request->get('tenant_user');

        $like = LessonCommentLike::where('user_id', $user->id)
            ->where('lesson_comment_id', $commentId)
            ->first();

        if ($like) {
            $like->delete();
            return $this->successResponse(null, 'Unliked');
        }

        LessonCommentLike::create([
            'user_id'           => $user->id,
            'lesson_comment_id' => $commentId,
        ]);

        return $this->successResponse(null, 'Liked');
    }
}
