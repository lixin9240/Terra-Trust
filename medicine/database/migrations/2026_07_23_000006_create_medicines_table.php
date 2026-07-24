<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('用户药品主键ID');
            $table->unsignedBigInteger('user_id')->comment('所属用户ID');
            $table->string('name', 100)->comment('药品名称');
            $table->string('specification', 60)->nullable()->comment('药品规格');
            $table->string('manufacturer', 100)->nullable()->comment('生产厂家');
            $table->date('expire_date')->nullable()->comment('有效期截止日期');
            $table->integer('stock')->default(0)->comment('药品库存数量');
            $table->string('unit', 20)->nullable()->comment('数量单位');
            $table->text('usage')->nullable()->comment('服用方式');
            $table->text('note')->nullable()->comment('备注信息');
            $table->dateTime('create_time')->useCurrent()->comment('创建时间');
            $table->dateTime('update_time')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
