<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GoodsSpecItem extends Model
{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'shop_goods_specitem';

    /**
     * 数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'item_id';

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
}
