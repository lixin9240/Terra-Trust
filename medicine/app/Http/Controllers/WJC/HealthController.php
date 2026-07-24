<?php

namespace App\Http\Controllers\WJC;

use App\Http\Requests\WJC\HealthRecordStoreRequest;
use App\Services\WJC\HealthService;
use App\Support\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function __construct(
        private readonly HealthService $healthService,
    ) {}

    public function store(HealthRecordStoreRequest $request): JsonResponse
    {
        $record = $this->healthService->create($request->validated(), auth('api')->id());

        return Result::success('新增成功', ['health_id' => $record->id]);
    }

    public function list(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $size = (int) $request->input('size', 10);

        $paginator = $this->healthService->list(auth('api')->id(), $page, $size);

        return Result::success('成功', [
            'total' => $paginator->total(),
            'list'  => $paginator->items(),
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        $this->healthService->delete($id, auth('api')->id());

        return Result::success('健康记录删除成功');
    }
}
