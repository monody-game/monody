<?php

namespace App\Http\Requests;

use App\Enums\InteractionActions;
use App\Rules\GameExistsRule;
use App\Rules\InGameRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class InteractionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['uuid', 'required'],
            'targetId' => ['exists:users,id', new InGameRule(null)],
            'gameId' => ['string', new GameExistsRule(), 'required'],
            'action' => [new Enum(InteractionActions::class), 'required'],
        ];
    }
}
