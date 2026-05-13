<?php

namespace App\Http\Requests\User\Comment;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserCommentRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'body'      => 'required|string',
            'parent_id' => 'nullable|exists:lesson_comments,id',
        ];
    }
}
