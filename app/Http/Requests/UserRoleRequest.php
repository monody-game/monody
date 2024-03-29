<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid', 'exists:users,id'],
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
