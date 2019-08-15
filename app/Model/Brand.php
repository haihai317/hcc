<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'shop_goods_brand';

    /**
     * 数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'brand_id';

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
    protected $guarded = [];




    /**
     * 限制查询只包括指定名称的品牌。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfName($query, $name)
    {
        return $query->where('brand_title','like', "%{$name}%");
    }



    /**
     * 获取该品牌下所有的商品
     */
    public function haveGoods()
    {
        return $this->hasMany('App\Model\Goods','goods_brand_id','brand_id');
    }

}
