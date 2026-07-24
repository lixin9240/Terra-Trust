<?php

namespace App\Http\Requests\LX;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medicine_id' => 'sometimes|integer|exists:medicines,id',
            'doctor_name' => 'nullable|string|max:50',
            'hospital'    => 'nullable|string|max:200',
            'diagnosis'   => 'nullable|string|max:500',
            'dosage'      => 'sometimes|string|max:50',
            'frequency'   => 'sometimes|string|max:50',
            'start_date'  => 'sometimes|date_format:Y-m-d',
            'end_date'    => 'nullable|date_format:Y-m-d',
            'note'        => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'medicine_id.exists'      => '所选药物不存在',
            'doctor_name.max'         => '医生姓名最多50位',
            'hospital.max'            => '医院名称最多200位',
            'diagnosis.max'           => '诊断最多500位',
            'dosage.max'              => '单次剂量最多50位',
            'frequency.max'           => '服用频率最多50位',
            'start_date.date_format'  => '开始日期格式应为YYYY-MM-DD',
            'end_date.date_format'    => '结束日期格式应为YYYY-MM-DD',
            'note.max'                => '备注最多500位',
        ];
    }
}
