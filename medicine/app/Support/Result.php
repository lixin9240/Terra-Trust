<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class Result
{
    /**
     * 操作成功响应
     */
    public static function success(string $msg = '成功', mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json(
            array_filter([
                'code' => $code,
                'msg'  => $msg,
                'data' => $data,
            ], fn($v) => $v !== null)
        );
    }

    /**
     * 新增创建成功响应
     */
    public static function created(string $msg = '创建成功', mixed $data = null): JsonResponse
    {
        return static::success($msg, $data, 201);
    }

    /**
     * 操作失败响应
     */
    public static function error(string $msg = '操作失败', int $code = 400, mixed $data = null): JsonResponse
    {
        return response()->json(
            array_filter([
                'code' => $code,
                'msg'  => $msg,
                'data' => $data,
            ], fn($v) => $v !== null)
        );
    }
}
