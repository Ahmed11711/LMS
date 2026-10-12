<?php

namespace App\Http\Controllers\Admin\QuizQuestion;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\QuizQuestion\QuizQuestionResource;
use App\Models\QuizQuestion;
use App\Models\Quizze;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        $questions = $quiz->questions()->with('answers')->get();

        return $this->successResponse(
            QuizQuestionResource::collection($questions),
            'Quiz questions retrieved successfully'
        );
    }

    public function store(Request $request, int $quizId): JsonResponse
    {
        $quiz = Quizze::find($quizId);

        if (!$quiz) {
            return $this->errorResponse('Quiz not found', 404);
        }

        $data = $request->validate([
            'question_ids'   => ['required', 'array', 'min:1', 'max:200'],
            'question_ids.*' => ['integer', 'distinct', Rule::exists(QuizQuestion::class, 'id')->whereNull('deleted_at')],
        ], [
            'question_ids.required'   => 'اختار سؤال واحد على الأقل.',
            'question_ids.*.exists'   => 'في سؤال مش موجود في البنك.',
            'question_ids.*.distinct' => 'في سؤال متكرر.',
        ]);

        DB::transaction(function () use ($quiz, $data) {
            $attached = $quiz->questions()->pluck('quiz_question_pivot.question_id')->map(fn($i) => (int) $i)->all();
            $position = (int) $quiz->questions()->max('quiz_question_pivot.position');

            foreach ($data['question_ids'] as $id) {
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

    public function reorder(Request $request, int $quizId): JsonResponse
    {
        $quiz = Quizze::find($quizId);

        if (!$quiz) {
            return $this->errorResponse('Quiz not found', 404);
        }

        $data = $request->validate([
            'question_ids'   => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'distinct'],
        ]);

        $attached = $quiz->questions()->pluck('quiz_question_pivot.question_id')->map(fn($i) => (int) $i)->all();
        $given    = array_map('intval', $data['question_ids']);

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
