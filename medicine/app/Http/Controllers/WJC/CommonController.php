<?php

namespace App\Http\Controllers\WJC;

use App\Support\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommonController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        if (!$request->hasFile('file')) {
            return Result::error(\App\Enums\ResponseCode::PARAM_ERROR, '请选择上传文件');
        }

        $file = $request->file('file');

        if (!$file->isValid()) {
            return Result::error(\App\Enums\ResponseCode::PARAM_ERROR, '文件上传失败');
        }

        $path = $file->store('uploads/' . date('Ymd'), 'oss');

        $url = Storage::disk('oss')->url($path);

        return Result::success('上传成功', ['file_url' => $url]);
    }

    public function config(): JsonResponse
    {
        return Result::success('成功', [
            'version'         => 'V1.2.0',
            'notice'          => '',
            'customer_phone'  => '400-123-4567',
            'max_upload_size' => 10485760,
        ]);
    }
}
