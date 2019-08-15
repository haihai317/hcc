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
use App\Model\Address;
use Illuminate\Support\Facades\Input;

class OrderApiController extends Controller
{
    /**
     * 用户查询订单列表
     */
    public function showOrderList(Request $request){
    	// 获取参数
    	$params = Input::except(["apikey","apitimes","token"]);
    	$user_id = $request->get("user_id",0);
    	// 页数
    	$page = $params['page'];
    	$offset = ($page - 1)*10 ;

  		// 书写查询条件
  		$where = " shop_order.order_status < 6 and shop_order.order_user_id = {$user_id} ";

        if($params['order_status'] != null){
            $where .= " and shop_order.order_status = ".$params['order_status'];
        }
        // dd($where);
        // 查询所有订单
        $orders = Order::whereRaw($where)
                  ->orderBy('order_id','desc')
                  ->offset($offset)
                  ->limit(10)
                  ->get();

        // 查询该订单下的所有商品信息
        foreach ($orders as $key => $value) {
        	$value->order_goods = OrderGoods::where('shop_order_goods.og_order_id','=',$value->order_id)
								            ->select('shop_order_goods.*')
								            ->get();
        }
       
		  return ajaxReturn(0,$orders,'success');
    }




    /**
     * 用户查看订单详情
     */
    public function showOrderDetail(Request $request){
      $order_id = $request->input("order_id",0);
      // 查询订单
      $order = Order::where("order_id",$order_id)->first();
      // 查询收货地址
      $order->address = Address::where("addr_id",$order->order_addr_id)->first();
      // 查询订单商品
      $order->order_goods = OrderGoods::where("og_order_id",$order_id)->get();
      return ajaxReturn(0,$order,"success");
    }



    /**
     * 将订单置为已付款
     */
    public function makeHavePay(Request $request){
      $order_id = $request->input("order_id",0);
      $params = $request->only(['order_pay_way', 'order_pay_code']);
      $params["order_paytime"] = date("Y-m-d H:i:s");
      $params["order_status"] = 2;
      $res = Order::where("order_id",$order_id)->update($params);
      return ajaxReturn(0,$res,"success");
    }



    /**
     * 取消未支付的订单
     */
    public function cancelOrder(Request $request){
      $order_id = $request->input("order_id",0);
      $res = Order::where("order_id",$order_id)->first();
      if($res->order_status == 1){
        Order::where("order_id",$order_id)->update(["order_status"=>6]);
        return ajaxReturn(0,"","success");
      }else{
        return ajaxReturn(1,"","您的订单已经支付，无法取消！");
      }
    }





    /**
     * 用户提交退款申请
     */
    
    public function applyForRefund(Request $request){
      $order_id = $request->input("refund_order_id",0);
      $refund_type = $request->input("refund_type",1);
      $refund_reason = $request->input("refund_reason","暂无");
      // 检查订单当前状态，看是否适合退换
      $res = Order::where("order_id",$order_id)->first();
      if($res->order_status > 2 && $refund_type == 1 ){
        return ajaxReturn(1,"","该订单已经发货，无法为您执行仅退款操作！");
      }else if($res->order_status < 3 && $refund_type > 1 ){
        return ajaxReturn(1,"","该订单尚未发货，无法为您执行退换货操作！");
      }
      // 将订单修改为退换状态
      Order::where("order_id",$order_id)->update(["order_status"=>4]);
      // 保存该订单的退换记录
      $refund = array();
      $refund["refund_order_id"] = $order_id;
      $refund["refund_type"] = $refund_type;
      $refund["refund_status"] = 1;
      $refund["refund_reason"] = $refund_reason;
      $refund["refund_createtime"] = date("Y-m-d H:i:s");
      $data = OrderRefund::create($refund);
      if($data){
        return ajaxReturn(0,"","success");
      }else{
        return ajaxReturn(1,"","创建退换申请失败！");
      }
    }




    /**
     * 用户确认收货
     */
    public function completeOrder(Request $request){
      $order_id = $request->input("order_id",0);
      $user_id = $request->get("user_id",0);
      $res = Order::where([["order_id",'=',$order_id],["order_user_id",'=',$user_id]])->first();
      if($res->order_status == 3){
        Order::where("order_id",$res->order_id)->update(["order_status"=>5,"order_finishtime"=>date("Y-m-d H:i:s")]);
        return ajaxReturn(0,'',"success");
      }else{
        return ajaxReturn(1,"","订单信息异常");
      }
    }




    /**
     * 用户查看退换详情
     */
    public function showRefundDetail(Request $request){
      $order_id = $request->input("order_id",0);
      $res = OrderRefund::where("refund_order_id",$order_id)->first();
      if($res->refund_status == 4){
        $address = Address::where("addr_id",$res->refund_back_addr_id)->first();
        $res->back_address = $address;
      }
      return ajaxReturn(0,$res,'success');
    }






    /**
     * 用户提交寄回物流信息
     */
    public function sendBackExpress(Request $request){
      $refund_id = $request->input("refund_id",0);
      $refund_back_express = $request->input("refund_back_express",0);
      $refund_back_expresssn = $request->input("refund_back_expresssn",0);
      if($refund_back_express && $refund_back_expresssn){
        $data = OrderRefund::where("refund_id",$refund_id)->update(["refund_back_express"=>$refund_back_express,"refund_back_expresssn"=>$refund_back_expresssn,"refund_status"=>5]);
        if($data){
          return ajaxReturn(0,"","success");
        }else{
          return ajaxReturn(1,"","更新物流信息失败");
        }
      }else{
        return ajaxReturn(2,"","请填写完整的物流信息");
      }     
    }




    /**
     * 用户确认收到换货
     */
    public function getRefundGoods(Request $request){
      $refund_id = $request->input("refund_id",0);
      // 退换状态
      OrderRefund::where("refund_id",$refund_id)->update(["refund_status"=>8,"refund_finishtime"=>date("Y-m-d H:i:s")]);
      
      return ajaxReturn(0,"","success");
    }
}
