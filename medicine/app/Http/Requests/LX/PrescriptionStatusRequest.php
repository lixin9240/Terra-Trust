<?php

namespace App\Http\Requests\LX;

use Illuminate\Foundation\Http\FormRequest;

class PrescriptionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|integer|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => '状态不能为空',
            'status.integer'  => '状态必须为整数',
            'status.in'       => '状态值无效，0=停用 1=启用',
        ];
    }
}
