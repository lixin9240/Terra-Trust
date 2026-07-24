<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('用户主键ID');
            $table->string('username', 50)->comment('登录用户名');
            $table->string('password', 255)->comment('加密密码');
            $table->string('phone', 20)->nullable()->comment('手机号');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('real_name', 50)->nullable()->comment('真实姓名');
            $table->tinyInteger('gender')->default(0)->comment('性别 0未知/1男/2女');
            $table->integer('age')->nullable()->comment('年龄');
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
        Schema::dropIfExists('users');
    }
};
