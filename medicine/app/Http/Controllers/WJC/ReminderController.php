<?php

namespace App\Http\Controllers\WJC;

use App\Http\Requests\WJC\ReminderStoreRequest;
use App\Http\Requests\WJC\ReminderUpdateRequest;
use App\Http\Requests\WJC\ReminderTakeRequest;
use App\Http\Requests\WJC\ReminderBatchStatusRequest;
use App\Services\WJC\ReminderService;
use App\Support\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function __construct(
        private readonly ReminderService $reminderService,
    ) {}

    public function store(ReminderStoreRequest $request): JsonResponse
    {
        $reminder = $this->reminderService->create($request->validated(), auth('api')->id());

        return Result::success('提醒创建成功', ['remind_id' => $reminder->id]);
    }

    public function today(): JsonResponse
    {
        $data = $this->reminderService->todayReminders(auth('api')->id());

        return Result::success('成功', $data);
    }

    public function take(int $id, ReminderTakeRequest $request): JsonResponse
    {
        $this->reminderService->take(
            $id,
            $request->validated('status'),
            $request->validated('note'),
            auth('api')->id()
        );

        return Result::success('记录保存成功');
    }

    public function detail(int $id): JsonResponse
    {
        $data = $this->reminderService->detail($id, auth('api')->id());

        return Result::success('成功', $data);
    }

    public function update(int $id, ReminderUpdateRequest $request): JsonResponse
    {
        $this->reminderService->update($id, $request->validated(), auth('api')->id());

        return Result::success('提醒修改成功');
    }

    public function delete(int $id): JsonResponse
    {
        $this->reminderService->delete($id, auth('api')->id());

        return Result::success('提醒删除成功');
    }

    public function batchStatus(ReminderBatchStatusRequest $request): JsonResponse
    {
        $this->reminderService->batchStatus(
            $request->validated('ids'),
            $request->validated('is_active'),
            auth('api')->id()
        );

        return Result::success('批量操作成功');
    }

    public function allList(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $size = (int) $request->input('size', 10);

        $paginator = $this->reminderService->allList(auth('api')->id(), $page, $size);

        return Result::success('成功', [
            'total' => $paginator->total(),
            'list'  => $paginator->items(),
        ]);
    }
}
