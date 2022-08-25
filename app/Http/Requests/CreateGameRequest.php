<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGameRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'users' => 'array',
            'roles' => 'array|required',
            'is_started' => 'bool',
        ];
    }
}
