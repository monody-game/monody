<?php

namespace App\Http\Requests;

use App\Enums\GameInteractions;
use App\Rules\GameExistsRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateInteractionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gameId' => ['required', 'string', new GameExistsRule],
            'type' => ['string', new Enum(GameInteractions::class)],
        ];
    }
}
