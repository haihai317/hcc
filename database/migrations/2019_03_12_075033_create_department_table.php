<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //生成员工表
        Schema::create('department', function (Blueprint $table) {
            // 
            $table->bigIncrements('de_id');
            // 部门名称
            $table->string('de_name')->comment('部门名称')->default('');
            // 部门代码
            $table->string('de_code')->comment('部门代码')->default('')->nullable();
            // 父级部门ID
            $table->integer('de_parent_id')->comment('父级部门ID')->nullable();
            // 所属的公司ID
            $table->integer('de_co_id')->comment('所属的公司ID');
            // 部门显示排序
            $table->smallInteger('de_sort')->comment('部门显示排序')->default(1)->nullable();
            //部门等级
            $table->smallInteger('de_level')->comment('部门等级')->default(1)->nullable();
            // 部门状态
            $table->smallInteger('de_status')->comment('部门状态 1正常 2禁用')->default(1);
            // 备注
            $table->string('de_remark')->comment('备注')->default('')->nullable();
            // 创建和修改时间
            $table->timestamp('de_addtime')->default(date("Y-m-d H:i:s"));
            $table->timestamp('de_updatetime')->default(date("Y-m-d H:i:s"));

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
        Schema::drop('department');
    }
}
