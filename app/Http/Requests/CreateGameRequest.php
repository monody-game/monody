<?php

namespace App\Http\Requests;

use App\Enums\GameType;
use App\Rules\RejectMultipleUniqueRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateGameRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'users' => 'array',
            'roles' => ['array', 'required', new RejectMultipleUniqueRoles()],
            'type' => [new Enum(GameType::class), 'nullable'],
        ];
    }
}
