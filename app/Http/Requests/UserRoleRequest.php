<?php

namespace App\Http\Requests;

use App\Rules\PlayerNotAliveRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid', 'exists:users,id', new PlayerNotAliveRule()],
        ];
    }

    /**
     * Add get parameter to validation input.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['id' => $this->route('id')]);
    }
}
