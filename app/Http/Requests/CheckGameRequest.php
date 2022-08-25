<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckGameRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'game_id' => 'required|string',
        ];
    }
}
