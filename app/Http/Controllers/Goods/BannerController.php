<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\Banner;
use App\Model\Costore;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;

class BannerController extends Controller
{
    /**
     * 添加新的banner
     */
    public function addBanner(Request $request){
    	$arr = $request->only(["banner_name",'banner_img','banner_sort','banner_url']);
    	$res = Banner::create($arr);
    	return ajaxReturn(0,$res,'success');
    }






    /**
     * 展示所有的banner
     */
    public function showAllBanner(Request $request){
    	
        $banners = Banner::orderBy('Banner_sort','asc')->get();

        return ajaxReturn(0,$banners,'success');
    }



    /**
     * 编辑banner信息
     */
    public function editBanner(Request $request){
    	$id = $request->input("banner_id",0);
    	if($id > 0){
    		$arr = $request->only(['banner_name','banner_img','banner_url',"banner_sort"]);
    		$res = Banner::where("banner_id",$id)->update($arr);
    		return ajaxReturn(0,'','success');
    	}else{
    		return ajaxReturn(1,'','无法获取banner信息');
    	}
    }





    /**
     * 删除品牌
     */
    public function delBanner(Request $request){
		$id = $request->input("id",0);
    	if($id > 0){
	        // 没有相关部门，直接删除
	        $res = Banner::destroy($id);
	        if($res){
	            return ajaxReturn(0,'','success');
	        }else{
	            return ajaxReturn(2,'','删除失败，请重试');
	        }
    	}else{
    		return ajaxReturn(1,'','无法获取品牌信息');
    	}
    }
}
