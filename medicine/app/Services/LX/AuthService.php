<?php

namespace App\Services\LX;

use App\Exceptions\BusinessException;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'phone'    => $data['phone'],
            'real_name'=> $data['real_name'] ?? null,
            'gender'   => $data['gender'] ?? 0,
            'age'      => $data['age'] ?? null,
            'status'   => 1,
        ]);

        // 注册后自动登录，生成 token
        $token = auth('api')->login($user);

        Log::channel('business')->info('用户注册成功', [
            'user_id'  => $user->id,
            'username' => $user->username,
        ]);

        return [
            'user_id'  => $user->id,
            'username' => $user->username,
            'token'    => $token,
        ];
    }

    public function login(string $username, string $password): array
    {
        $token = auth('api')->attempt([
            'username' => $username,
            'password' => $password,
        ]);

        if (!$token) {
            // 尝试用手机号
            $token = auth('api')->attempt([
                'phone'    => $username,
                'password' => $password,
            ]);
        }

        if (!$token) {
            throw new BusinessException('用户名或密码错误');
        }

        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        if ($user->status !== 1) {
            auth('api')->logout();
            throw new BusinessException('账号已被禁用');
        }

        $user->update(['last_login_time' => now()]);

        Log::channel('business')->info('用户登录成功', [
            'user_id' => $user->id,
        ]);

        return [
            'user_id'   => $user->id,
            'username'  => $user->username,
            'token'     => $token,
            'expire_at' => now()->addMinutes(config('jwt.ttl'))->format('Y-m-d\TH:i:s'),
        ];
    }

    public function logout(): void
    {
        auth('api')->logout();
    }

    public function userInfo(User $user): array
    {
        return [
            'user_id'   => $user->id,
            'username'  => $user->username,
            'real_name' => $user->real_name,
            'phone'     => $user->phone,
            'gender'    => $user->gender,
            'age'       => $user->age,
            'avatar'    => $user->avatar,
            'status'    => $user->status,
        ];
    }

    /**
     * 修改登录密码
     */
    public function changePassword(User $user, string $oldPassword, string $newPassword): void
    {
        if (!Hash::check($oldPassword, $user->password)) {
            throw new BusinessException('原密码错误');
        }

        $user->update(['password' => Hash::make($newPassword)]);

        // 修改密码后强制退出，需要重新登录
        auth('api')->logout();

        Log::channel('business')->info('用户修改密码成功', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * 刷新Token
     */
    public function refreshToken(): array
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $token = $guard->refresh();

        return [
            'token'     => $token,
            'expire_at' => now()->addMinutes(config('jwt.ttl'))->format('Y-m-d\TH:i:s'),
        ];
    }

    /**
     * 发送短信验证码
     */
    public function sendCode(string $phone): void
    {
        // 生成6位随机验证码
        $code = sprintf('%06d', random_int(0, 999999));

        // 存储到缓存，5分钟有效
        $cacheKey = 'sms_code:' . $phone;
        Cache::put($cacheKey, $code, now()->addMinutes(5));

        // TODO: 接入真实短信服务商，目前仅记录日志
        Log::channel('business')->info('短信验证码已生成', [
            'phone' => $phone,
            'code'  => $code,
        ]);
    }

    /**
     * 验证码重置密码
     */
    public function resetPassword(string $phone, string $code, string $newPassword): void
    {
        // 校验验证码
        $cacheKey = 'sms_code:' . $phone;
        $cachedCode = Cache::get($cacheKey);

        if (!$cachedCode || $cachedCode !== $code) {
            throw new BusinessException('验证码错误或已过期');
        }

        // 查找用户
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            throw new BusinessException('该手机号未注册');
        }

        // 更新密码
        $user->update(['password' => Hash::make($newPassword)]);

        // 删除已使用的验证码
        Cache::forget($cacheKey);

        Log::channel('business')->info('用户通过验证码重置密码成功', [
            'user_id' => $user->id,
            'phone'   => $phone,
        ]);
    }
}
