<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Role extends Model
{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'role';

    /**
     * 数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'role_id';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = ['role_addtime'];





    /**
     * 数据模型的启动方法
     * 匿名的全局作用域，为模型的所有查询添加条件约束
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_status', function(Builder $builder) {
            $builder->where('role_status', '=', 1);
        });

        // 需要的话通过以下方法移除全局作用域
        // Department::withoutGlobalScope('de_status')->get(); 
    }




    /**
     * 限制查询只包括指定名称的部门。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfName($query, $name)
    {
        return $query->where('role_name','like', "%{$name}%");
    }




    /**
     * 获取该职位下的所有员工信息
     */
    public function haveEms()
    {
        return $this->hasMany('App\Model\Employee','em_role_id',"role_id");
    }


}
