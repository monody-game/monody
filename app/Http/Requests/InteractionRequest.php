<?php

namespace App\Http\Requests;

use App\Enums\InteractionActions;
use App\Rules\GameExistsRule;
use App\Rules\InGameRule;
use App\Rules\PlayerAliveRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class InteractionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'interactionId' => ['uuid', 'required'],
            'targetId' => ['exists:users,id', new InGameRule(null), new PlayerAliveRule(), 'required'],
            'gameId' => ['string', new GameExistsRule(), 'required'],
            'interaction' => [new Enum(InteractionActions::class), 'required'],
        ];
    }
}
