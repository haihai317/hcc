<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\Brand;
use App\Model\Costore;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;

class BrandController extends Controller
{
    /**
     * 添加新的品牌
     */
    public function addBrand(Request $request){
    	$arr = $request->only(["brand_title",'brand_img','brand_descript']);
    	$res = Brand::create($arr);
    	return ajaxReturn(0,$res,'success');
    }



    /**
     * 上传图片
     * @route post.loginapi/uploadImage
     */
    public function uploadImg() {
        $param = Input::all();
        // 规格图片处理需要该参数
        $spec_name = "";
        if(isset($param["name"])){
            $spec_name = $param["name"];
        }
        try {
            $file = Input::file('file');
            if (!$file) return ajaxReturn(7, '', '请上传图片');
            if (!$file->isValid()) {
                return ajaxReturn(5, '', '图片无效');
            }

            $size = $file->getSize();
            $mime = $file->getClientOriginalExtension();

            if ($size > 2097152) {
                return ajaxReturn(3, '', '图片大小不能超过2M');
            }

            $fileName = date('ymd').rand(1000, 9999).time().'.'.$mime;
            $realPath = $file->getRealPath();

            //上传到腾讯云
            $cos = new Costore();
            $ret = $cos->fileUpload($realPath, $fileName);

            if ($ret['code'] != 0) {
                return ajaxReturn(4, '', '上传失败，请重试');
            }
            $data["url"] = $ret['data']['source_url'];
            $data['name'] = $spec_name;
            return ajaxReturn(0,$data , 'success');
        } catch (\Exception $e) {
            Log::debug('Route post.loginapi/uploadImage;　错误信息：'.$e->getMessage().'。IP:'.$_SERVER['REMOTE_ADDR']);
            return ajaxReturn(500, '', '服务器错误');
        }
    }



    /**
     * 展示所有的商品品牌
     */
    public function showAllBrand(Request $request){
    	$page = $request->input('page',0);
    	$offset = ($page - 1)*10;
    	$limit = 10;
    	$name = $request->input('name','');

        // 查询品牌数据和数量
        if($name != ''){
            $company = Brand::ofName($name)
                                ->orderBy('brand_id','asc')
                                ->offset($offset)
                                ->limit(10)
                                ->get();
            $count = Brand::ofName($name)->count();
        }else{
            $company = Brand::orderBy('brand_id','asc')
                                ->offset($offset)
                                ->limit(10)
                                ->get();
            $count = Brand::count();
        }

        // 整理数据
        $res['coms'] = $company;
        $res['count'] = $count;
        return ajaxReturn(0,$res,'success');
    }



    /**
     * 编辑品牌信息
     */
    public function editBrand(Request $request){
    	$id = $request->input("brand_id",0);
    	if($id > 0){
    		$arr = $request->only(['brand_title','brand_img','brand_descript']);
    		$res = Brand::where("brand_id",$id)->update($arr);
    		return ajaxReturn(0,'','success');
    	}else{
    		return ajaxReturn(1,'','无法获取品牌信息');
    	}
    }





    /**
     * 删除品牌
     */
    public function delBrand(Request $request){
		$id = $request->input("id",0);
    	if($id > 0){
    		// 查询院系下是否有专业
            $de = Brand::find($id)->haveGoods()->select('goods_id')->first();
            if(empty($de)){
                // 没有相关部门，直接删除
                $res = Brand::destroy($id);
                if($res){
                    return ajaxReturn(0,'','success');
                }else{
                    return ajaxReturn(2,'','删除失败，请重试');
                }
            }else{
                // 有相关部门无法删除
                return ajaxReturn(3,'','该公司下有相关部门，无法删除');
            }
    	}else{
    		return ajaxReturn(1,'','无法获取品牌信息');
    	}
    }
    
}
