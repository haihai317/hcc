<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //生成分公司表
        Schema::create('company', function (Blueprint $table) {
            // 
            $table->bigIncrements('co_id');
            // 公司全称 varchar
            $table->string('co_name')->comment('公司全称')->default('');
            // 公司简称
            $table->string('co_alias')->comment('公司简称')->default('')->nullable();
            // 公司代码
            $table->string('co_code')->comment('公司代码')->default('')->nullable();
            // 公司联系电话
            $table->string('co_tel')->comment('公司联系电话')->default('')->nullable();
            // 公司地址
            $table->string('co_addr')->comment('公司地址')->default('')->nullable();
            // 公司备注
            $table->string('co_remark')->comment('公司备注')->default('')->nullable();
            // 创建和修改时间
            $table->timestamp('co_addtime')->default(date("Y-m-d H:i:s"));
            $table->timestamp('co_updatetime')->default(date("Y-m-d H:i:s"));
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
        Schema::drop('company');
    }
}
