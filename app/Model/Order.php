<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'shop_order';

    /**
     * 数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'order_id';

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
     * 获取订单下所有的订单商品。
     */
    public function hasgoods()
    {
        return $this->hasMany('App\Model\OrderGoods','og_order_id','order_id');
        
    }





    /**
     * 获取订单下所有的退款退货请求。
     */
    public function hasrefunds()
    {
        return $this->hasMany('App\Model\OrderRefund','refund_order_id','order_id');
        
    }

}
