<?php

namespace App\Http\Requests;

use App\Rules\RejectMultipleUniqueRoles;
use Illuminate\Foundation\Http\FormRequest;

class CreateGameRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'users' => 'array',
            'roles' => ['array', 'required', new RejectMultipleUniqueRoles()],
        ];
    }
}
