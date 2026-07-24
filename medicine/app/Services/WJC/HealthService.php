<?php

namespace App\Services\WJC;

use App\Exceptions\BusinessException;
use App\Models\WJC\HealthRecord;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class HealthService
{
    public function create(array $data, int $userId): HealthRecord
    {
        $data['user_id'] = $userId;

        $record = HealthRecord::create($data);

        Log::channel('business')->info('健康记录新增', [
            'user_id'    => $userId,
            'health_id'  => $record->id,
        ]);

        return $record;
    }

    public function list(int $userId, int $page, int $size): LengthAwarePaginator
    {
        return HealthRecord::where('user_id', $userId)
            ->select([
                'id', 'blood_pressure_high', 'blood_pressure_low',
                'blood_sugar', 'weight', 'create_time',
            ])
            ->orderBy('create_time', 'desc')
            ->paginate($size, ['*'], 'page', $page)
            ->through(function ($record) {
                return [
                    'health_id'            => $record->id,
                    'blood_pressure_high'  => $record->blood_pressure_high,
                    'blood_pressure_low'   => $record->blood_pressure_low,
                    'blood_sugar'          => $record->blood_sugar,
                    'weight'               => $record->weight,
                    'create_time'          => $record->create_time?->format('Y-m-d H:i:s'),
                ];
            });
    }

    public function delete(int $recordId, int $userId): void
    {
        $record = HealthRecord::where('id', $recordId)
            ->where('user_id', $userId)
            ->first();

        if (!$record) {
            throw new BusinessException('健康记录不存在');
        }

        $record->delete();

        Log::channel('business')->info('健康记录删除', [
            'user_id'   => $userId,
            'health_id' => $recordId,
        ]);
    }
}
