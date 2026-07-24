<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('提醒主键ID');
            $table->unsignedBigInteger('user_id')->comment('所属用户ID');
            $table->unsignedBigInteger('prescription_id')->nullable()->comment('关联医嘱ID');
            $table->unsignedBigInteger('medicine_id')->comment('关联药品ID');
            $table->time('remind_time')->comment('每日提醒时间');
            $table->string('dosage', 50)->comment('本次服用剂量');
            $table->tinyInteger('repeat_type')->default(1)->comment('重复类型 1每日/2周期/3单次');
            $table->string('repeat_days', 20)->nullable()->comment('周期重复星期');
            $table->tinyInteger('remind_method')->default(1)->comment('提醒方式 1APP/2短信/3电话');
            $table->tinyInteger('is_active')->default(1)->comment('提醒开关状态 0关闭/1开启');
            $table->dateTime('create_time')->useCurrent()->comment('创建时间');
            $table->dateTime('update_time')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');

            $table->index('user_id');
            $table->index('medicine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
