<?php

namespace App\Services\WJC;

use App\Exceptions\BusinessException;
use App\Models\User;
use App\Models\WJC\Relative;
use App\Models\WJC\UserRelative;
use App\Models\WJC\Reminder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class RelativeService
{
    public function bind(array $data, int $userId): void
    {
        $targetUser = User::where('phone', $data['phone'])->first();
        if (!$targetUser) {
            throw new BusinessException('该手机号未注册');
        }

        if ($targetUser->id === $userId) {
            throw new BusinessException('不能绑定自己');
        }

        $exists = UserRelative::where('user_id', $userId)
            ->where('relative_id', $targetUser->id)
            ->where('bind_status', 1)
            ->exists();

        if ($exists) {
            throw new BusinessException('已绑定该亲属');
        }

        DB::transaction(function () use ($data, $userId, $targetUser) {
            $relative = Relative::firstOrCreate(
                ['phone' => $targetUser->phone],
                [
                    'username' => $targetUser->username . '_r',
                    'password' => $targetUser->password,
                    'real_name'=> $targetUser->real_name,
                    'status'   => 1,
                ]
            );

            UserRelative::create([
                'user_id'       => $userId,
                'relative_id'   => $relative->id,
                'relation_text' => $data['relation'],
                'permission'    => $data['permission'],
                'bind_status'   => 1,
                'bind_time'     => now(),
            ]);

            Log::channel('business')->info('亲属绑定成功', [
                'user_id'     => $userId,
                'relative_id' => $relative->id,
            ]);
        });
    }

    public function list(int $userId): array
    {
        $binds = UserRelative::with('relative')
            ->where('user_id', $userId)
            ->where('bind_status', 1)
            ->get();

        $permMap = [1 => '仅查看', 2 => '可管理'];

        return $binds->map(function ($bind) use ($permMap) {
            return [
                'bind_id'         => $bind->id,
                'phone'           => $bind->relative->phone ?? '',
                'real_name'       => $bind->relative->real_name ?? '',
                'relation'        => $bind->relation_text,
                'permission'      => $bind->permission,
                'permission_text' => $permMap[$bind->permission] ?? '',
                'bind_status'     => $bind->bind_status,
                'bind_time'       => $bind->bind_time?->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    public function unbind(int $bindId, int $userId): void
    {
        $bind = UserRelative::where('id', $bindId)
            ->where('user_id', $userId)
            ->first();

        if (!$bind) {
            throw new BusinessException('绑定关系不存在');
        }

        $bind->update(['bind_status' => 0]);

        Log::channel('business')->info('亲属解绑成功', [
            'user_id' => $userId,
            'bind_id' => $bindId,
        ]);
    }

    public function updatePermission(int $bindId, int $permission, int $userId): void
    {
        $bind = UserRelative::where('id', $bindId)
            ->where('user_id', $userId)
            ->first();

        if (!$bind) {
            throw new BusinessException('绑定关系不存在');
        }

        $bind->update(['permission' => $permission]);

        Log::channel('business')->info('亲属权限修改', [
            'user_id'    => $userId,
            'bind_id'    => $bindId,
            'permission' => $permission,
        ]);
    }

    public function login(string $username, string $password): array
    {
        $token = auth('relative_api')->attempt([
            'username' => $username,
            'password' => $password,
        ]);

        if (!$token) {
            $token = auth('relative_api')->attempt([
                'phone'    => $username,
                'password' => $password,
            ]);
        }

        if (!$token) {
            throw new BusinessException('用户名或密码错误');
        }

        $relative = auth('relative_api')->user();

        if ($relative->status !== 1) {
            auth('relative_api')->logout();
            throw new BusinessException('账号已被禁用');
        }

        $relative->update(['last_login_time' => now()]);

        $bindCount = UserRelative::where('relative_id', $relative->id)
            ->where('bind_status', 1)
            ->count();

        return [
            'token'           => $token,
            'expire_at'       => now()->addMinutes(config('jwt.ttl'))->format('Y-m-d\TH:i:s'),
            'bind_user_count' => $bindCount,
        ];
    }

    public function userTodayRemind(int $targetUserId, int $relativeUserId): array
    {
        $bind = UserRelative::where('user_id', $targetUserId)
            ->where('relative_id', $relativeUserId)
            ->where('bind_status', 1)
            ->first();

        if (!$bind) {
            throw new BusinessException('无权限查看该用户数据');
        }

        $today = now()->format('Y-m-d');

        return Reminder::with('medicine:id,name')
            ->where('user_id', $targetUserId)
            ->where('is_active', 1)
            ->get()
            ->map(function ($reminder) use ($today) {
                $record = \App\Models\WJC\MedicationRecord::where('reminder_id', $reminder->id)
                    ->whereDate('plan_time', $today)
                    ->latest('id')
                    ->first();

                $statusMap = [0 => '未服用', 1 => '已服用', 2 => '漏服', 3 => '跳过'];

                return [
                    'remind_id'     => $reminder->id,
                    'medicine_name' => $reminder->medicine->name ?? '',
                    'remind_time'   => $reminder->remind_time,
                    'dosage'        => $reminder->dosage,
                    'status'        => $record ? $record->status : 0,
                    'status_text'   => $statusMap[$record->status ?? 0],
                ];
            })
            ->toArray();
    }
}
