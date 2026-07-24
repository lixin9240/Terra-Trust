<?php

namespace App\Http\Controllers\LX;

use App\Http\Requests\LX\StorePrescriptionRequest;
use App\Http\Requests\LX\UpdatePrescriptionRequest;
use App\Http\Requests\LX\PrescriptionStatusRequest;
use App\Http\Requests\LX\CheckConflictRequest;
use App\Services\LX\PrescriptionService;
use App\Support\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function __construct(
        private readonly PrescriptionService $prescriptionService,
    ) {}

    /**
     * 4.1 添加医嘱
     */
    public function store(StorePrescriptionRequest $request): JsonResponse
    {
        $prescription = $this->prescriptionService->create($request->validated(), auth('api')->id());

        return Result::created('医嘱创建成功', ['prescription_id' => $prescription->id]);
    }

    /**
     * 4.2 医嘱列表
     */
    public function list(Request $request): JsonResponse
    {
        $page   = (int) $request->input('page', 1);
        $size   = (int) $request->input('size', 10);
        $status = $request->has('status') ? (int) $request->input('status') : null;

        $paginator = $this->prescriptionService->list(auth('api')->id(), $page, $size, $status);

        return Result::success('成功', [
            'total' => $paginator->total(),
            'list'  => $paginator->items(),
        ]);
    }

    /**
     * 4.3 医嘱详情
     */
    public function detail(int $id): JsonResponse
    {
        $data = $this->prescriptionService->detail($id, auth('api')->id());

        return Result::success('成功', $data);
    }

    /**
     * 4.4 修改医嘱
     */
    public function update(int $id, UpdatePrescriptionRequest $request): JsonResponse
    {
        $this->prescriptionService->update($id, $request->validated(), auth('api')->id());

        return Result::success('医嘱修改成功');
    }

    /**
     * 4.5 删除医嘱
     */
    public function delete(int $id): JsonResponse
    {
        $this->prescriptionService->delete($id, auth('api')->id());

        return Result::success('医嘱删除成功');
    }

    /**
     * 4.6 启用/停用医嘱
     */
    public function updateStatus(int $id, PrescriptionStatusRequest $request): JsonResponse
    {
        $this->prescriptionService->updateStatus(
            $id,
            $request->validated('status'),
            auth('api')->id()
        );

        return Result::success('状态修改成功');
    }

    /**
     * 4.7 药物冲突检测
     */
    public function checkConflict(CheckConflictRequest $request): JsonResponse
    {
        $data = $this->prescriptionService->checkConflict(
            $request->validated('medicine_ids'),
            auth('api')->id()
        );

        return Result::success('检测完成', $data);
    }
}
