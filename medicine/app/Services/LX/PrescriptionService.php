<?php

namespace App\Services\LX;

use App\Exceptions\BusinessException;
use App\Models\WJC\DrugConflict;
use App\Models\WJC\Medicine;
use App\Models\WJC\MedicationRecord;
use App\Models\WJC\Prescription;
use App\Models\WJC\Reminder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrescriptionService
{
    /**
     * 4.1 添加医嘱
     */
    public function create(array $data, int $userId): Prescription
    {
        $data['user_id'] = $userId;
        $data['status'] = 1;

        $prescription = Prescription::create($data);

        Log::channel('business')->info('用户创建医嘱', [
            'user_id'         => $userId,
            'prescription_id' => $prescription->id,
            'medicine_id'     => $data['medicine_id'],
        ]);

        return $prescription;
    }

    /**
     * 4.2 医嘱列表
     */
    public function list(int $userId, int $page, int $size, ?int $status): LengthAwarePaginator
    {
        $query = Prescription::with('medicine:id,name')
            ->where('user_id', $userId);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->select([
                'id', 'medicine_id', 'doctor_name', 'hospital', 'diagnosis',
                'dosage', 'frequency', 'start_date', 'end_date',
                'status', 'create_time',
            ])
            ->orderBy('create_time', 'desc')
            ->paginate($size, ['*'], 'page', $page)
            ->through(function ($prescription) {
                return [
                    'prescription_id' => $prescription->id,
                    'medicine_id'     => $prescription->medicine_id,
                    'medicine_name'   => $prescription->medicine->name ?? '',
                    'doctor_name'     => $prescription->doctor_name,
                    'hospital'        => $prescription->hospital,
                    'diagnosis'       => $prescription->diagnosis,
                    'dosage'          => $prescription->dosage,
                    'frequency'       => $prescription->frequency,
                    'start_date'      => $prescription->start_date?->format('Y-m-d'),
                    'end_date'        => $prescription->end_date?->format('Y-m-d'),
                    'status'          => $prescription->status,
                    'create_time'     => $prescription->create_time?->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * 4.3 医嘱详情
     */
    public function detail(int $prescriptionId, int $userId): array
    {
        $prescription = $this->findUserPrescription($prescriptionId, $userId);

        $prescription->load('medicine:id,name,specification');

        // 关联提醒数量
        $remindCount = Reminder::where('prescription_id', $prescriptionId)->count();

        // 服药完成率
        $totalRecords = MedicationRecord::where('prescription_id', $prescriptionId)->count();
        $takenRecords = MedicationRecord::where('prescription_id', $prescriptionId)
            ->where('status', MedicationRecord::STATUS_TAKEN)
            ->count();
        $finishRate = $totalRecords > 0 ? round($takenRecords / $totalRecords * 100) . '%' : '0%';

        return [
            'prescription_id' => $prescription->id,
            'medicine_id'     => $prescription->medicine_id,
            'medicine_name'   => $prescription->medicine->name ?? '',
            'specification'   => $prescription->medicine->specification ?? '',
            'doctor_name'     => $prescription->doctor_name,
            'hospital'        => $prescription->hospital,
            'diagnosis'       => $prescription->diagnosis,
            'dosage'          => $prescription->dosage,
            'frequency'       => $prescription->frequency,
            'start_date'      => $prescription->start_date?->format('Y-m-d'),
            'end_date'        => $prescription->end_date?->format('Y-m-d'),
            'status'          => $prescription->status,
            'note'            => $prescription->note,
            'remind_count'    => $remindCount,
            'finish_rate'     => $finishRate,
            'create_time'     => $prescription->create_time?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 4.4 修改医嘱
     */
    public function update(int $prescriptionId, array $data, int $userId): void
    {
        $prescription = $this->findUserPrescription($prescriptionId, $userId);

        $prescription->update($data);

        Log::channel('business')->info('用户修改医嘱', [
            'user_id'         => $userId,
            'prescription_id' => $prescriptionId,
        ]);
    }

    /**
     * 4.5 删除医嘱
     */
    public function delete(int $prescriptionId, int $userId): void
    {
        $prescription = $this->findUserPrescription($prescriptionId, $userId);

        DB::transaction(function () use ($prescription) {
            // 删除关联的服药记录
            MedicationRecord::where('prescription_id', $prescription->id)->delete();

            // 删除关联的提醒
            Reminder::where('prescription_id', $prescription->id)->delete();

            // 删除医嘱
            $prescription->delete();
        });

        Log::channel('business')->info('用户删除医嘱', [
            'user_id'         => $userId,
            'prescription_id' => $prescriptionId,
        ]);
    }

    /**
     * 4.6 启用/停用医嘱
     */
    public function updateStatus(int $prescriptionId, int $status, int $userId): void
    {
        $prescription = $this->findUserPrescription($prescriptionId, $userId);

        $prescription->update(['status' => $status]);

        // 停用医嘱时同步关闭关联提醒
        if ($status === 0) {
            Reminder::where('prescription_id', $prescriptionId)->update(['is_active' => 0]);
        }

        Log::channel('business')->info('用户修改医嘱状态', [
            'user_id'         => $userId,
            'prescription_id' => $prescriptionId,
            'status'          => $status,
        ]);
    }

    /**
     * 4.7 药物冲突检测
     */
    public function checkConflict(array $medicineIds, int $userId): array
    {
        // 验证所有药物都属于当前用户
        $userMedicineCount = Medicine::whereIn('id', $medicineIds)
            ->where('user_id', $userId)
            ->count();

        if ($userMedicineCount !== count($medicineIds)) {
            throw new BusinessException('部分药物不存在或不属于当前用户');
        }

        $conflicts = [];

        // 两两检测冲突
        for ($i = 0; $i < count($medicineIds); $i++) {
            for ($j = $i + 1; $j < count($medicineIds); $j++) {
                $idA = $medicineIds[$i];
                $idB = $medicineIds[$j];

                // 查找冲突记录 (药物冲突表使用药品库ID，这里简化处理，直接基于药物名称判断)
                // 实际项目中应根据 drug_library 表关联来检测冲突
                $drugConflict = DrugConflict::where(function ($query) use ($idA, $idB) {
                    $query->where('medicine_a_id', $idA)
                          ->where('medicine_b_id', $idB);
                })->orWhere(function ($query) use ($idA, $idB) {
                    $query->where('medicine_a_id', $idB)
                          ->where('medicine_b_id', $idA);
                })->first();

                if ($drugConflict) {
                    $medicineA = Medicine::find($idA);
                    $medicineB = Medicine::find($idB);

                    $conflicts[] = [
                        'medicine_a'    => $medicineA->name ?? '',
                        'medicine_b'    => $medicineB->name ?? '',
                        'conflict_level'=> $drugConflict->conflict_level,
                        'desc'          => $drugConflict->conflict_desc,
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * 查找属于当前用户的医嘱
     */
    private function findUserPrescription(int $prescriptionId, int $userId): Prescription
    {
        $prescription = Prescription::where('id', $prescriptionId)
            ->where('user_id', $userId)
            ->first();

        if (!$prescription) {
            throw new BusinessException('医嘱不存在');
        }

        return $prescription;
    }
}
