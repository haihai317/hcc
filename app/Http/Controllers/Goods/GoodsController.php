<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\Goods;
use App\Model\GoodsImg;
use App\Model\GoodsSpecItem;
use App\Model\GoodsSpecGroup;
use App\Model\GoodsParam;
use App\Model\OrderGoods;
use Illuminate\Support\Facades\Input;

class GoodsController extends Controller
{
    /**
     * 添加商品信息
     */
    public function makeAdd(Request $request){
    	$arr = $request->input("form",[]);

    	$goods_id = 0 ;
        // 如果是修改商品信息
    	if(isset($arr['goods_id'])){
    		$goods_id = $arr['goods_id'];
			//删除原来的商品副图
			GoodsImg::where("img_goods_id",$goods_id)->delete();
	    	//删除原来的规格等级
	    	GoodsSpecItem::where("item_goods_id",$goods_id)->delete();
	    	//删除原来的规格组合
	    	GoodsSpecGroup::where("spec_goods_id",$goods_id)->delete();
            //删除原来的商品参数
            GoodsSpecGroup::where("param_goods_id",$goods_id)->delete();
    	}

        // 保存商品参数
        $params = $arr['paramArr'];
        unset($arr["paramArr"]);
        unset($arr["ifparams"]);
    	
    	// 是否开启多规格
    	if($arr['delivery'] == true){
			$arr["goods_ifspec"] = 2;
    	}else{
			$arr["goods_ifspec"] = 1;
    	}

    	// 商品详情
    	$arr['goods_text'] = $arr["detail_text"];
    	unset($arr["detail_text"]);
    	unset($arr["delivery"]);
    	$arr["goods_createtime"] = date("Y-m-d H:i:s");
    	// 分别存储三级分类
    	$arr['goods_classify_id1'] = $arr['goods_classify_value'][0];
    	$arr['goods_classify_id2'] = $arr['goods_classify_value'][1];
    	$arr['goods_classify_id3'] = $arr['goods_classify_value'][2];
    	unset($arr["goods_classify_value"]);

    	// 商品专区展示
        $arr["goods_iselite"] = 1;
        $arr["goods_isnewshow"] = 1;
    	for ($i=0; $i < count($arr["type"]); $i++) { 
    		if($arr["type"][$i] == "精品推荐"){
    			$arr["goods_iselite"] = 2;
    		}else if($arr["type"][$i] == "新品首发"){
				$arr["goods_isnewshow"] = 2;
    		}
    	}
    	unset($arr["type"]);
    	unset($arr["specArr"]);
    	unset($arr["specGroup"]);

    	// 保存商品信息
    	$theid = 0;
    	if($goods_id > 0){
    		Goods::where("goods_id",$goods_id)->update($arr);
    		$theid = $goods_id;
    	}else{
    		$res = Goods::create($arr);
    		$theid = $res["goods_id"];
    	}

		$imgs = $request->input("goodsImgUrl",[]);
    	// 整理副图数组并存放
    	$brr = array();
    	foreach ($imgs as $key => $value) {
			$brr[$key]['img_goods_id'] = $theid;
    		$brr[$key]['img_url'] = $value;
    		// 保存商品副图
    		GoodsImg::create($brr[$key]);
    	}

    	// 存放规格（两个规格等级表和一个规格组合表）
    	// 规格等级表数据主要用于修改商品信息时呈现规格等级，规格组合表数据用于前端购买商品的规格呈现
    	// 以下存放规格等级
    	$specItems = $request->input("form",[]);
    	$specItems = $specItems["specArr"];
    	foreach ($specItems as $key => $value) {
    		// 存放一级规格
    		$data = GoodsSpecItem::create(["item_name"=>$value["name"],"item_goods_id"=>$theid]);
    		foreach ($value["children"] as $k => $v) {
    			// 存放二级规格
    			GoodsSpecItem::create(["item_name"=>$v["name"],"item_goods_id"=>$theid,"item_parent_id"=>$data["item_id"]]);
    		}
    	}

        // 保存商品参数
        for ($i=0; $i < count($params); $i++) { 
            $params[$i]['param_goods_id'] = $theid;
            GoodsParam::create($params[$i]);
        }

    	// 以下存放规格组合
    	$specgs = $request->input("form",[]);
    	$specgs = $specgs["specGroup"];
    	foreach ($specgs as $key => $value) {
    		$crr["spec_goods_id"] = $theid; 
    		$crr["spec_name"] = $value["name"]; 
    		$crr["spec_img"] = $value["spec_img"]; 
    		$crr["spec_store"] = $value["store"]; 
    		$crr["spec_marketprice"] = $value["marketprice"]; 
    		$crr["spec_costprice"] = $value["costprice"]; 
    		$crr["spec_saleprice"] = $value["saleprice"]; 
    		$crr["spec_memberprice"] = $value["memberprice"]; 
    		$crr["spec_sn"] = $value["sn"]; 
    		GoodsSpecGroup::create($crr);
    	}
    	
    	return ajaxReturn(0,$theid,"success");
    	
    }



    /**
     * 展示商品信息
     */
    public function showGoodsList(Request $request){
    	// 获取参数
    	$params = Input::except(["headers","user_id","power_str"]);
    	// 页数
    	$page = $params['page'];
    	$offset = ($page - 1)*10 ;

		// 书写查询条件
        $where = ' shop_goods.goods_status = '.$params['goods_status'];

        if($params['goods_title'] != null){
            $where .= " and shop_goods.goods_title like '%".$params['goods_title']."%' ";
        }
        if(!empty($params['goods_classify_value'])){
            $where .=" and shop_goods.goods_classify_id3 = ".$params['goods_classify_value'][2];
        }
        if($params['goods_brand_id'] != null){
            $where .= " and shop_goods.goods_brand_id = ".$params['goods_brand_id'];
        }

        $ems = Goods::join('shop_goods_brand','shop_goods_brand.brand_id','=','shop_goods.goods_brand_id')
                  ->join('shop_goods_classify','shop_goods_classify.class_id','=','shop_goods.goods_classify_id3')
                  ->select('shop_goods.*','shop_goods_classify.class_name','shop_goods_brand.brand_title')
                  ->whereRaw($where)
                  ->orderBy('goods_id','asc')
                  ->offset($offset)
                  ->limit(10)
                  ->get();

        // 多规格商品，查询其规格列表
        foreach ($ems as $key => $value) {
        	if($value->goods_ifspec == 2){
        		$value->spec_list = GoodsSpecGroup::where('spec_goods_id','=',$value->goods_id)->get();
        	}
        }


        $count = Goods::whereRaw($where)->select('goods_id')->count();

        $res['ems'] = $ems;
        $res['count'] = $count;

		return ajaxReturn(0,$res,'success');
    }






    /**
     * 下架商品
     */
    public function dropDown(Request $request){
    	$id = $request->input("goods_id",0);
    	if($id > 0){
    		// 先检查该商品是否关联未完成的订单
    		$data = OrderGoods::join("shop_order","shop_order.order_id",'=','shop_order_goods.og_order_id')
    						  ->where([["shop_order_goods.og_goods_id",'=',$id],["shop_order.order_status","<",7]])
    						  ->first();
    		if(empty($data)){
    			// 没有关联未完成的订单
    			// 执行下架操作
	    		$res = Goods::where("goods_id",$id)->update(['goods_status'=>2]);
	    		if($res){
	    			return ajaxReturn(0,$res,'success');
	    		}else{
	    			return ajaxReturn(2,'','下架失败');
	    		}
    		}else{
    			return ajaxReturn(3,"","该商品关联未完成的订单，无法下架！");
    		}	
    	}else{
    		return ajaxReturn(1,'','未找到相关商品');
    	}
    }





    /**
     * 上架商品
     */
    public function dropUp(Request $request){
    	$id = $request->input("goods_id",0);
    	if($id > 0){
    		// 执行上架操作
    		$res = Goods::where("goods_id",$id)->update(['goods_status'=>1]);
    		if($res){
    			return ajaxReturn(0,$res,'success');
    		}else{
    			return ajaxReturn(2,'','上架失败');
    		}
    		
    	}else{
    		return ajaxReturn(1,'','未找到相关商品');
    	}
    }






    /**
     * 删除商品
     */
    public function deleteGoods(Request $request){
    	$id = $request->input("goods_id",0);
    	if($id > 0){
    		$res = Goods::where("goods_id",$id)->delete();
    		if($res){
    			GoodsImg::where("img_goods_id",$id)->delete();
    			GoodsSpecGroup::where("spec_goods_id",$id)->delete();
    			GoodsSpecItem::where("item_goods_id",$id)->delete();
    			return ajaxReturn(0,$res,'success');
    		}else{
    			return ajaxReturn(2,'','删除商品失败');
    		}
    		
    	}else{
    		return ajaxReturn(1,'','未找到相关商品');
    	}
    }






    /**
     * 查询待编辑商品的信息
     */
    public function showEditMes(Request $request){
    	// 根据id查询商品信息
    	$id = $request->input("id",0);
    	if($id == 0) return ajaxReturn(1,'','无法找到该商品信息');
    	$goods = Goods::where("goods_id",$id)->first();
    	$goods = json_decode(json_encode($goods),true);
    	// 是否开启多规格设置
    	if($goods["goods_ifspec"] == 2){
			$goods['delivery'] = true;
    	}else{
			$goods["delivery"] = false;
    	}

    	// 目前所属的三级分类
		$goods["goods_classify_value"] = [$goods['goods_classify_id1'],$goods['goods_classify_id2'],$goods['goods_classify_id3']];

    	// 商品专区展示
    	$goods["type"] = [];
    	if($goods["goods_iselite"] == 2){
			$goods["type"][] = "精品推荐";
		}
		if($goods["goods_isnewshow"] == 2){
			$goods["type"][] = "新品首发";
		}

		// 商品详情
		$goods['detail_text'] = $goods['goods_text'];

		// 查询商品副图信息
		$imgs = GoodsImg::where("img_goods_id",$goods['goods_id'])->select("img_url")->get();
		$goodsImgs = [];
		foreach ($imgs as $key => $value) {
			$goodsImgs[] = $value->img_url;
		}
    	

    	// 查询商品的规格项
    	$specItems = GoodsSpecItem::where("item_goods_id",$goods['goods_id'])->get();
    	// 将规格项整理为符合前端显示的二维数组
    	$items = [];
    	foreach ($specItems as $key => $value) {
    		if($value->item_parent_id == 0){
    			$items[]=["name"=>$value["item_name"],"id"=>$value["item_id"],"children"=>[]];
    			unset($specItems[$key]);
    		}
    	}
    	foreach ($specItems as $key => $value) {
    		for ($i=0; $i < count($items) ; $i++) { 
    			if($value->item_parent_id == $items[$i]['id'] ){
    				$items[$i]['children'][]["name"] = $value["item_name"];
    				break;
    			}
    		}
    		unset($specItems[$key]);	
    	}

    	// 查询商品的规格组合
    	$groups = GoodsSpecGroup::where("spec_goods_id",$goods['goods_id'])->get();
    	foreach ($groups as $key => $value) {
    		$value["name"] = $value["spec_name"]; 
    		$value["store"] = $value["spec_store"]; 
    		$value["marketprice"] = $value["spec_marketprice"]; 
    		$value["costprice"] = $value["spec_costprice"]; 
    		$value["saleprice"] = $value["spec_saleprice"]; 
    		$value["memberprice"] = $value["spec_memberprice"]; 
    		$value["sn"] = $value["spec_sn"]; 
    	}

        // 查询商品的参数
        $params = GoodsParam::where("param_goods_id",$goods['goods_id'])->get();
        $params = json_decode(json_encode($params,true));
        if(!empty($params)){
            $goods['ifparams'] = true;
            $goods['paramArr'] = $params;
        }

    	// 去除不需要的参数
    	unset($goods['goods_classify_id']);
    	unset($goods['goods_classify_id1']);
    	unset($goods['goods_classify_id2']);
    	unset($goods['goods_classify_id3']);
    	unset($goods['goods_createtime']);
    	unset($goods['goods_ifspec']);
    	unset($goods['goods_iselite']);
    	unset($goods['goods_isnewshow']);
    	unset($goods['goods_status']);
    	unset($goods['goods_text']);

    	$goods['specArr'] = $items;
    	$goods['specGroup'] = $groups;

    	$theData['form'] = $goods;
    	$theData['goodsImgUrl'] = $goodsImgs;
    	
    	return ajaxReturn(0,$theData,"success");
    	
    }
}
