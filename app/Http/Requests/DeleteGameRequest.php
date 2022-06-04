<?php

namespace App\Http\Requests;

use App\Rules\GameExists;
use Illuminate\Foundation\Http\FormRequest;

class DeleteGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'game_id' => ['required', 'string', new GameExists()],
        ];
    }
}
