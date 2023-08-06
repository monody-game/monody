<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AvatarUploadRequest extends FormRequest
{
	protected function failedValidation(Validator $validator)
	{
		dd(ini_get('post_max_size'), ini_get('upload_max_filesize'));
		dd($validator);
	}

    public function rules(): array
    {
        return [
            'avatar' => 'required|file|max:4096|mimes:jpg,png,webp,bmp|dimensions:min_width=100,min_height=100,max_width=2500,max_height=2500',
        ];
    }
}
