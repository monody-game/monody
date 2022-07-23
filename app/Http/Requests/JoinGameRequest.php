<?php

namespace App\Http\Requests;

use App\Rules\GameExistsRule;
use Illuminate\Foundation\Http\FormRequest;

class JoinGameRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'userId' => ['uuid', 'exists:users,id'],
            'gameId' => ['string', 'required', new GameExistsRule()]
        ];
    }
}
