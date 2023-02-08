<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => 'min:3|max:24|string|unique:users|nullable',
            'email' => 'email|unique:users|nullable',
        ];
    }
}
