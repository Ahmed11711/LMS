<?php

namespace App\Http\Controllers\Admin\QuizQuestion;

use App\Repositories\QuizQuestion\QuizQuestionRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\QuizQuestion\QuizQuestionStoreRequest;
use App\Http\Requests\Admin\QuizQuestion\QuizQuestionUpdateRequest;
use App\Http\Resources\Admin\QuizQuestion\QuizQuestionResource;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Question bank (7 types: mcq, multiple_response, true_false, short_answer,
 * fill_blanks, numeric, essay).
 *
 * Request contract (validated keys the Store/Update requests must expose):
 *  Common   : type, question_text, image_path (file), remove_image (bool, update),
 *             difficulty, points, tags[], explanation, meta{}
 *  mcq / multiple_response : answers[i][text|image|image_path|is_correct], keep_answers_order
 *  true_false   : correct_answer (bool)
 *  short_answer : answers = "a | b" | ["a","b"]
 *  fill_blanks  : answers = "a | b" | ["a","b"] | [{text, blank_index}]
 *  numeric      : meta.numeric = {value, unit, tolerance_percent, exact}   (no answer rows)
 *  essay        : model_answer -> meta.model_answer                         (no answer rows)
 */
class QuizQuestionController extends BaseController
{
    /** Captured in beforeStore/beforeUpdate, consumed in afterStore/afterUpdate. */
    private mixed $answersPayload = null;
    private ?bool $correctAnswer = null;
    private bool $hasAnswersInput = false;

    /** BaseController passes the same record as "old" and "new" in afterUpdate, so snapshot the type. */
    private ?string $oldType = null;

    public function __construct(QuizQuestionRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'QuizQuestion',
            fileFields: ['image_path']
        );

        $this->storeRequestClass = QuizQuestionStoreRequest::class;
        $this->updateRequestClass = QuizQuestionUpdateRequest::class;
        $this->resourceClass = QuizQuestionResource::class;
        $this->withRelationships = ['answers'];
    }

    /* ======================================================================
     |  Index filters: ?type=mcq,essay &difficulty=easy &tags=a,b &q=text
     * ====================================================================*/
    protected function applyScoping($query)
    {
        $query = parent::applyScoping($query);

        if (!request()->isMethod('get')) {
            return $query;
        }

        $table = $query->getModel()->getTable();

        if ($type = request()->query('type')) {
            $query->whereIn("{$table}.type", is_array($type) ? $type : explode(',', (string) $type));
        }

        if ($difficulty = request()->query('difficulty')) {
            $query->whereIn("{$table}.difficulty", is_array($difficulty) ? $difficulty : explode(',', (string) $difficulty));
        }

        if ($tags = request()->query('tags')) {
            $tags = is_array($tags) ? $tags : explode(',', (string) $tags);
            $tags = array_values(array_unique(array_filter(array_map('trim', $tags), fn($t) => $t !== '')));

            if ($tags) {
                $query->whereRaw("{$table}.tags @> ?::jsonb", [json_encode($tags, JSON_UNESCAPED_UNICODE)]);
            }
        }

        if ($q = trim((string) request()->query('q', ''))) {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $query->where("{$table}.question_text", 'ILIKE', $like);
        }

        return $query;
    }

    /* ======================================================================
     |  Hooks
     * ====================================================================*/
    protected function beforeStore(array $data, Request $request): array
    {
        $this->captureAnswerInput($data);

        return $this->prepareMeta($data, []);
    }

    protected function afterStore($record, Request $request): void
    {
        $this->syncAnswers($record, $request, []);
    }

    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        $this->oldType = $existingRecord->type;
        $this->captureAnswerInput($data);

        // remove the question image (BaseController only replaces it when a new file is sent)
        if ($request->boolean('remove_image') && !$request->hasFile('image_path')) {
            $this->deleteFile($existingRecord->image_path);
            $data['image_path'] = null;
        }
        unset($data['remove_image']);

        $typeChanged = isset($data['type']) && $data['type'] !== $existingRecord->type;

        return $this->prepareMeta($data, $typeChanged ? [] : ($existingRecord->meta ?? []));
    }

    protected function afterUpdate($updatedRecord, $oldRecord, Request $request): void
    {
        $typeChanged = $this->oldType !== null && $this->oldType !== $updatedRecord->type;

        // No answers sent and the type is unchanged -> keep the existing answers.
        if (!$this->hasAnswersInput && !$typeChanged) {
            return;
        }

        $oldImages = $updatedRecord->answers()->whereNotNull('image_path')->pluck('image_path')->all();

        $this->syncAnswers($updatedRecord, $request, $oldImages);
    }

    /* ======================================================================
     |  Input handling
     * ====================================================================*/
    private function captureAnswerInput(array &$data): void
    {
        $this->hasAnswersInput = array_key_exists('answers', $data) || array_key_exists('correct_answer', $data);
        $this->answersPayload = $data['answers'] ?? null;
        $this->correctAnswer = array_key_exists('correct_answer', $data)
            ? filter_var($data['correct_answer'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        unset($data['answers'], $data['correct_answer']);
    }

    private function prepareMeta(array $data, array $existing): array
    {
        $meta = $existing;

        if (isset($data['meta']) && is_array($data['meta'])) {
            $meta = array_merge($meta, $data['meta']);
        }

        if (array_key_exists('keep_answers_order', $data)) {
            $meta['keep_answers_order'] = filter_var($data['keep_answers_order'], FILTER_VALIDATE_BOOLEAN);
        }

        if (array_key_exists('model_answer', $data)) {
            $model = trim((string) $data['model_answer']);

            if ($model === '') {
                unset($meta['model_answer']);
            } else {
                $meta['model_answer'] = $model;
            }
        }

        if (isset($meta['numeric']) && is_array($meta['numeric'])) {
            $exact = filter_var($meta['numeric']['exact'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $meta['numeric'] = [
                'value' => isset($meta['numeric']['value']) ? (float) $meta['numeric']['value'] : null,
                'unit' => isset($meta['numeric']['unit']) && $meta['numeric']['unit'] !== ''
                    ? trim((string) $meta['numeric']['unit'])
                    : null,
                'exact' => $exact,
                // "exact" has priority over tolerance
                'tolerance_percent' => ($exact || !isset($meta['numeric']['tolerance_percent']))
                    ? null
                    : (float) $meta['numeric']['tolerance_percent'],
            ];
        }

        unset($data['keep_answers_order'], $data['model_answer']);
        $data['meta'] = $meta ?: null;

        return $data;
    }

    /* ======================================================================
     |  Files (answer images; the question image goes through $fileFields)
     * ====================================================================*/
    private function storeUpload(UploadedFile $file, string $subDir = ''): string
    {
        $clean = preg_replace('/\s+/', '_', trim(urldecode($file->getClientOriginalName())));
        $filename = time() . '_' . Str::random(6) . '_' . $clean;
        $dir = "uploads/{$this->collectionName}" . ($subDir !== '' ? "/{$subDir}" : '');

        return '/storage/' . $file->storeAs($dir, $filename, $this->uploadDisk);
    }

    private function deleteFile(?string $publicPath): void
    {
        if ($publicPath) {
            Storage::disk($this->uploadDisk)->delete(Str::replaceFirst('/storage/', '', $publicPath));
        }
    }

    /* ======================================================================
     |  Answers sync (runs inside BaseController's DB transaction)
     * ====================================================================*/
    private function syncAnswers($question, Request $request, array $oldImages): void
    {
        $rows = match ($question->type) {
            'mcq', 'multiple_response' => $this->choiceRows($question->type, $request, $oldImages),
            'true_false' => $this->trueFalseRows(),
            'short_answer', 'fill_blanks' => $this->textRows($question->type),
            default => [], // numeric, essay: no answer rows
        };

        $kept = array_filter(array_column($rows, 'image_path'));

        $question->answers()->delete();

        if ($rows) {
            $question->answers()->createMany($rows);
        }

        foreach (array_diff($oldImages, $kept) as $unused) {
            $this->deleteFile($unused);
        }
    }

    /** mcq / multiple_response */
    private function choiceRows(string $type, Request $request, array $oldImages): array
    {
        $answers = is_array($this->answersPayload) ? $this->answersPayload : [];
        $rows = [];
        $position = 0;

        foreach ($answers as $key => $answer) {
            if (!is_array($answer)) {
                continue;
            }

            $text = trim((string) ($answer['text'] ?? ''));
            $file = $request->file("answers.{$key}.image");

            $imagePath = null;
            if ($file instanceof UploadedFile) {
                $imagePath = $this->storeUpload($file, 'answers');
            } elseif (!empty($answer['image_path']) && in_array($answer['image_path'], $oldImages, true)) {
                $imagePath = $answer['image_path']; // keep the existing image
            }

            if ($text === '' && !$imagePath) {
                continue;
            }

            $rows[] = [
                'text' => $text !== '' ? $text : null,
                'image_path' => $imagePath,
                'is_correct' => filter_var($answer['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'blank_index' => null,
                'position' => $position++,
            ];
        }

        $correct = count(array_filter($rows, fn($r) => $r['is_correct']));

        if (count($rows) < 2 || count($rows) > 10) {
            abort(422, 'عدد الإجابات لازم يكون من 2 إلى 10.');
        }

        if ($type === 'mcq' && $correct !== 1) {
            abort(422, 'سؤال الاختيار من متعدد لازم يكون له إجابة صحيحة واحدة بالظبط.');
        }

        if ($type === 'multiple_response' && $correct < 1) {
            abort(422, 'لازم تحدد إجابة صحيحة واحدة على الأقل.');
        }

        return $rows;
    }

    /** true_false: two fixed rows ("true" / "false") */
    private function trueFalseRows(): array
    {
        $correct = $this->correctAnswer;

        if ($correct === null) {
            abort(422, 'حدد الإجابة الصحيحة (صح أو خطأ).');
        }

        return [
            ['text' => 'true', 'image_path' => null, 'is_correct' => $correct, 'blank_index' => null, 'position' => 0],
            ['text' => 'false', 'image_path' => null, 'is_correct' => !$correct, 'blank_index' => null, 'position' => 1],
        ];
    }

    /** short_answer / fill_blanks: one row per accepted answer ("a | b" is split here too) */
    private function textRows(string $type): array
    {
        $payload = $this->answersPayload;

        if (is_string($payload)) {
            $payload = [$payload];
        }

        $items = [];

        foreach ((array) $payload as $item) {
            if (is_array($item)) {
                $text = (string) ($item['text'] ?? '');
                $blank = (int) ($item['blank_index'] ?? 1);
            } else {
                $text = (string) $item;
                $blank = 1;
            }

            foreach (explode('|', $text) as $part) {
                $part = trim($part);

                if ($part !== '') {
                    $items[] = ['text' => $part, 'blank' => $type === 'fill_blanks' ? max(1, $blank) : null];
                }
            }
        }

        // unique (case-insensitive) per blank
        $seen = [];
        $rows = [];
        $position = 0;

        foreach ($items as $it) {
            $k = ($it['blank'] ?? 0) . '|' . mb_strtolower($it['text']);

            if (isset($seen[$k])) {
                continue;
            }

            $seen[$k] = true;

            $rows[] = [
                'text' => $it['text'],
                'image_path' => null,
                'is_correct' => true,
                'blank_index' => $it['blank'],
                'position' => $position++,
            ];
        }

        if (!$rows) {
            abort(422, 'لازم تضيف إجابة صحيحة واحدة على الأقل.');
        }

        if ($type === 'fill_blanks') {
            $indexes = array_unique(array_column($rows, 'blank_index'));

            for ($i = 1, $max = max($indexes); $i <= $max; $i++) {
                if (!in_array($i, $indexes, true)) {
                    abort(422, "الفراغ رقم {$i} ملوش إجابة صحيحة.");
                }
            }
        }

        return $rows;
    }
}
