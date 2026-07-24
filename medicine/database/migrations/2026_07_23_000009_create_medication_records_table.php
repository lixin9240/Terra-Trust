<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_records', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('服药记录主键ID');
            $table->unsignedBigInteger('user_id')->comment('所属用户ID');
            $table->unsignedBigInteger('reminder_id')->nullable()->comment('关联提醒ID');
            $table->unsignedBigInteger('prescription_id')->nullable()->comment('关联医嘱ID');
            $table->unsignedBigInteger('medicine_id')->comment('关联药品ID');
            $table->dateTime('plan_time')->comment('计划服药时间');
            $table->dateTime('actual_time')->nullable()->comment('实际服药时间');
            $table->string('dosage_taken', 50)->nullable()->comment('实际服用剂量');
            $table->tinyInteger('status')->default(1)->comment('服药状态 1已服/2漏服/3跳过');
            $table->text('note')->nullable()->comment('备注');
            $table->dateTime('create_time')->useCurrent()->comment('创建时间');

            $table->index('user_id');
            $table->index('reminder_id');
            $table->index('plan_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_records');
    }
};
