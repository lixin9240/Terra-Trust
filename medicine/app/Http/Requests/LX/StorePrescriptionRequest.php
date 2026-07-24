<?php

namespace App\Http\Requests\LX;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medicine_id' => 'required|integer|exists:medicines,id',
            'doctor_name' => 'nullable|string|max:50',
            'hospital'    => 'nullable|string|max:200',
            'diagnosis'   => 'nullable|string|max:500',
            'dosage'      => 'required|string|max:50',
            'frequency'   => 'required|string|max:50',
            'start_date'  => 'required|date_format:Y-m-d',
            'end_date'    => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'note'        => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'medicine_id.required'        => '药物ID不能为空',
            'medicine_id.exists'          => '所选药物不存在',
            'doctor_name.max'             => '医生姓名最多50位',
            'hospital.max'                => '医院名称最多200位',
            'diagnosis.max'               => '诊断最多500位',
            'dosage.required'             => '单次剂量不能为空',
            'dosage.max'                  => '单次剂量最多50位',
            'frequency.required'          => '服用频率不能为空',
            'frequency.max'               => '服用频率最多50位',
            'start_date.required'         => '开始日期不能为空',
            'start_date.date_format'      => '开始日期格式应为YYYY-MM-DD',
            'end_date.date_format'        => '结束日期格式应为YYYY-MM-DD',
            'end_date.after_or_equal'     => '结束日期不能早于开始日期',
            'note.max'                    => '备注最多500位',
        ];
    }
}
