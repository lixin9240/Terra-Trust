<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relatives', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('亲属账号主键ID');
            $table->string('username', 50)->comment('亲属登录账号');
            $table->string('password', 255)->comment('加密密码');
            $table->string('phone', 20)->nullable()->comment('亲属手机号');
            $table->string('real_name', 50)->nullable()->comment('亲属真实姓名');
            $table->string('avatar', 255)->nullable()->comment('头像链接');
            $table->tinyInteger('status')->default(1)->comment('账号状态 0禁用/1正常');
            $table->dateTime('last_login_time')->nullable()->comment('最后登录时间');
            $table->dateTime('create_time')->useCurrent()->comment('创建时间');
            $table->dateTime('update_time')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');

            $table->unique('username');
            $table->unique('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relatives');
    }
};
