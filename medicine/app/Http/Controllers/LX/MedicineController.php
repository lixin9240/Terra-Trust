<?php

namespace App\Http\Controllers\LX;

use App\Http\Requests\LX\StoreMedicineRequest;
use App\Http\Requests\LX\UpdateMedicineRequest;
use App\Http\Requests\LX\BatchDeleteMedicineRequest;
use App\Services\LX\MedicineService;
use App\Support\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function __construct(
        private readonly MedicineService $medicineService,
    ) {}

    /**
     * 3.1 添加药物
     */
    public function store(StoreMedicineRequest $request): JsonResponse
    {
        $medicine = $this->medicineService->create($request->validated(), auth('api')->id());

        return Result::created('添加成功', ['medicine_id' => $medicine->id]);
    }

    /**
     * 3.2 药物列表
     */
    public function list(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $size = (int) $request->input('size', 10);

        $paginator = $this->medicineService->list(auth('api')->id(), $page, $size);

        return Result::success('成功', [
            'total' => $paginator->total(),
            'list'  => $paginator->items(),
        ]);
    }

    /**
     * 3.3 修改药物
     */
    public function update(int $id, UpdateMedicineRequest $request): JsonResponse
    {
        $this->medicineService->update($id, $request->validated(), auth('api')->id());

        return Result::success('修改成功');
    }

    /**
     * 3.4 删除药物
     */
    public function delete(int $id): JsonResponse
    {
        $this->medicineService->delete($id, auth('api')->id());

        return Result::success('删除成功');
    }

    /**
     * 3.5 药物详情
     */
    public function detail(int $id): JsonResponse
    {
        $data = $this->medicineService->detail($id, auth('api')->id());

        return Result::success('成功', $data);
    }

    /**
     * 3.6 库存预警药品
     */
    public function warnStock(): JsonResponse
    {
        $data = $this->medicineService->warnStock(auth('api')->id());

        return Result::success('成功', $data);
    }

    /**
     * 3.7 过期药品列表
     */
    public function expired(): JsonResponse
    {
        $data = $this->medicineService->expired(auth('api')->id());

        return Result::success('成功', $data);
    }

    /**
     * 3.8 批量删除药物
     */
    public function batchDelete(BatchDeleteMedicineRequest $request): JsonResponse
    {
        $this->medicineService->batchDelete($request->validated('ids'), auth('api')->id());

        return Result::success('批量删除成功');
    }
}
