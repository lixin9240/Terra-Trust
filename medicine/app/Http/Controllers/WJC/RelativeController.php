<?php

namespace App\Http\Controllers\WJC;

use App\Http\Requests\WJC\RelativeBindRequest;
use App\Http\Requests\WJC\RelativeLoginRequest;
use App\Http\Requests\WJC\RelativePermissionRequest;
use App\Services\WJC\RelativeService;
use App\Support\Result;
use Illuminate\Http\JsonResponse;

class RelativeController extends Controller
{
    public function __construct(
        private readonly RelativeService $relativeService,
    ) {}

    public function bind(RelativeBindRequest $request): JsonResponse
    {
        $this->relativeService->bind($request->validated(), auth('api')->id());

        return Result::success('绑定申请已发送');
    }

    public function list(): JsonResponse
    {
        $data = $this->relativeService->list(auth('api')->id());

        return Result::success('成功', $data);
    }

    public function unbind(int $bindId): JsonResponse
    {
        $this->relativeService->unbind($bindId, auth('api')->id());

        return Result::success('亲属解绑成功');
    }

    public function updatePermission(int $bindId, RelativePermissionRequest $request): JsonResponse
    {
        $this->relativeService->updatePermission(
            $bindId,
            $request->validated('permission'),
            auth('api')->id()
        );

        return Result::success('权限修改成功');
    }

    public function login(RelativeLoginRequest $request): JsonResponse
    {
        $data = $this->relativeService->login(
            $request->validated('username'),
            $request->validated('password')
        );

        return Result::success('登录成功', $data);
    }

    public function userTodayRemind(int $userId): JsonResponse
    {
        $relative = auth('relative_api')->user();

        $data = $this->relativeService->userTodayRemind($userId, $relative->id);

        return Result::success('成功', $data);
    }
}
