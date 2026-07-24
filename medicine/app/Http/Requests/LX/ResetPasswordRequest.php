<?php

namespace App\Http\Requests\LX;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'        => 'required|string',
            'code'         => 'required|string',
            'new_password' => 'required|string|min:6|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required'        => '手机号不能为空',
            'code.required'         => '验证码不能为空',
            'new_password.required' => '新密码不能为空',
            'new_password.min'      => '新密码至少6位',
            'new_password.max'      => '新密码最多20位',
        ];
    }
}
