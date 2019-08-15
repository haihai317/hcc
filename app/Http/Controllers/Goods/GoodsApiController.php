<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Goods\ClassifyController;
use Illuminate\Support\Facades\DB;
use App\Model\Goods;
use App\Model\Banner;
use App\Model\Classify;
use App\Model\GoodsImg;
use App\Model\GoodsSpecItem;
use App\Model\GoodsParam;
use App\Model\GoodsSpecGroup;
use App\Model\OrderGoods;
use Illuminate\Support\Facades\Input;

class GoodsApiController extends Controller
{
    /**
     * 商城首页数据
     */
    public function showIndex(){
    	$res = array();
    	// 查询商品的一级分类
    	$final = Classify::where("class_level","<",2)->get();
    	// 整理无限分类
    	// $class = new ClassifyController;
    	// $final = $class->getSecondTree($allClass,0);
    	$res['classify'] = $final;

    	// 为首页筛选出新品专区和精品推荐的六条商品信息
    	$new_goods = Goods::where("goods_isnewshow",2)
    					  ->select("shop_goods.goods_main_img","shop_goods.goods_marketprice","shop_goods.goods_title","shop_goods.goods_id")
    					  ->offset(0)
    					  ->limit(6)
    	                  ->orderby("goods_createtime","desc")
    	                  ->get();
    	$top_goods = Goods::where("goods_iselite",2)
    					  ->select("shop_goods.goods_main_img","shop_goods.goods_marketprice","shop_goods.goods_title","shop_goods.goods_id")
    					  ->offset(0)
    					  ->limit(6)
    	                  ->orderby("goods_createtime","desc")
    	                  ->get();

    	$res["new_goods"] = $new_goods;
    	$res["top_goods"] = $top_goods;

    	// 主页轮播图
    	$banner = Banner::all();
    	$res['banners'] = $banner;

    	return ajaxReturn(0,$res,'success');
    }





    /**
     * 查询某一级分类下的新品精品以及他的二级分类
     */
    public function theClassifyIndex(Request $request){
    	$class_id = $request->input("class_id",0);
    	$res = array();
    	// 查询一级分类的二级分类
    	$final = Classify::where("class_parent_id",$class_id)->get();
    	$res['classify'] = $final;

    	// 为首页筛选出新品专区和精品推荐的六条商品信息
    	$top_goods = Goods::where([["goods_iselite",'=',2],["goods_classify_id1","=",$class_id]])
    					  ->select("shop_goods.goods_main_img","shop_goods.goods_marketprice","shop_goods.goods_title","shop_goods.goods_id")
    					  ->offset(0)
    					  ->limit(6)
    	                  ->orderby("goods_createtime","desc")
    	                  ->get();
    	$new_goods = Goods::where([["goods_isnewshow",'=',2],["goods_classify_id1","=",$class_id]])
    					  ->select("shop_goods.goods_main_img","shop_goods.goods_marketprice","shop_goods.goods_title","shop_goods.goods_id")
    					  ->offset(0)
    					  ->limit(6)
    	                  ->orderby("goods_createtime","desc")
    	                  ->get();

    	$res["new_goods"] = $new_goods;
    	$res["top_goods"] = $top_goods;

    	return ajaxReturn(0,$res,'success');
    }



    /**
     * 商品的分类页一级二级分类查询
     */
    public function classifyPage(){
    	// 查询商品的一级分类
    	$allClass = Classify::where("class_level","<",3)->get();
    	// 整理无限分类
    	$class = new ClassifyController;
    	$final = $class->getSecondTree($allClass,0);
    	return ajaxReturn(0,$final,'success');
    }



    /**
     * 商品列表页查询商品的第三级分类
     */
    public function showThirdClass(Request $request){
        // 获取二级分类的参数
        $class_id = $request->input('class_id',0);
        $data = Classify::where("class_parent_id",$class_id)->get();
        return ajaxReturn(0,$data,'success');
    }




    /**
     * 查询二三级分类下的商品列表
     */
    public function showClassGoods(Request $request){
        // 筛选条件
        $second_id = $request->input("second_id",0);
        $third_id = $request->input("third_id",0);
        $sort_num = $request->input("sort_num",0);
        $price_sort = $request->input("price_sort",0);
        $page = $request->input('pages',1);
        $offset = ($page - 1)*10 ;

        $goods = null;
        $where = ' 1=1 ';
        // 是否点击具体的三级分类查询
        if($third_id == 0){
            $where .= " and shop_goods.goods_classify_id2 = {$second_id} ";
        }else{
            $where .= " and shop_goods.goods_classify_id3 = {$third_id} ";
        }
        // 是否按照特定的排序方式查询
        if($sort_num > 0){
            if($sort_num == 1){
                $where .= " order by shop_goods.goods_store desc ";
            }else if($sort_num == 2){
                $where .= " order by shop_goods.goods_createtime desc ";
            }else if($sort_num == 3){
                if($price_sort == 1){
                    $where .= " order by shop_goods.goods_marketprice asc ";
                }else{
                    $where .= " order by shop_goods.goods_marketprice desc ";
                }
            }
        }

        // 查询商品
        $goods = Goods::whereRaw($where)
                      ->select("shop_goods.goods_title","shop_goods.goods_main_img","shop_goods.goods_marketprice","shop_goods.goods_id")
                      ->offset($offset)
                      ->limit(10)
                      ->get();

        return ajaxReturn(0,$goods,'success');
    }




    // 查询商品详情
    public function showGoodsDetail(Request $request){
        // 根据id查询商品信息
        $id = $request->input("goods_id",0);
        if($id == 0) return ajaxReturn(1,'','无法找到该商品信息');
        $goods = Goods::where([["goods_id",'=',$id],["goods_status",'=',1]])->first();
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

        // 查询商品参数
        $params = GoodsParam::where("param_goods_id",$goods["goods_id"])->get();
        $goods['params'] = $params;

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

        $goods['specGroup'] = $groups;
        $goods['imgs'] = $goodsImgs;

        
        return ajaxReturn(0,$goods,"success");
    }
}
