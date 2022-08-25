<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvatarUploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'avatar' => 'required|file|max:2048|mimes:jpg,png,webp,bmp|dimensions:min_width=200,min_height=200,max_width=1000,max_height=1000',
        ];
    }
}
