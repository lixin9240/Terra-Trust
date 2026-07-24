<?php

namespace App\Http\Requests\LX;

use Illuminate\Foundation\Http\FormRequest;

class CheckConflictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medicine_ids'   => 'required|array|min:2',
            'medicine_ids.*' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'medicine_ids.required' => '请选择要检测的药物',
            'medicine_ids.array'    => '参数格式错误',
            'medicine_ids.min'      => '至少需要2种药物才能检测冲突',
            'medicine_ids.*.integer'=> '药物ID必须为整数',
        ];
    }
}
