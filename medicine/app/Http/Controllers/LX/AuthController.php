<?php

namespace App\Http\Controllers\LX;

use App\Http\Requests\LX\ChangePasswordRequest;
use App\Http\Requests\LX\LoginRequest;
use App\Http\Requests\LX\RegisterRequest;
use App\Http\Requests\LX\ResetPasswordRequest;
use App\Http\Requests\LX\SendCodeRequest;
use App\Services\LX\AuthService;
use App\Support\Result;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->authService->register($request->validated());

        return Result::success('注册成功', $data);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login(
            $request->validated('username'),
            $request->validated('password')
        );

        return Result::success('登录成功', $data);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return Result::success('退出成功');
    }

    public function info(): JsonResponse
    {
        $data = $this->authService->userInfo(auth('api')->user());

        return Result::success('成功', $data);
    }

    /**
     * 1.4 修改登录密码
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            auth('api')->user(),
            $request->validated('old_password'),
            $request->validated('new_password')
        );

        return Result::success('密码修改成功，请重新登录');
    }

    /**
     * 1.5 刷新Token
     */
    public function refreshToken(): JsonResponse
    {
        $data = $this->authService->refreshToken();

        return Result::success('刷新成功', $data);
    }

    /**
     * 1.6 获取短信验证码
     */
    public function sendCode(SendCodeRequest $request): JsonResponse
    {
        $this->authService->sendCode($request->validated('phone'));

        return Result::success('验证码已发送');
    }

    /**
     * 1.7 验证码重置密码
     */
    public function resetPwd(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword(
            $request->validated('phone'),
            $request->validated('code'),
            $request->validated('new_password')
        );

        return Result::success('密码重置成功，请登录');
    }
}
