<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserJoinedVocalChannelRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'discord_id' => 'string|required',
        ];
    }
}
