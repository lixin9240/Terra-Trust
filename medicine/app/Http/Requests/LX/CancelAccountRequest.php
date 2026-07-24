<?php

namespace App\Http\Requests\LX;

use Illuminate\Foundation\Http\FormRequest;

class CancelAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => 'required|string|min:6|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => '密码不能为空',
            'password.min'      => '密码至少6位',
            'password.max'      => '密码最多20位',
        ];
    }
}
