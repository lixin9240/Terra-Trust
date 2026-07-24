<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('健康记录主键ID');
            $table->unsignedBigInteger('user_id')->comment('所属用户ID');
            $table->string('blood_pressure_high', 10)->nullable()->comment('收缩压');
            $table->string('blood_pressure_low', 10)->nullable()->comment('舒张压');
            $table->string('blood_sugar', 10)->nullable()->comment('血糖值');
            $table->string('weight', 10)->nullable()->comment('体重');
            $table->dateTime('create_time')->useCurrent()->comment('创建时间');

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
