<?php

namespace App\Http\Controllers\WJC;

use App\Services\WJC\NoticeService;
use App\Support\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function __construct(
        private readonly NoticeService $noticeService,
    ) {}

    public function list(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $size = (int) $request->input('size', 10);

        $paginator = $this->noticeService->list(auth('api')->id(), $page, $size);

        return Result::success('成功', [
            'total' => $paginator->total(),
            'list'  => $paginator->items(),
        ]);
    }

    public function read(int $id): JsonResponse
    {
        $this->noticeService->markRead($id, auth('api')->id());

        return Result::success('操作成功');
    }

    public function readAll(): JsonResponse
    {
        $this->noticeService->markAllRead(auth('api')->id());

        return Result::success('全部已读成功');
    }

    public function delete(int $id): JsonResponse
    {
        $this->noticeService->delete($id, auth('api')->id());

        return Result::success('消息删除成功');
    }
}
