<?php

namespace App\Http\Controllers\WJC;

use App\Services\WJC\RecordService;
use App\Support\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    public function __construct(
        private readonly RecordService $recordService,
    ) {}

    public function byDate(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $data = $this->recordService->byDate(auth('api')->id(), $date);

        return Result::success('成功', $data);
    }

    public function detail(int $id): JsonResponse
    {
        $data = $this->recordService->detail($id, auth('api')->id());

        return Result::success('成功', $data);
    }

    public function monthStat(Request $request): JsonResponse
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $data = $this->recordService->monthStat(auth('api')->id(), $year, $month);

        return Result::success('成功', $data);
    }

    public function missList(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $size = (int) $request->input('size', 10);

        $paginator = $this->recordService->missList(auth('api')->id(), $page, $size);

        return Result::success('成功', [
            'total' => $paginator->total(),
            'list'  => $paginator->items(),
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $startDate = $request->input('startDate');
        $endDate   = $request->input('endDate');

        if (!$startDate || !$endDate) {
            return Result::error(\App\Enums\ResponseCode::PARAM_ERROR, '开始日期和结束日期必填');
        }

        $records = \App\Models\WJC\MedicationRecord::with('medicine:id,name')
            ->where('user_id', auth('api')->id())
            ->whereBetween('plan_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('plan_time', 'asc')
            ->get()
            ->map(function ($r) {
                $statusMap = [1 => '已服用', 2 => '漏服', 3 => '跳过'];
                return [
                    '药品名称' => $r->medicine->name ?? '',
                    '计划时间' => $r->plan_time?->format('Y-m-d H:i:s'),
                    '实际时间' => $r->actual_time?->format('Y-m-d H:i:s'),
                    '剂量'     => $r->dosage_taken,
                    '状态'     => $statusMap[$r->status] ?? '',
                    '备注'     => $r->note,
                ];
            });

        return Result::success('成功', [
            'total'   => $records->count(),
            'records' => $records,
        ]);
    }
}
