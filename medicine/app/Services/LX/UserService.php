<?php

namespace App\Services\LX;

use App\Exceptions\BusinessException;
use App\Models\User;
use App\Models\WJC\Medicine;
use App\Models\WJC\Prescription;
use App\Models\WJC\Reminder;
use App\Models\WJC\MedicationRecord;
use App\Models\WJC\HealthRecord;
use App\Models\WJC\Notice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    /**
     * 2.2 修改用户信息
     */
    public function updateInfo(User $user, array $data): void
    {
        $user->update(array_filter([
            'real_name' => $data['real_name'] ?? null,
            'gender'    => $data['gender'] ?? null,
            'age'       => $data['age'] ?? null,
            'avatar'    => $data['avatar'] ?? null,
        ], fn($v) => $v !== null));

        Log::channel('business')->info('用户修改个人信息', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * 2.3 上传头像
     */
    public function uploadAvatar(User $user, $file): array
    {
        $path = $file->store('avatars', 'public');

        $url = asset('storage/' . $path);

        $user->update(['avatar' => $url]);

        Log::channel('business')->info('用户上传头像', [
            'user_id'    => $user->id,
            'avatar_url' => $url,
        ]);

        return ['avatar_url' => $url];
    }

    /**
     * 2.4 账号注销
     */
    public function cancelAccount(User $user, string $password): void
    {
        if (!Hash::check($password, $user->password)) {
            throw new BusinessException('密码错误');
        }

        $userId = $user->id;

        DB::transaction(function () use ($userId) {
            // 删除服药记录
            MedicationRecord::where('user_id', $userId)->delete();
            // 删除提醒
            Reminder::where('user_id', $userId)->delete();
            // 删除医嘱
            Prescription::where('user_id', $userId)->delete();
            // 删除药物
            Medicine::where('user_id', $userId)->delete();
            // 删除健康记录
            HealthRecord::where('user_id', $userId)->delete();
            // 删除通知
            Notice::where('user_id', $userId)->delete();
            // 删除用户
            User::where('id', $userId)->delete();
        });

        Log::channel('business')->info('用户注销账号', [
            'user_id' => $userId,
        ]);
    }
}
