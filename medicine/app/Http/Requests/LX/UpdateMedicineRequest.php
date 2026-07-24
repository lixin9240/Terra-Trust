<?php

namespace App\Http\Requests\LX;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'sometimes|string|max:100',
            'specification' => 'nullable|string|max:100',
            'manufacturer'  => 'nullable|string|max:200',
            'expire_date'   => 'nullable|date_format:Y-m-d',
            'stock'         => 'sometimes|integer|min:0',
            'unit'          => 'sometimes|string|max:20',
            'usage'         => 'nullable|string|max:500',
            'note'          => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max'                => '药物名称最多100位',
            'specification.max'       => '规格最多100位',
            'manufacturer.max'        => '生产厂家最多200位',
            'expire_date.date_format' => '有效期格式应为YYYY-MM-DD',
            'stock.integer'           => '库存数量必须为整数',
            'stock.min'               => '库存数量不能小于0',
            'unit.max'                => '单位最多20位',
            'usage.max'               => '服用方式最多500位',
            'note.max'                => '备注最多500位',
        ];
    }
}
