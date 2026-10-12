<?php

namespace App\Http\Controllers\Admin\Quizze;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Quizze\QuizzeQuestionAttachRequest;
use App\Http\Requests\Admin\Quizze\QuizzeQuestionReorderRequest;
use App\Http\Resources\Admin\QuizQuestion\QuizQuestionResource;
use App\Models\Quizze;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Links questions from the question bank to a quiz (pivot: quiz_question_pivot).
 *
 * GET    /quizzes/{quizId}/questions                 list, ordered by position
 * POST   /quizzes/{quizId}/questions                 body: question_ids[]  (appended at the end, duplicates skipped)
 * PUT    /quizzes/{quizId}/questions                 body: question_ids[]  (new order of the attached questions)
 * DELETE /quizzes/{quizId}/questions/{questionId}    detach one question
 */
class QuizzeQuestionController extends Controller
{
    use ApiResponseTrait;

    public function index(int $quizId): JsonResponse
    {
        $quiz = Quizze::find($quizId);

        if (!$quiz) {
            return $this->errorResponse('Quiz not found', 404);
        }

        return $this->successResponse(
            QuizQuestionResource::collection($quiz->questions()->with('answers')->get()),
            'Quiz questions retrieved successfully'
        );
    }

    public function store(QuizzeQuestionAttachRequest $request, int $quizId): JsonResponse
    {
        $quiz = Quizze::find($quizId);

        if (!$quiz) {
            return $this->errorResponse('Quiz not found', 404);
        }

        $ids = $request->validated()['question_ids'];

        DB::transaction(function () use ($quiz, $ids) {
            $attached = $quiz->questions()->pluck('quiz_question_pivot.question_id')->map(fn($i) => (int) $i)->all();
            $position = (int) $quiz->questions()->max('quiz_question_pivot.position');

            foreach ($ids as $id) {
                if (in_array((int) $id, $attached, true)) {
                    continue;
                }

                $quiz->questions()->attach($id, ['position' => ++$position]);
            }
        });

        return $this->successResponse(
            QuizQuestionResource::collection($quiz->questions()->with('answers')->get()),
            'Questions added to the quiz',
            201
        );
    }

    public function reorder(QuizzeQuestionReorderRequest $request, int $quizId): JsonResponse
    {
        $quiz = Quizze::find($quizId);

        if (!$quiz) {
            return $this->errorResponse('Quiz not found', 404);
        }

        $given = array_map('intval', $request->validated()['question_ids']);
        $attached = $quiz->questions()->pluck('quiz_question_pivot.question_id')->map(fn($i) => (int) $i)->all();

        if (array_diff($given, $attached)) {
            return $this->errorResponse('في سؤال مش مضاف للاختبار ده.', 422);
        }

        DB::transaction(function () use ($quiz, $given) {
            foreach ($given as $index => $id) {
                $quiz->questions()->updateExistingPivot($id, ['position' => $index + 1]);
            }
        });

        return $this->successResponse(
            QuizQuestionResource::collection($quiz->questions()->with('answers')->get()),
            'Quiz questions reordered'
        );
    }

    public function destroy(int $quizId, int $questionId): JsonResponse
    {
        $quiz = Quizze::find($quizId);

        if (!$quiz) {
            return $this->errorResponse('Quiz not found', 404);
        }

        if ($quiz->questions()->detach($questionId) === 0) {
            return $this->errorResponse('السؤال مش مضاف للاختبار ده.', 404);
        }

        return $this->successResponse(null, 'Question removed from the quiz');
    }
}
