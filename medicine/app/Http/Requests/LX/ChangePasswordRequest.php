<?php

namespace App\Http\Requests\LX;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'old_password'     => 'required|string',
            'new_password'     => 'required|string|min:6|max:20',
            'confirm_password' => 'required|string|same:new_password',
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required'      => '原密码不能为空',
            'new_password.required'      => '新密码不能为空',
            'new_password.min'           => '新密码至少6位',
            'new_password.max'           => '新密码最多20位',
            'confirm_password.required'  => '确认密码不能为空',
            'confirm_password.same'      => '两次输入的新密码不一致',
        ];
    }
}
