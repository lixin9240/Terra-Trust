<?php

namespace App\Services\WJC;

use App\Exceptions\BusinessException;
use App\Models\WJC\MedicationRecord;
use Illuminate\Pagination\LengthAwarePaginator;

class RecordService
{
    public function byDate(int $userId, string $date): array
    {
        return MedicationRecord::with('medicine:id,name')
            ->where('user_id', $userId)
            ->whereDate('plan_time', $date)
            ->orderBy('plan_time', 'asc')
            ->get()
            ->map(function ($record) {
                $statusMap = [1 => '已服用', 2 => '漏服', 3 => '跳过'];

                return [
                    'record_id'     => $record->id,
                    'medicine_name' => $record->medicine->name ?? '',
                    'plan_time'     => $record->plan_time?->format('Y-m-d H:i:s'),
                    'actual_time'   => $record->actual_time?->format('Y-m-d H:i:s'),
                    'status'        => $record->status,
                    'status_text'   => $statusMap[$record->status] ?? '',
                ];
            })
            ->toArray();
    }

    public function detail(int $recordId, int $userId): array
    {
        $record = MedicationRecord::with(['medicine:id,name', 'prescription:id,medicine_id'])
            ->where('id', $recordId)
            ->where('user_id', $userId)
            ->first();

        if (!$record) {
            throw new BusinessException('服药记录不存在');
        }

        $statusMap = [1 => '已服用', 2 => '漏服', 3 => '跳过'];

        return [
            'record_id'       => $record->id,
            'medicine_id'     => $record->medicine_id,
            'medicine_name'   => $record->medicine->name ?? '',
            'prescription_id' => $record->prescription_id,
            'plan_time'       => $record->plan_time?->format('Y-m-d H:i:s'),
            'actual_time'     => $record->actual_time?->format('Y-m-d H:i:s'),
            'dosage'          => $record->dosage_taken,
            'status'          => $record->status,
            'status_text'     => $statusMap[$record->status] ?? '',
            'note'            => $record->note,
            'create_time'     => $record->create_time?->format('Y-m-d H:i:s'),
        ];
    }

    public function monthStat(int $userId, int $year, int $month): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $stats = MedicationRecord::where('user_id', $userId)
            ->whereBetween('plan_time', [$startDate, $endDate . ' 23:59:59'])
            ->selectRaw("
                COUNT(*) as total_plan,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as taken,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as miss,
                SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as skip
            ")
            ->first();

        $totalPlan = (int) ($stats->total_plan ?? 0);
        $taken = (int) ($stats->taken ?? 0);

        return [
            'total_plan'   => $totalPlan,
            'taken'        => $taken,
            'miss'         => (int) ($stats->miss ?? 0),
            'skip'         => (int) ($stats->skip ?? 0),
            'complete_rate'=> $totalPlan > 0 ? round($taken / $totalPlan * 100, 1) . '%' : '0%',
        ];
    }

    public function missList(int $userId, int $page, int $size): LengthAwarePaginator
    {
        return MedicationRecord::with('medicine:id,name')
            ->where('user_id', $userId)
            ->where('status', MedicationRecord::STATUS_MISSED)
            ->select(['id', 'medicine_id', 'plan_time', 'status', 'note'])
            ->orderBy('plan_time', 'desc')
            ->paginate($size, ['*'], 'page', $page)
            ->through(function ($record) {
                return [
                    'record_id'     => $record->id,
                    'medicine_name' => $record->medicine->name ?? '',
                    'plan_time'     => $record->plan_time?->format('Y-m-d H:i:s'),
                    'status'        => $record->status,
                    'status_text'   => '漏服',
                    'note'          => $record->note,
                ];
            });
    }
}
