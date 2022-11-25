<?php

namespace App\Http\Requests;

use App\Rules\GameExistsRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignRolesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gameId' => ['required', 'string', new GameExistsRule()],
        ];
    }
}
