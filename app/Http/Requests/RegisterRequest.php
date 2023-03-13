<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|min:3|max:24|unique:users',
            'email' => 'email|unique:users|nullable',
            'password' => 'required|confirmed|min:8',
        ];
    }
}
