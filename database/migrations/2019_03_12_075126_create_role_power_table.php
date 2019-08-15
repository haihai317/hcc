<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolePowerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //生成职位权限关联表
        Schema::create('role_power', function (Blueprint $table) {
            // 
            $table->bigIncrements('bind_id');
            // 关联的role_id
            $table->integer('bind_role_id')->comment('关联的role_id');
            // 关联的power_id
            $table->integer('bind_power_id')->comment('关联的power_id');

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
        Schema::drop('role_power');
    }
}
