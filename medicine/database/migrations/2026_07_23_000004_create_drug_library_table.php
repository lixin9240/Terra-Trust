<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drug_library', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('药品知识库主键ID');
            $table->string('drug_name', 100)->comment('药品通用名称');
            $table->string('specification', 60)->nullable()->comment('药品规格');
            $table->string('manufacturer', 100)->nullable()->comment('生产厂家');
            $table->text('usage')->nullable()->comment('服用方式');
            $table->text('taboo')->nullable()->comment('禁忌人群/场景');
            $table->text('side_effect')->nullable()->comment('副作用');
            $table->text('effect')->nullable()->comment('药品功效');
            $table->text('match_conflict')->nullable()->comment('配伍冲突说明');
            $table->text('save_note')->nullable()->comment('储存注意事项');
            $table->dateTime('create_time')->useCurrent()->comment('创建时间');
            $table->dateTime('update_time')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');

            $table->index('drug_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drug_library');
    }
};
