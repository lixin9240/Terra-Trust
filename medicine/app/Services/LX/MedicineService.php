<?php

namespace App\Services\LX;

use App\Exceptions\BusinessException;
use App\Models\WJC\Medicine;
use App\Models\WJC\Prescription;
use App\Models\WJC\Reminder;
use App\Models\WJC\MedicationRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicineService
{
    /**
     * 3.1 添加药物
     */
    public function create(array $data, int $userId): Medicine
    {
        $data['user_id'] = $userId;

        $medicine = Medicine::create($data);

        Log::channel('business')->info('用户添加药物', [
            'user_id'     => $userId,
            'medicine_id' => $medicine->id,
            'name'        => $medicine->name,
        ]);

        return $medicine;
    }

    /**
     * 3.2 药物列表
     */
    public function list(int $userId, int $page, int $size): LengthAwarePaginator
    {
        return Medicine::where('user_id', $userId)
            ->select([
                'id', 'name', 'specification', 'stock', 'unit',
                'expire_date', 'create_time',
            ])
            ->orderBy('create_time', 'desc')
            ->paginate($size, ['*'], 'page', $page)
            ->through(function ($medicine) {
                return [
                    'medicine_id'  => $medicine->id,
                    'name'         => $medicine->name,
                    'specification'=> $medicine->specification,
                    'stock'        => $medicine->stock,
                    'unit'         => $medicine->unit,
                    'expire_date'  => $medicine->expire_date?->format('Y-m-d'),
                    'create_time'  => $medicine->create_time?->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * 3.3 修改药物
     */
    public function update(int $medicineId, array $data, int $userId): void
    {
        $medicine = $this->findUserMedicine($medicineId, $userId);

        $medicine->update($data);

        Log::channel('business')->info('用户修改药物', [
            'user_id'     => $userId,
            'medicine_id' => $medicineId,
        ]);
    }

    /**
     * 3.4 删除药物
     */
    public function delete(int $medicineId, int $userId): void
    {
        $medicine = $this->findUserMedicine($medicineId, $userId);

        DB::transaction(function () use ($medicine) {
            // 获取关联的医嘱ID列表
            $prescriptionIds = Prescription::where('medicine_id', $medicine->id)->pluck('id');

            // 删除关联的服药记录
            if ($prescriptionIds->isNotEmpty()) {
                MedicationRecord::whereIn('prescription_id', $prescriptionIds)->delete();
            }

            // 删除关联的提醒
            Reminder::where('medicine_id', $medicine->id)->delete();

            // 删除关联的医嘱
            Prescription::where('medicine_id', $medicine->id)->delete();

            // 删除药物
            $medicine->delete();
        });

        Log::channel('business')->info('用户删除药物', [
            'user_id'     => $userId,
            'medicine_id' => $medicineId,
        ]);
    }

    /**
     * 3.5 药物详情
     */
    public function detail(int $medicineId, int $userId): array
    {
        $medicine = $this->findUserMedicine($medicineId, $userId);

        $prescriptionCount = Prescription::where('medicine_id', $medicineId)->count();

        return [
            'medicine_id'       => $medicine->id,
            'name'              => $medicine->name,
            'specification'     => $medicine->specification,
            'manufacturer'      => $medicine->manufacturer,
            'expire_date'       => $medicine->expire_date?->format('Y-m-d'),
            'stock'             => $medicine->stock,
            'unit'              => $medicine->unit,
            'usage'             => $medicine->usage,
            'note'              => $medicine->note,
            'prescription_count'=> $prescriptionCount,
        ];
    }

    /**
     * 3.6 库存预警药品
     */
    public function warnStock(int $userId): array
    {
        return Medicine::where('user_id', $userId)
            ->where('stock', '<=', 5)
            ->select(['id', 'name', 'stock', 'unit'])
            ->get()
            ->map(function ($medicine) {
                return [
                    'medicine_id' => $medicine->id,
                    'name'        => $medicine->name,
                    'stock'       => $medicine->stock,
                    'unit'        => $medicine->unit,
                ];
            })
            ->toArray();
    }

    /**
     * 3.7 过期药品列表
     */
    public function expired(int $userId): array
    {
        return Medicine::where('user_id', $userId)
            ->where('expire_date', '<', now()->format('Y-m-d'))
            ->select(['id', 'name', 'specification', 'stock', 'unit', 'expire_date', 'create_time'])
            ->orderBy('expire_date', 'asc')
            ->get()
            ->map(function ($medicine) {
                return [
                    'medicine_id'  => $medicine->id,
                    'name'         => $medicine->name,
                    'specification'=> $medicine->specification,
                    'stock'        => $medicine->stock,
                    'unit'         => $medicine->unit,
                    'expire_date'  => $medicine->expire_date?->format('Y-m-d'),
                    'create_time'  => $medicine->create_time?->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }

    /**
     * 3.8 批量删除药物
     */
    public function batchDelete(array $ids, int $userId): void
    {
        $medicines = Medicine::whereIn('id', $ids)
            ->where('user_id', $userId)
            ->get();

        if ($medicines->isEmpty()) {
            throw new BusinessException('未找到可删除的药物');
        }

        DB::transaction(function () use ($ids) {
            // 获取关联的医嘱ID列表
            $prescriptionIds = Prescription::whereIn('medicine_id', $ids)->pluck('id');

            // 删除关联的服药记录
            if ($prescriptionIds->isNotEmpty()) {
                MedicationRecord::whereIn('prescription_id', $prescriptionIds)->delete();
            }

            // 删除关联的提醒
            Reminder::whereIn('medicine_id', $ids)->delete();

            // 删除关联的医嘱
            Prescription::whereIn('medicine_id', $ids)->delete();

            // 批量删除药物
            Medicine::whereIn('id', $ids)->delete();
        });

        Log::channel('business')->info('用户批量删除药物', [
            'user_id'       => $userId,
            'medicine_ids'  => $ids,
        ]);
    }

    /**
     * 查找属于当前用户的药物
     */
    private function findUserMedicine(int $medicineId, int $userId): Medicine
    {
        $medicine = Medicine::where('id', $medicineId)
            ->where('user_id', $userId)
            ->first();

        if (!$medicine) {
            throw new BusinessException('药物不存在');
        }

        return $medicine;
    }
}
