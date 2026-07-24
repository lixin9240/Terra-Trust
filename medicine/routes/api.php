<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LX\AuthController;
use App\Http\Controllers\LX\UserController;
use App\Http\Controllers\LX\MedicineController;
use App\Http\Controllers\LX\PrescriptionController;
use App\Http\Controllers\WJC\ReminderController;
use App\Http\Controllers\WJC\RecordController;
use App\Http\Controllers\WJC\RelativeController;
use App\Http\Controllers\WJC\NoticeController;
use App\Http\Controllers\WJC\DrugLibraryController;
use App\Http\Controllers\WJC\HealthController;
use App\Http\Controllers\WJC\CommonController;


Route::prefix('v1')->group(function () {

    // ========== 1. 用户认证（无需登录）==========
    Route::post('auth/register', [AuthController::class, 'register']);// 注册
    Route::post('auth/login',    [AuthController::class, 'login']);// 登录
    Route::post('auth/sendCode', [AuthController::class, 'sendCode']);// 发送验证码
    Route::post('auth/resetPwd', [AuthController::class, 'resetPwd']);// 重置密码

    // ========== 公开接口 ==========
    Route::get('drugLib/search',      [DrugLibraryController::class, 'search']);// 搜索药品
    Route::get('drugLib/{libId}',     [DrugLibraryController::class, 'detail']);// 药品详情
    Route::post('relatives/login',    [RelativeController::class, 'login']);// 亲属登录
    Route::get('common/config',       [CommonController::class, 'config']);// 公共配置

    // ========== 亲属认证 ==========
    Route::middleware('auth:relative_api')->group(function () {
        Route::get('relatives/user/{userId}/todayRemind', [RelativeController::class, 'userTodayRemind']);// 今日服药提醒
    });

    // ========== 需要登录 ==========
    Route::middleware('auth:api')->group(function () {

        // --- 1. 认证 ---
        Route::post('auth/logout',   [AuthController::class, 'logout']);// 退出登录
        Route::put('auth/password',  [AuthController::class, 'changePassword']);// 修改密码
        Route::post('auth/refresh',  [AuthController::class, 'refreshToken']);// 刷新Token
        Route::get('user/info',      [AuthController::class, 'info']);// 用户信息

        // --- 2. 用户信息 ---
        Route::put('user/info',              [UserController::class, 'updateInfo']);// 修改用户信息
        Route::post('user/uploadAvatar',     [UserController::class, 'uploadAvatar']);// 上传头像
        Route::post('user/cancel',           [UserController::class, 'cancel']);// 账号注销

        // --- 3. 药物管理 ---
        Route::post('medicines',             [MedicineController::class, 'store']);// 添加药物
        Route::get('medicines',              [MedicineController::class, 'list']);// 药物列表
        Route::get('medicines/warnStock',    [MedicineController::class, 'warnStock']);// 库存预警
        Route::get('medicines/expired',      [MedicineController::class, 'expired']);// 过期药品
        Route::delete('medicines/batch',     [MedicineController::class, 'batchDelete']);// 批量删除
        Route::put('medicines/{id}',         [MedicineController::class, 'update']);// 修改药物
        Route::delete('medicines/{id}',      [MedicineController::class, 'delete']);// 删除药物
        Route::get('medicines/{id}',         [MedicineController::class, 'detail']);// 药物详情

        // --- 4. 医嘱管理 ---
        Route::post('prescriptions',                      [PrescriptionController::class, 'store']);// 添加医嘱
        Route::get('prescriptions',                       [PrescriptionController::class, 'list']);// 医嘱列表
        Route::post('prescriptions/checkConflict',        [PrescriptionController::class, 'checkConflict']);// 药物冲突检测
        Route::get('prescriptions/{id}',                  [PrescriptionController::class, 'detail']);// 医嘱详情
        Route::put('prescriptions/{id}',                  [PrescriptionController::class, 'update']);// 修改医嘱
        Route::delete('prescriptions/{id}',               [PrescriptionController::class, 'delete']);// 删除医嘱
        Route::put('prescriptions/{id}/status',           [PrescriptionController::class, 'updateStatus']);// 启用/停用医嘱

        // --- 5. 服药提醒 ---
        Route::post('reminders',              [ReminderController::class, 'store']);// 创建提醒
        Route::get('reminders/today',         [ReminderController::class, 'today']);// 今日提醒
        Route::get('reminders/all',           [ReminderController::class, 'allList']);// 所有提醒
        Route::put('reminders/batchStatus',   [ReminderController::class, 'batchStatus']);// 批量更新状态
        Route::post('reminders/{id}/take',    [ReminderController::class, 'take']);// 取药
        Route::get('reminders/{id}',          [ReminderController::class, 'detail']);// 详情
        Route::put('reminders/{id}',          [ReminderController::class, 'update']);// 更新
        Route::delete('reminders/{id}',       [ReminderController::class, 'delete']);// 删除

        // --- 6. 服药记录 ---
        Route::get('records',           [RecordController::class, 'byDate']);// 日期查询
        Route::get('records/monthStat', [RecordController::class, 'monthStat']);// 月统计
        Route::get('records/miss',      [RecordController::class, 'missList']);// 缺药记录
        Route::get('records/export',    [RecordController::class, 'export']);// 导出
        Route::get('records/{id}',      [RecordController::class, 'detail']);// 详情

        // --- 7. 亲属管理 ---
        Route::post('relatives/bind',                    [RelativeController::class, 'bind']);// 绑定亲属
        Route::get('relatives',                          [RelativeController::class, 'list']);// 亲属列表
        Route::delete('relatives/{bindId}',              [RelativeController::class, 'unbind']);// 解绑亲属
        Route::put('relatives/{bindId}/permission',      [RelativeController::class, 'updatePermission']);// 更新权限

        // --- 8. 消息通知 ---
        Route::get('notice/list',     [NoticeController::class, 'list']);// 消息列表
        Route::put('notice/{id}/read',[NoticeController::class, 'read']);// 标记已读
        Route::put('notice/readAll',  [NoticeController::class, 'readAll']);// 标记所有已读
        Route::delete('notice/{id}',  [NoticeController::class, 'delete']);// 删除

        // --- 10. 健康档案 ---
        Route::post('health',       [HealthController::class, 'store']);// 创建健康档案
        Route::get('health',        [HealthController::class, 'list']);// 健康档案列表
        Route::delete('health/{id}',[HealthController::class, 'delete']);// 删除健康档案

        // --- 11. 通用 ---
        Route::post('common/upload', [CommonController::class, 'upload']);// 上传文件
    });
});
