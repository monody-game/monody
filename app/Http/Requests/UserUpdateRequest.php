<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => 'string|nullable',
            'email' => 'email|nullable',
        ];
    }
}
