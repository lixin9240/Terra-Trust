<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('医嘱主键ID');
            $table->unsignedBigInteger('user_id')->comment('所属用户ID');
            $table->unsignedBigInteger('medicine_id')->comment('关联药品ID');
            $table->string('doctor_name', 50)->nullable()->comment('开具医生姓名');
            $table->string('hospital', 100)->nullable()->comment('就诊医院');
            $table->text('diagnosis')->nullable()->comment('诊断病情');
            $table->string('dosage', 50)->comment('单次服用剂量');
            $table->string('frequency', 50)->comment('服用频率');
            $table->date('start_date')->comment('医嘱开始日期');
            $table->date('end_date')->nullable()->comment('医嘱结束日期');
            $table->text('conflict_warning')->nullable()->comment('药物冲突预警信息');
            $table->tinyInteger('status')->default(1)->comment('医嘱状态 0停用/1启用');
            $table->text('note')->nullable()->comment('医嘱备注');
            $table->dateTime('create_time')->useCurrent()->comment('创建时间');
            $table->dateTime('update_time')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');

            $table->index('user_id');
            $table->index('medicine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
