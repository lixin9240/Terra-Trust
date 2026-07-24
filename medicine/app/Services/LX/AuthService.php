<?php

namespace App\Services\LX;

use App\Exceptions\BusinessException;
use App\Models\User;
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

        Log::channel('business')->info('用户注册成功', [
            'user_id'  => $user->id,
            'username' => $user->username,
        ]);

        return [
            'user_id'  => $user->id,
            'username' => $user->username,
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
}
