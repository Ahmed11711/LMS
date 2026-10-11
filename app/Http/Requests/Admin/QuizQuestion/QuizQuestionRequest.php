<?php

namespace App\Http\Requests\Admin\QuizQuestion;

use App\Models\QuizQuestion;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Shared rules for store/update. Rules depend on the (effective) question type.
 * LaTeX in question_text / answers is plain text: nothing here strips $ \ ^ _ { }.
 */
abstract class QuizQuestionRequest extends BaseRequest
{
    public const TYPES = ['mcq', 'multiple_response', 'true_false', 'short_answer', 'fill_blanks', 'numeric', 'essay'];

    public const DIFFICULTIES = ['easy', 'medium', 'hard'];

    abstract protected function isStore(): bool;

    public function authorize(): bool
    {
        return true;
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                           */
    /* ------------------------------------------------------------------ */

    private ?string $existingType = null;
    private bool $existingTypeLoaded = false;

    private function existingType(): ?string
    {
        if (!$this->existingTypeLoaded) {
            $id = $this->route('id');
            $this->existingType = $id ? QuizQuestion::query()->whereKey($id)->value('type') : null;
            $this->existingTypeLoaded = true;
        }

        return $this->existingType;
    }

    protected function effectiveType(): ?string
    {
        $type = $this->input('type');

        if (is_string($type) && $type !== '') {
            return $type;
        }

        return $this->isStore() ? null : $this->existingType();
    }

    /** On update, type-specific fields are mandatory only if the type changes. */
    private function typeChanged(): bool
    {
        return !$this->isStore()
            && is_string($this->input('type'))
            && $this->input('type') !== $this->existingType();
    }

    private function toBool(mixed $value): ?bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /* ------------------------------------------------------------------ */
    /*  Normalisation (multipart sends "true"/"false", "a | b", "x,y")    */
    /* ------------------------------------------------------------------ */

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['remove_image', 'keep_answers_order', 'correct_answer'] as $key) {
            if ($this->has($key)) {
                $merge[$key] = $this->toBool($this->input($key));
            }
        }

        // tags: array or "a, b" -> trimmed unique array of strings
        if ($this->has('tags')) {
            $tags = $this->input('tags');

            if (is_string($tags)) {
                $tags = preg_split('/[,،]/u', $tags) ?: [];
            }

            if (is_array($tags)) {
                $tags = array_values(array_unique(array_filter(
                    array_map(fn($t) => is_scalar($t) ? trim((string) $t) : '', $tags),
                    fn($t) => $t !== ''
                )));
            }

            $merge['tags'] = $tags;
        }

        // meta: only meta.numeric is accepted from the client
        if ($this->has('meta')) {
            $numeric = $this->input('meta.numeric');

            if (is_array($numeric) && array_key_exists('exact', $numeric)) {
                $numeric['exact'] = $this->toBool($numeric['exact']);
            }

            $merge['meta'] = is_array($numeric) ? ['numeric' => $numeric] : [];
        }

        // answers
        if ($this->has('answers')) {
            $type    = $this->effectiveType();
            $answers = $this->input('answers');

            if (in_array($type, ['mcq', 'multiple_response'], true) && is_array($answers)) {
                foreach ($answers as $k => $answer) {
                    if (is_array($answer) && array_key_exists('is_correct', $answer)) {
                        $answers[$k]['is_correct'] = $this->toBool($answer['is_correct']);
                    }
                }
            } elseif (in_array($type, ['short_answer', 'fill_blanks'], true)) {
                $answers = $this->normalizeTextAnswers($answers, $type);
            }

            $merge['answers'] = $answers;
        }

        if ($merge) {
            $this->merge($merge);
        }
    }

    /**
     * "have seen | seen"            -> short_answer: ["have seen","seen"]
     *                                  fill_blanks : [{text, blank_index:1}, ...]
     * Accepts string, list of strings, or (fill_blanks) list of {text, blank_index}.
     */
    private function normalizeTextAnswers(mixed $answers, string $type): mixed
    {
        if (is_string($answers)) {
            $answers = [$answers];
        }

        if (!is_array($answers)) {
            return $answers;
        }

        $out = [];

        foreach ($answers as $item) {
            if (is_array($item)) {
                $text  = (string) ($item['text'] ?? '');
                $blank = $item['blank_index'] ?? 1;
            } elseif (is_scalar($item)) {
                $text  = (string) $item;
                $blank = 1;
            } else {
                continue;
            }

            foreach (explode('|', $text) as $part) {
                $part = trim($part);

                if ($part === '') {
                    continue;
                }

                $out[] = $type === 'fill_blanks'
                    ? ['text' => $part, 'blank_index' => ($blank === null || $blank === '') ? 1 : $blank]
                    : $part;
            }
        }

        return $out;
    }

    /* ------------------------------------------------------------------ */
    /*  Rules                                                             */
    /* ------------------------------------------------------------------ */

    public function rules(): array
    {
        $lead = $this->isStore() ? ['required'] : ['sometimes', 'required'];

        $rules = [
            'type'          => [...$lead, Rule::in(self::TYPES)],
            'question_text' => [...$lead, 'string', 'max:10000'],
            'image_path'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image'  => ['sometimes', 'boolean'],
            'difficulty'    => ['nullable', Rule::in(self::DIFFICULTIES)],
            'points'        => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'tags'          => ['nullable', 'array', 'max:30'],
            'tags.*'        => ['string', 'max:30'],
            'explanation'   => ['nullable', 'string', 'max:10000'],
        ];

        $req = ($this->isStore() || $this->typeChanged()) ? 'required' : 'sometimes';
        $fileRule = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        switch ($this->effectiveType()) {
            case 'mcq':
            case 'multiple_response':
                $rules += [
                    'answers'              => [$req, 'array', 'min:2', 'max:10'],
                    'answers.*'            => ['array'],
                    'answers.*.text'       => ['nullable', 'string', 'max:2000'],
                    'answers.*.image'      => $fileRule,
                    'answers.*.image_path' => ['nullable', 'string', 'max:500'],
                    'answers.*.is_correct' => ['nullable', 'boolean'],
                    'keep_answers_order'   => ['sometimes', 'boolean'],
                ];
                break;

            case 'true_false':
                $rules += [
                    'correct_answer' => [$req, 'boolean'],
                ];
                break;

            case 'short_answer':
                $rules += [
                    'answers'   => [$req, 'array', 'min:1', 'max:50'],
                    'answers.*' => ['string', 'max:500'],
                ];
                break;

            case 'fill_blanks':
                $rules += [
                    'answers'                => [$req, 'array', 'min:1', 'max:100'],
                    'answers.*.text'         => ['required', 'string', 'max:500'],
                    'answers.*.blank_index'  => ['nullable', 'integer', 'min:1', 'max:20'],
                ];
                break;

            case 'numeric':
                $rules += [
                    'meta.numeric'                   => [$req, 'array'],
                    'meta.numeric.value'             => [$req, 'numeric'],
                    'meta.numeric.unit'              => ['nullable', 'string', 'max:30'],
                    'meta.numeric.tolerance_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
                    'meta.numeric.exact'             => ['nullable', 'boolean'],
                ];
                break;

            case 'essay':
                $rules += [
                    'model_answer' => ['nullable', 'string', 'max:10000'],
                ];
                break;
        }

        return $rules;
    }

    /* ------------------------------------------------------------------ */
    /*  Cross-field checks                                                */
    /* ------------------------------------------------------------------ */

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty() || !$this->has('answers')) {
                return;
            }

            $type    = $this->effectiveType();
            $answers = $this->input('answers');

            if (in_array($type, ['mcq', 'multiple_response'], true) && is_array($answers)) {
                $correct = 0;

                foreach ($answers as $k => $answer) {
                    $hasText  = trim((string) ($answer['text'] ?? '')) !== '';
                    $hasImage = $this->hasFile("answers.{$k}.image") || !empty($answer['image_path']);

                    if (!$hasText && !$hasImage) {
                        $v->errors()->add("answers.{$k}", 'كل إجابة لازم يكون ليها نص أو صورة.');
                    }

                    if (!empty($answer['is_correct'])) {
                        $correct++;
                    }
                }

                if ($type === 'mcq' && $correct !== 1) {
                    $v->errors()->add('answers', 'لازم تحدد إجابة صحيحة واحدة بالظبط.');
                }

                if ($type === 'multiple_response' && $correct < 1) {
                    $v->errors()->add('answers', 'لازم تحدد إجابة صحيحة واحدة على الأقل.');
                }
            }

            if ($type === 'fill_blanks' && is_array($answers) && $answers) {
                $indexes = array_unique(array_map(fn($a) => (int) ($a['blank_index'] ?? 1), $answers));

                for ($i = 1, $max = max($indexes); $i <= $max; $i++) {
                    if (!in_array($i, $indexes, true)) {
                        $v->errors()->add('answers', "الفراغ رقم {$i} ملوش إجابة صحيحة.");
                    }
                }
            }
        });
    }

    /** Drop null points so the DB default (2) applies. */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (array_key_exists('points', $data) && $data['points'] === null) {
            unset($data['points']);
        }

        return $key === null ? $data : data_get($data, $key, $default);
    }

    /* ------------------------------------------------------------------ */
    /*  Messages                                                          */
    /* ------------------------------------------------------------------ */

    public function messages(): array
    {
        return [
            'required'                   => 'حقل :attribute مطلوب.',
            'string'                     => 'حقل :attribute لازم يكون نص.',
            'max.string'                 => 'حقل :attribute طويل أكتر من اللازم.',
            'boolean'                    => 'حقل :attribute لازم يكون صح أو خطأ.',
            'numeric'                    => 'حقل :attribute لازم يكون رقم.',
            'in'                         => 'القيمة المختارة في :attribute غير صالحة.',
            'array'                      => 'حقل :attribute لازم يكون قائمة.',
            'answers.min'                => 'عدد الإجابات أقل من المطلوب.',
            'answers.max'                => 'عدد الإجابات أكتر من المسموح.',
            'image_path.mimes'                => 'الصورة لازم تكون jpg أو png أو webp.',
            'image_path.max'                  => 'حجم الصورة لازم ما يزيدش عن 5 ميجا.',
            'answers.*.image.mimes'      => 'صورة الإجابة لازم تكون jpg أو png أو webp.',
            'answers.*.image.max'        => 'حجم صورة الإجابة لازم ما يزيدش عن 5 ميجا.',
            'tags.*.max'                 => 'الوسم الواحد لا يزيد عن 30 حرف.',
            'points.min'                 => 'الدرجة لا يمكن أن تكون أقل من صفر.',
            'meta.numeric.value.required' => 'القيمة الرقمية مطلوبة.',
            'meta.numeric.value.numeric'  => 'القيمة الرقمية لازم تكون رقم.',
            'correct_answer.required'    => 'حدد الإجابة الصحيحة (صح أو خطأ).',
        ];
    }

    public function attributes(): array
    {
        return [
            'type'                           => 'نوع السؤال',
            'question_text'                  => 'نص السؤال',
            'image_path'                          => 'الصورة',
            'difficulty'                     => 'مستوى السؤال',
            'points'                         => 'الدرجة',
            'tags'                           => 'الوسوم',
            'explanation'                    => 'شرح الإجابة',
            'answers'                        => 'الإجابات',
            'answers.*.text'                 => 'نص الإجابة',
            'answers.*.blank_index'          => 'رقم الفراغ',
            'correct_answer'                 => 'الإجابة الصحيحة',
            'model_answer'                   => 'نموذج الإجابة',
            'meta.numeric'                   => 'الإجابة الرقمية',
            'meta.numeric.value'             => 'القيمة',
            'meta.numeric.unit'              => 'الوحدة',
            'meta.numeric.tolerance_percent' => 'نسبة السماح',
        ];
    }
}
