<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvatarUploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'avatar' => 'required|file|max:2048|mimes:jpg,png,webp,bmp|dimensions:min_width=100,min_height=100,max_width=2500,max_height=2500',
        ];
    }
}
