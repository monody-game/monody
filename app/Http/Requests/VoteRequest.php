<?php

namespace App\Http\Requests;

use App\Rules\GameExists;
use App\Rules\InGame;
use App\Rules\PlayerAlive;
use Illuminate\Foundation\Http\FormRequest;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'gameId' => ['string', 'required', new GameExists(), new InGame(null)],
            'userId' => ['exists:users,id', 'required', new InGame(), new PlayerAlive()]
        ];
    }
}
