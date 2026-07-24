<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LX\AuthController;
use App\Http\Controllers\WJC\ReminderController;
use App\Http\Controllers\WJC\RecordController;
use App\Http\Controllers\WJC\RelativeController;
use App\Http\Controllers\WJC\NoticeController;
use App\Http\Controllers\WJC\DrugLibraryController;
use App\Http\Controllers\WJC\HealthController;
use App\Http\Controllers\WJC\CommonController;

/*
|--------------------------------------------------------------------------
| API Routes — PillPal v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ========== 1. 用户认证（无需登录）==========
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login',    [AuthController::class, 'login']);

    // ========== 公开接口 ==========
    Route::get('drugLib/search',      [DrugLibraryController::class, 'search']);
    Route::get('drugLib/{libId}',     [DrugLibraryController::class, 'detail']);
    Route::post('relatives/login',    [RelativeController::class, 'login']);
    Route::get('common/config',       [CommonController::class, 'config']);

    // ========== 亲属认证 ==========
    Route::middleware('auth:relative_api')->group(function () {
        Route::get('relatives/user/{userId}/todayRemind', [RelativeController::class, 'userTodayRemind']);
    });

    // ========== 需要登录 ==========
    Route::middleware('auth:api')->group(function () {

        // --- 1. 认证 ---
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('user/info',    [AuthController::class, 'info']);

        // --- 5. 服药提醒 ---
        Route::post('reminders',              [ReminderController::class, 'store']);
        Route::get('reminders/today',         [ReminderController::class, 'today']);
        Route::get('reminders/all',           [ReminderController::class, 'allList']);
        Route::put('reminders/batchStatus',   [ReminderController::class, 'batchStatus']);
        Route::post('reminders/{id}/take',    [ReminderController::class, 'take']);
        Route::get('reminders/{id}',          [ReminderController::class, 'detail']);
        Route::put('reminders/{id}',          [ReminderController::class, 'update']);
        Route::delete('reminders/{id}',       [ReminderController::class, 'delete']);

        // --- 6. 服药记录 ---
        Route::get('records',           [RecordController::class, 'byDate']);
        Route::get('records/monthStat', [RecordController::class, 'monthStat']);
        Route::get('records/miss',      [RecordController::class, 'missList']);
        Route::get('records/export',    [RecordController::class, 'export']);
        Route::get('records/{id}',      [RecordController::class, 'detail']);

        // --- 7. 亲属管理 ---
        Route::post('relatives/bind',                    [RelativeController::class, 'bind']);
        Route::get('relatives',                          [RelativeController::class, 'list']);
        Route::delete('relatives/{bindId}',              [RelativeController::class, 'unbind']);
        Route::put('relatives/{bindId}/permission',      [RelativeController::class, 'updatePermission']);

        // --- 8. 消息通知 ---
        Route::get('notice/list',     [NoticeController::class, 'list']);
        Route::put('notice/{id}/read',[NoticeController::class, 'read']);
        Route::put('notice/readAll',  [NoticeController::class, 'readAll']);
        Route::delete('notice/{id}',  [NoticeController::class, 'delete']);

        // --- 10. 健康档案 ---
        Route::post('health',       [HealthController::class, 'store']);
        Route::get('health',        [HealthController::class, 'list']);
        Route::delete('health/{id}',[HealthController::class, 'delete']);

        // --- 11. 通用 ---
        Route::post('common/upload', [CommonController::class, 'upload']);
    });
});
