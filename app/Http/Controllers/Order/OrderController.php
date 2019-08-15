<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\GoodsSpecGroup;
use App\Model\Goods;
use App\Model\Order;
use App\Model\OrderGoods;
use App\Model\OrderRefund;
use Illuminate\Support\Facades\Input;

class OrderController extends Controller
{
    /**
     * 创建订单
     */
    public function makeOrder(Request $request){
    	// 获取购物车里的商品参数
    	$arr = $request->input("car_arr",[]);
    	$brr = json_decode($arr,true);
    	//订单信息
    	$order = array();

    	// 订单商品总额
    	$order['order_goods_money'] = 0;
    	// 订单运费
    	$order['order_trans_money'] = 0;

    	
    	// 检测商品是否下架，库存是否足够
    	foreach ($brr as $key => $value) {
    		// 查询该商品
    		$goodsres = Goods::where('goods_id',intval($value['goods_id']))->first();
    		// 该商品是否存在
    		if(empty($goodsres)) return ajaxReturn(2,"","该商品不存在！");

    		$order['order_trans_money'] = $goodsres['goods_transfee'];

    		if($goodsres['goods_ifspec'] == 2){
    			// 该商品规格是否存在、库存是否足够
    			$specres = GoodsSpecGroup::where("spec_id",intval($value['spec_id']))->first();
    			if(empty($specres)) return ajaxReturn(2,"","该商品规格已被移除！");
    			if(intval($value['buy_num']) > $specres['spec_store']) return ajaxReturn(1,"",$value['goods_title']."库存不足！");

    			// 商品金额
    			$order['order_goods_money'] += $specres['spec_marketprice']*intval($value['buy_num']);
    			// 减少商品库存
    			$reduce_num = $specres['spec_store'] - intval($value['buy_num']);
    			GoodsSpecGroup::where("spec_id",intval($value['spec_id']))->update(["spec_store"=>$reduce_num]);

    		}else{
    			if($value['buy_num'] > $goodsres['goods_store']) return ajaxReturn(1,"",$value['goods_title']."库存不足！");
				// 商品金额
    			$order['order_goods_money'] += $goodsres['goods_marketprice']*intval($value['buy_num']);
    			// 减少商品库存
    			$reduce_num = $goodsres['goods_store'] - intval($value['buy_num']);
    			GoodsSpecGroup::where("goods_id",intval($value['goods_id']))->update(["goods_store"=>$reduce_num]);
    		}
    	}

    	// 保存订单
    	// 用户id
    	$order['order_user_id'] = $request->get('user_id');
    	// 订单号
    	$order_code = getRandChar(10);
    	$order['order_code'] = $order_code.time();
    	// 收货地址
    	$order['order_addr_id'] = $request->input('order_addr_id',0);
    	// 订单状态
    	$order['order_status'] = 1;
    	// 订单备注
    	$order['order_remark'] = $request->input('order_remark','');
    	$order['order_createtime'] = date('Y-m-d H:i:s');
    	// 创建订单信息
    	$data = Order::create($order);
    	// 创建订单商品信息
    	$order_id = $data->order_id;
    	foreach ($brr as $key => $value) {
    		OrderGoods::create(["og_order_id"=>$order_id,"og_goods_id"=>$value["goods_id"],"og_goods_title"=>$value['goods_title'],"og_goods_specid"=>$value['spec_id'],"og_goods_specname"=>$value['spec_name'],"og_goods_num"=>$value["buy_num"],"og_goods_img"=>$value['img'],"og_goods_price"=>$value['marketprice']]);
    	}
    	return ajaxReturn(0,$order_id,'success');
    }





    /**
     * 展示订单列表
     */
    public function showOrderList(Request $request){
    	// 获取参数
    	$params = Input::except(["headers","user_id","power_str"]);
    	// 页数
    	$page = $params['page'];
    	$offset = ($page - 1)*10 ;

		// 书写查询条件
		$where = ' shop_order.order_status <> 4 ';
		if($params["order_code"] != null){
			$where .= " and shop_order.order_code like '%".$params['order_code']."%' ";
		}
        if($params['order_express_num'] != null){
            $where .=" and shop_order.order_express_num = ".$params['order_express_num'];
        }
        if($params['order_pay_way'] != null){
            $where .= " and shop_order.order_pay_way = ".$params['order_pay_way'];
        }

        if($params['order_status'] != null){
            $where .= " and shop_order.order_status = ".$params['order_status'];
        }


        if($params['goods_title'] != null){
            $where .= " and shop_order_goods.og_goods_title like '%".$params['goods_title']."%' ";
            $orders = Order::join('shop_order_goods','shop_order_goods.og_order_id','=','shop_order.order_id')
                  ->join('shop_user','shop_user.user_id','=','shop_order.order_user_id')
                  ->join('shop_user_addr','shop_user_addr.addr_id','=','shop_order.order_addr_id')
                  ->select('shop_order.*',"shop_user.user_phone","shop_user_addr.*")
                  ->whereRaw($where)
                  ->orderBy('order_id','desc')
                  ->offset($offset)
                  ->limit(10)
                  ->get();
        	$count = Order::join('shop_order_goods','shop_order_goods.og_order_id','=','shop_order.order_id')
        				  ->whereRaw($where)->select('order_id')->count();

        }else{
            $orders = Order::join('shop_user','shop_user.user_id','=','shop_order.order_user_id')
                  ->join('shop_user_addr','shop_user_addr.addr_id','=','shop_order.order_addr_id')
                  ->select('shop_order.*',"shop_user.user_phone","shop_user_addr.*")
                  ->whereRaw($where)
                  ->orderBy('order_id','desc')
                  ->offset($offset)
                  ->limit(10)
                  ->get();
        	$count = Order::whereRaw($where)->select('order_id')->count();
        }


        $res['orders'] = $orders;
        $res['count'] = $count;

		return ajaxReturn(0,$res,'success');
    }






    /**
     * 提交物流信息
     */
    public function addExpressMes(Request $request){
    	// 获取要更新的数据
    	$express = Input::only(["order_express",'order_express_sn','order_express_num']);
    	$order_id = $request->input("order_id",0);
    	if($order_id > 0){
    		$express['order_status'] = 3;
    		$express['order_sendtime'] = date("Y-m-d H:i:s");
    		$res = Order::where("order_id",$order_id)->update($express);
    		if($res){
    			return ajaxReturn(0,$res,'success');
    		}else{
    			return ajaxReturn(1,'','更新物流信息失败');
    		}
    	}else{
    		return ajaxReturn(2,'','无法找到订单信息！');
    	}
    }






    /**
     * 后台展示订单详情
     */
    public function showOrderDetail(Request $request){
    	$order_id = $request->input("order_id",0);
    	if($order_id > 0){
    		// 查询订单信息
    		$order = Order::join("shop_user","shop_user.user_id","=","shop_order.order_user_id")
    					  ->join("shop_user_addr","shop_user_addr.addr_id",'=','shop_order.order_addr_id')
    					  ->where("order_id",$order_id)
    					  ->select("shop_order.*","shop_user.user_name",'shop_user.user_phone',"shop_user_addr.*")
    					  ->first();
    		// 订单商品信息
    		$ordergoods = Order::find($order_id)->hasgoods()->get();
    		$res['order'] = $order;
    		$res['goods'] = $ordergoods;
    		// 订单退款相关
    		$refunds = Order::find($order_id)->hasrefunds()->get();
    		$res['refunds'] = $refunds;
    		return ajaxReturn(0,$res,'success');
    	}else{
    		return ajaxReturn(2,'','无法找到订单信息！');
    	}
    }






    /**
     * 展示退款退货处理中的订单列表
     */
    public function showRefundList(Request $request){
    	// 获取参数
    	$params = Input::except(["headers","user_id","power_str"]);
    	// 页数
    	$page = $params['page'];
    	$offset = ($page - 1)*10 ;

		// 书写查询条件
		$where = ' shop_order.order_status = 4 ';
		if($params["order_code"] != null){
			$where .= " and shop_order.order_code like '%".$params['order_code']."%' ";
		}

        if($params['refund_status'] != null){
            $where .= " and shop_order_refund.refund_status = ".$params['refund_status'];
        }

        $orders = Order::join('shop_user','shop_user.user_id','=','shop_order.order_user_id')
              ->join('shop_order_refund','shop_order_refund.refund_order_id','=','shop_order.order_id')
              ->select('shop_order.*',"shop_user.user_phone","shop_order_refund.*")
              ->whereRaw($where)
              ->whereNotIn('refund_status', [2, 3, 6,8])
              ->orderBy('refund_id','desc')
              ->offset($offset)
              ->limit(10)
              ->get();

    	$count = Order::join('shop_order_refund','shop_order_refund.refund_order_id','=','shop_order.order_id')
    				  ->whereRaw($where)
    				  ->whereNotIn('refund_status', [2, 3, 6,8])
    				  ->select('order_id')->count();
      
        $res['orders'] = $orders;
        $res['count'] = $count;

		return ajaxReturn(0,$res,'success');
    }











    /**
     * 用户创建一条退换申请
     */
	public function createRefund(Request $request){
		$arr = array();
		$arr['refund_order_id'] = $request->input("order_id",0);
		$arr['refund_type'] = $request->input("refund_type",'');
		$arr['refund_reason'] = $request->input("refund_reason",'');
		$arr['refund_img'] = $request->input("refund_img",'');
		$arr['refund_status'] = 1;
		$arr['refund_createtime'] = date("Y-m-d H:i:s");
		if($arr['refund_order_id'] > 0){
			// 找到订单
			$order = Order::where("order_id",$arr['refund_order_id'])->first();
			if(!empty($order)){
				// 检测是否是已发货的状态，判断能否申请仅退款
				if($order['order_status'] > 2 && $arr['refund_type'] == 1){
					return ajaxReturn(4,'','该订单已发货，无法申请仅退款');
				}
				// 将该订单状态改为退款退货
				Order::where("order_id",$arr['refund_order_id'])->update(["order_status"=>4]);
				// 添加退换记录
				$res = OrderRefund::create($arr);
				if($res){
					return ajaxReturn(0,$res,'success');
				}else{
					return ajaxReturn(3,'','创建申请失败');
				}
			}else{
				return ajaxReturn(2,'','无法找到相对应的订单');
			}
		}else{
			return ajaxReturn(1,'','无法找到相对应的订单');
		}
	}    








    /**
     * 后台展示退换订单详情
     */
    public function showRefundDetail(Request $request){
    	$refund_id = $request->input("refund_id",0);
    	if($refund_id > 0){
    		// 查询退换单详情
    		$refund = OrderRefund::where('refund_id',$refund_id)->first();
    		// dd($refund);
    		// 查询订单信息
    		$order = Order::join("shop_user","shop_user.user_id","=","shop_order.order_user_id")
    					  ->join("shop_user_addr","shop_user_addr.addr_id",'=','shop_order.order_addr_id')
    					  ->where("order_id",$refund['refund_order_id'])
    					  ->select("shop_order.*","shop_user.user_name",'shop_user.user_phone',"shop_user_addr.*")
    					  ->first();
    		// 订单商品信息
    		$ordergoods = Order::find($refund['refund_order_id'])->hasgoods()->get();
    		$res['order'] = $order;
    		$res['goods'] = $ordergoods;;
    		$res['refunds'] = $refund;
    		return ajaxReturn(0,$res,'success');
    	}else{
    		return ajaxReturn(2,'','无法找到订单信息！');
    	}
    }








    /**
     * 商家拒绝仅退款的需求
     */
    public function refuseBackMoney(Request $request){
    	// 获取退款id
    	$refund_id = $request->input('refund_id',0);
    	if($refund_id>0){
    		// 修改退款单状态
    		$res = OrderRefund::where("refund_id",$refund_id)->update(["refund_status"=>3]);
    		return ajaxReturn(0,$res,"success");
    	}else{
    		return ajaxReturn(1,'','无法找到订单信息！');
    	}
    }








    /**
     * 商家同意并执行退款
     */
    public function agreeBackMoney(Request $request){
    	// 获取退款id
    	$refund_id = $request->input('refund_id',0);
    	if($refund_id>0){
    		// 修改退款单状态
    		$res = OrderRefund::where("refund_id",$refund_id)->update(["refund_status"=>2]);
    		return ajaxReturn(0,$res,"success");
    	}else{
    		return ajaxReturn(1,'','无法找到订单信息！');
    	}
    }








    /**
     * 商家保存选择的寄回地址，并改状态为等待买家寄回
     */
    public function saveRefundBackAddr(Request $request){
        $addr_id = $request->input('addr_id',0);
        $refund_id = $request->input("refund_id",0);
        $res = OrderRefund::where("refund_id",$refund_id)->update(["refund_back_addr_id"=>$addr_id,"refund_status"=>4]);
        if($res){
            return ajaxReturn(0,$res,'success');
        }else{
            return ajaxReturn(1,'','提交寄回地址信息失败');
        }
    }






    /**
     * 商家收到退货并同意退款
     */
    public function doBackMoney(Request $request){
        // 获取退款id
        $refund_id = $request->input('refund_id',0);
        if($refund_id>0){
            // 修改退款单状态
            $res = OrderRefund::where("refund_id",$refund_id)->update(["refund_status"=>6,"refund_finishtime"=>date("Y-m-d H:i:s")]);
            return ajaxReturn(0,$res,"success");
        }else{
            return ajaxReturn(1,'','无法找到订单信息！');
        }
    }




    /**
     * 商家填写寄回地址
     */
    public function writeRefundSendExpress(Request $request){
        $refund_id = $request->input("refund_id");
        $refund_send_express = $request->input("refund_send_express");
        $refund_send_expresssn = $request->input("refund_send_expresssn");
        $data = OrderRefund::where("refund_id",$refund_id)->update(["refund_status"=>7,"refund_send_express"=>$refund_send_express,"refund_send_expresssn"=>$refund_send_expresssn,'refund_sendtime'=>date("Y-m-d H:i:s")]);
        return ajaxReturn(0,$data,"success");
    }

}
