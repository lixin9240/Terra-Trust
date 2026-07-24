<?php

namespace App\Http\Requests\LX;

use Illuminate\Foundation\Http\FormRequest;

class BatchDeleteMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => '请选择要删除的药物',
            'ids.array'    => '参数格式错误',
            'ids.min'      => '请至少选择一条药物',
            'ids.*.integer'=> '药物ID必须为整数',
        ];
    }
}
