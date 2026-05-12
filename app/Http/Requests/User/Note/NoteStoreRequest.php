<?php

namespace App\Http\Requests\User\Note;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NoteStoreRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'body'       => 'required|string',
            'video_time' => 'nullable|integer',
        ];
    }
}
