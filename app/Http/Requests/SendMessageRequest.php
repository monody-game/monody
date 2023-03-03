<?php

namespace App\Http\Requests;

use App\Rules\GameExistsRule;
use App\Rules\InGameRule;
use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gameId' => ['string', 'required', app(GameExistsRule::class), new InGameRule(null)],
            'content' => 'required|string|max:500',
        ];
    }
}
