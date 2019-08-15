<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePowerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //生成员工表
        Schema::create('power', function (Blueprint $table) {
            // 
            $table->bigIncrements('power_id');
            // varchar形态,权限名称
            $table->string('power_name')->comment('权限名称')->default('');
            // 权限类型
            $table->smallInteger('power_type')->comment('权限类型，1节点，2模块')->default(1)->nullable();
            // 权限状态
            $table->smallInteger('power_status')->comment('权限状态,1可用2禁用')->default(1);
            // 权限备注
            $table->string('power_remark')->comment('权限备注')->default('')->nullable();
            // 权限element图标
            $table->string('power_icon')->comment('权限的element图标')->default('')->nullable();
            // 显示排序
            $table->smallInteger('power_sort')->comment('显示排序')->default(1)->nullable();
            // 父级权限
            $table->integer('power_parent_id')->comment('父级权限')->default(0)->nullable();
            // 权限路由
            $table->string('power_url')->comment('权限路由')->default('');
            // 创建和修改时间
            $table->timestamp('power_addtime')->default(date("Y-m-d H:i:s"));
            $table->timestamp('power_updatetime')->default(date("Y-m-d H:i:s"));

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //回滚迁移
        Schema::drop('power');
    }
}
