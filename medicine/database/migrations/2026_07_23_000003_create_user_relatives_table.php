<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_relatives', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('绑定关系主键ID');
            $table->unsignedBigInteger('user_id')->comment('服药用户ID');
            $table->unsignedBigInteger('relative_id')->comment('亲属账号ID');
            $table->string('relation_text', 20)->comment('亲属关系');
            $table->tinyInteger('permission')->default(1)->comment('权限 1仅查看/2可管理');
            $table->tinyInteger('bind_status')->default(1)->comment('绑定状态 0解绑/1正常绑定');
            $table->dateTime('bind_time')->useCurrent()->comment('绑定时间');
            $table->dateTime('create_time')->useCurrent()->comment('创建时间');
            $table->dateTime('update_time')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');

            $table->index('user_id');
            $table->index('relative_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_relatives');
    }
};
