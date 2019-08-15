<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //生成员工表
        Schema::create('employee', function (Blueprint $table) {
            // 
            $table->bigIncrements('em_id');
            // varchar形态,员工姓名/昵称
            $table->string('em_name')->comment('员工姓名')->default('');
            // 员工电话
            $table->string('em_tel')->comment('员工电话')->default('');
            // 登录密码
            $table->string('em_password')->comment('员工登录密码')->default('123456');
            // 员工所属的职位ID
            $table->integer('em_role_id')->comment('员工所属的职位ID');
            // 员工所属的部门ID
            $table->integer('em_de_id')->comment('员工所属的部门ID');
            // 员工所属的公司ID
            $table->integer('em_co_id')->comment('员工所属的公司ID');
            // 员工身份状态
            $table->smallInteger('em_status')->comment('身份状态,1可用2禁用3离职')->default(1);
            // 员工access_token
            $table->string('em_token')->comment('员工身份验证token')->nullable();
            // 员工认证验证码
            $table->string('em_verify')->comment('员工认证验证码')->nullable();
            // 创建和修改时间
            $table->timestamp('em_addtime')->default(date("Y-m-d H:i:s"));
            $table->timestamp('em_updatetime')->default(date("Y-m-d H:i:s"));

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
        Schema::drop('employee');
    }
}
