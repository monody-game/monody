<?php

namespace App\Http\Requests;

use App\Rules\GameExists;
use Illuminate\Foundation\Http\FormRequest;

class AfterVoteRequest extends FormRequest
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
    public function rules()
    {
        return [
            'gameId' => ['string', 'required', new GameExists()],
        ];
    }
}
