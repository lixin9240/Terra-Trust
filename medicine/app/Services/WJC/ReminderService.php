<?php

namespace App\Services\WJC;

use App\Exceptions\BusinessException;
use App\Models\WJC\Reminder;
use App\Models\WJC\MedicationRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class ReminderService
{
    public function create(array $data, int $userId): Reminder
    {
        $data['user_id'] = $userId;
        $data['is_active'] = 1;

        $reminder = DB::transaction(function () use ($data, $userId) {
            $reminder = Reminder::create($data);

            MedicationRecord::create([
                'user_id'         => $userId,
                'reminder_id'     => $reminder->id,
                'prescription_id' => $data['prescription_id'] ?? null,
                'medicine_id'     => $data['medicine_id'],
                'plan_time'       => now()->format('Y-m-d') . ' ' . $data['remind_time'],
                'status'          => MedicationRecord::STATUS_TAKEN,
            ]);

            Log::channel('business')->info('服药提醒创建成功', [
                'user_id'    => $userId,
                'remind_id'  => $reminder->id,
                'medicine_id'=> $data['medicine_id'],
            ]);

            return $reminder;
        });

        return $reminder;
    }

    public function todayReminders(int $userId): array
    {
        $today = now()->format('Y-m-d');

        return Reminder::with('medicine:id,name')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->get()
            ->map(function ($reminder) use ($today) {
                $record = MedicationRecord::where('reminder_id', $reminder->id)
                    ->whereDate('plan_time', $today)
                    ->latest('id')
                    ->first();

                return [
                    'remind_id'    => $reminder->id,
                    'medicine_name'=> $reminder->medicine->name ?? '',
                    'remind_time'  => $reminder->remind_time,
                    'dosage'       => $reminder->dosage,
                    'status'       => $record ? $record->status : 0,
                ];
            })
            ->toArray();
    }

    public function take(int $reminderId, int $status, ?string $note, int $userId): void
    {
        $reminder = Reminder::where('id', $reminderId)
            ->where('user_id', $userId)
            ->first();

        if (!$reminder) {
            throw new BusinessException('提醒记录不存在');
        }

        $today = now()->format('Y-m-d');

        DB::transaction(function () use ($reminder, $status, $note, $userId, $today) {
            $record = MedicationRecord::where('reminder_id', $reminder->id)
                ->whereDate('plan_time', $today)
                ->latest('id')
                ->first();

            if ($record) {
                $record->update([
                    'status'      => $status,
                    'actual_time' => $status === MedicationRecord::STATUS_TAKEN ? now() : null,
                    'note'        => $note,
                ]);
            } else {
                MedicationRecord::create([
                    'user_id'         => $userId,
                    'reminder_id'     => $reminder->id,
                    'prescription_id' => $reminder->prescription_id,
                    'medicine_id'     => $reminder->medicine_id,
                    'plan_time'       => $today . ' ' . $reminder->remind_time,
                    'actual_time'     => $status === MedicationRecord::STATUS_TAKEN ? now() : null,
                    'status'          => $status,
                    'note'            => $note,
                ]);
            }

            Log::channel('business')->info('服药记录更新', [
                'user_id'    => $userId,
                'remind_id'  => $reminder->id,
                'status'     => $status,
            ]);
        });
    }

    public function detail(int $reminderId, int $userId): array
    {
        $reminder = Reminder::with(['medicine:id,name', 'prescription:id,medicine_id'])
            ->where('id', $reminderId)
            ->where('user_id', $userId)
            ->first();

        if (!$reminder) {
            throw new BusinessException('提醒记录不存在');
        }

        $repeatTypeMap = [1 => '每日重复', 2 => '周期重复', 3 => '单次'];
        $methodMap = [1 => 'APP提醒', 2 => '短信提醒', 3 => '电话提醒'];

        return [
            'remind_id'          => $reminder->id,
            'prescription_id'    => $reminder->prescription_id,
            'medicine_id'        => $reminder->medicine_id,
            'medicine_name'      => $reminder->medicine->name ?? '',
            'remind_time'        => $reminder->remind_time,
            'dosage'             => $reminder->dosage,
            'repeat_type'        => $reminder->repeat_type,
            'repeat_type_text'   => $repeatTypeMap[$reminder->repeat_type] ?? '',
            'repeat_days'        => $reminder->repeat_days ?? '',
            'remind_method'      => $reminder->remind_method,
            'remind_method_text' => $methodMap[$reminder->remind_method] ?? '',
            'is_active'          => $reminder->is_active,
            'create_time'        => $reminder->create_time?->format('Y-m-d H:i:s'),
        ];
    }

    public function update(int $reminderId, array $data, int $userId): void
    {
        $reminder = Reminder::where('id', $reminderId)
            ->where('user_id', $userId)
            ->first();

        if (!$reminder) {
            throw new BusinessException('提醒记录不存在');
        }

        $reminder->update($data);

        Log::channel('business')->info('服药提醒修改成功', [
            'user_id'   => $userId,
            'remind_id' => $reminderId,
        ]);
    }

    public function delete(int $reminderId, int $userId): void
    {
        $reminder = Reminder::where('id', $reminderId)
            ->where('user_id', $userId)
            ->first();

        if (!$reminder) {
            throw new BusinessException('提醒记录不存在');
        }

        DB::transaction(function () use ($reminder) {
            MedicationRecord::where('reminder_id', $reminder->id)->delete();
            $reminder->delete();
        });

        Log::channel('business')->info('服药提醒删除成功', [
            'user_id'   => $userId,
            'remind_id' => $reminderId,
        ]);
    }

    public function batchStatus(array $ids, int $isActive, int $userId): void
    {
        Reminder::whereIn('id', $ids)
            ->where('user_id', $userId)
            ->update(['is_active' => $isActive]);

        Log::channel('business')->info('批量开关提醒', [
            'user_id'   => $userId,
            'ids'       => $ids,
            'is_active' => $isActive,
        ]);
    }

    public function allList(int $userId, int $page, int $size): LengthAwarePaginator
    {
        return Reminder::with('medicine:id,name')
            ->where('user_id', $userId)
            ->select([
                'id', 'medicine_id', 'remind_time', 'dosage',
                'is_active', 'create_time',
            ])
            ->orderBy('create_time', 'desc')
            ->paginate($size, ['*'], 'page', $page)
            ->through(function ($reminder) {
                return [
                    'remind_id'     => $reminder->id,
                    'medicine_name' => $reminder->medicine->name ?? '',
                    'remind_time'   => $reminder->remind_time,
                    'dosage'        => $reminder->dosage,
                    'is_active'     => $reminder->is_active,
                    'create_time'   => $reminder->create_time?->format('Y-m-d H:i:s'),
                ];
            });
    }
}
