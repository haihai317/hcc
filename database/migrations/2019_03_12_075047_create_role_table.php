<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //生成角色表
        Schema::create('role', function (Blueprint $table) {
            // 
            $table->bigIncrements('role_id');
            // varchar形态,职位名称
            $table->string('role_name')->comment('职位名称')->default('');
            // 职位代码
            $table->string('role_code')->comment('职位代码')->default('')->nullable();
            // 职位状态
            $table->smallInteger('role_status')->comment('职位状态,1可用2禁用')->default(1);
            // 职位等级
            $table->smallInteger('role_level')->comment('职位等级')->default(1)->nullable();
            // 创建和修改时间
            $table->timestamp('role_addtime')->default(date("Y-m-d H:i:s"));
            $table->timestamp('role_updatetime')->default(date("Y-m-d H:i:s"));

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
        Schema::drop('role');
    }
}
