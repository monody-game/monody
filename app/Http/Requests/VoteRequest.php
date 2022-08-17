<?php

namespace App\Http\Requests;

use App\Rules\GameExistsRule;
use App\Rules\InGameRule;
use App\Rules\PlayerAliveRule;
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
            'gameId' => ['string', 'required', new GameExistsRule(), new InGameRule(null)],
            'userId' => ['exists:users,id', 'required', new InGameRule(), new PlayerAliveRule()],
        ];
    }
}
