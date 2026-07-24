<?php

namespace App\Http\Controllers\LX;

use App\Http\Requests\LX\UpdateUserInfoRequest;
use App\Http\Requests\LX\CancelAccountRequest;
use App\Services\LX\UserService;
use App\Support\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * 2.2 修改用户信息
     */
    public function updateInfo(UpdateUserInfoRequest $request): JsonResponse
    {
        $this->userService->updateInfo(auth('api')->user(), $request->validated());

        return Result::success('信息修改成功');
    }

    /**
     * 2.3 上传头像
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|file|image|max:5120',
        ], [
            'avatar.required' => '请选择头像文件',
            'avatar.image'    => '头像必须是图片格式',
            'avatar.max'      => '头像大小不能超过5MB',
        ]);

        $data = $this->userService->uploadAvatar(auth('api')->user(), $request->file('avatar'));

        return Result::success('上传成功', $data);
    }

    /**
     * 2.4 账号注销
     */
    public function cancel(CancelAccountRequest $request): JsonResponse
    {
        $this->userService->cancelAccount(auth('api')->user(), $request->validated('password'));

        return Result::success('账号注销成功');
    }
}
