<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Company extends Model
{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'company';

    /**
     * 数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'co_id';

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
    protected $guarded = ['co_addtime'];



    /**
     * 限制查询只包括指定名称的子公司。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfName($query, $name)
    {
        return $query->where('co_name','like', "%{$name}%");
    }



    /**
     * 获取子公司下所有的部门。
     */
    public function departments()
    {
        return $this->hasMany('App\Model\Department','de_co_id','co_id');
        
    }

}
