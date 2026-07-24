<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drug_conflicts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('药物冲突主键ID');
            $table->unsignedBigInteger('medicine_a_id')->comment('冲突药品A知识库ID');
            $table->unsignedBigInteger('medicine_b_id')->comment('冲突药品B知识库ID');
            $table->tinyInteger('conflict_level')->default(0)->comment('冲突等级 0低/1中/2高风险');
            $table->text('conflict_desc')->nullable()->comment('冲突危害描述');
            $table->text('suggest')->nullable()->comment('规避建议');
            $table->dateTime('create_time')->useCurrent()->comment('创建时间');
            $table->dateTime('update_time')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drug_conflicts');
    }
};
