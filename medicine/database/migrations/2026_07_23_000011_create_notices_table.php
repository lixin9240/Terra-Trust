<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('消息主键ID');
            $table->unsignedBigInteger('user_id')->comment('所属用户ID');
            $table->string('title', 200)->comment('消息标题');
            $table->text('content')->nullable()->comment('消息内容');
            $table->string('type', 50)->comment('消息类型');
            $table->tinyInteger('is_read')->default(0)->comment('是否已读 0未读/1已读');
            $table->dateTime('create_time')->useCurrent()->comment('创建时间');

            $table->index('user_id');
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
