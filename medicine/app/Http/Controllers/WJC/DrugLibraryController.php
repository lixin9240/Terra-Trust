<?php

namespace App\Http\Controllers\WJC;

use App\Services\WJC\DrugLibraryService;
use App\Support\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DrugLibraryController extends Controller
{
    public function __construct(
        private readonly DrugLibraryService $drugLibraryService,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $keyword = $request->input('keyword', '');
        if (empty($keyword)) {
            return Result::error(\App\Enums\ResponseCode::PARAM_ERROR, '搜索关键词不能为空');
        }

        $page = (int) $request->input('page', 1);
        $size = (int) $request->input('size', 10);

        $paginator = $this->drugLibraryService->search($keyword, $page, $size);

        return Result::success('成功', [
            'total' => $paginator->total(),
            'list'  => $paginator->items(),
        ]);
    }

    public function detail(int $libId): JsonResponse
    {
        $data = $this->drugLibraryService->detail($libId);

        return Result::success('成功', $data);
    }
}
