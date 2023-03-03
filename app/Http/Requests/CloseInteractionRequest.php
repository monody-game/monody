<?php

namespace App\Http\Requests;

use App\Rules\GameExistsRule;
use Illuminate\Foundation\Http\FormRequest;

class CloseInteractionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gameId' => ['required', 'string', app(GameExistsRule::class)],
            'id' => ['required', 'uuid'],
        ];
    }
}
