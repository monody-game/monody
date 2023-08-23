<?php

namespace App\Http\Requests;

use App\Rules\RejectMultipleUniqueRoles;
use Illuminate\Foundation\Http\FormRequest;

class CreateGameRequest extends FormRequest
{
    public function rules(): array
    {
        if (app()->runningUnitTests() || app()->isLocal()) {
            return [
                'users' => 'array',
                'roles' => ['array', 'required', new RejectMultipleUniqueRoles()],
                'type' => ['nullable'],
            ];
        }

        return [
            'users' => 'array',
            'roles' => ['array', 'required', new RejectMultipleUniqueRoles(), 'min:5'],
            'type' => ['nullable'],
        ];
    }
}
