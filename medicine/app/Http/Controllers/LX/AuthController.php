<?php

namespace App\Http\Controllers\LX;

use App\Http\Requests\LX\RegisterRequest;
use App\Http\Requests\LX\LoginRequest;
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
}
