<?php

namespace App\Http\Requests\LX;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'real_name' => 'nullable|string|max:50',
            'gender'    => 'nullable|integer|in:0,1,2',
            'age'       => 'nullable|integer|min:1|max:200',
            'avatar'    => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'real_name.max' => '真实姓名最多50位',
            'gender.in'     => '性别参数无效',
            'age.min'       => '年龄不能小于1',
            'age.max'       => '年龄不能大于200',
            'avatar.max'    => '头像地址过长',
        ];
    }
}
