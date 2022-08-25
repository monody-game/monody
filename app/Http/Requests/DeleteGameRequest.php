<?php

namespace App\Http\Requests;

use App\Rules\GameExistsRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteGameRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'game_id' => ['required', 'string', new GameExistsRule()],
        ];
    }
}
