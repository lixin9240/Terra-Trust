<?php

namespace App\Http\Requests\LX;

use Illuminate\Foundation\Http\FormRequest;

class SendCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => '手机号不能为空',
        ];
    }
}
