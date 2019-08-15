<?php

namespace App\Http\Controllers\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\Classify;
use App\Model\Brand;

class ClassifyController extends Controller
{





    /**
     * 新增分类
     * @param  $[parent_id/name/sort/description] 
     */
    public function makeNewClassify(Request $request){
    	// 整理基本信息
    	$arr['class_name'] = $request->input('class_name','');
        $arr['class_sort'] = $request->input('class_sort','');
    	$arr['class_img'] = $request->input('class_img','');
    	$arr['class_description'] = $request->input('class_remark','');
    	// 顶级分类标志参数
    	$class_type = $request->input('class_type',1);
    	// 是否有parent_id,并根据父级分类判断该分类的等级
    	$arr['class_parent_id'] = $request->input('class_parent_id',0);

    	if($class_type == 2 && $arr['class_parent_id'] > 0){
    		// 找到父级分类的level，确定当前分类的level
    		$data = Classify::where("class_id",$arr['class_parent_id'])->first();
    		$arr["class_level"] = $data["class_level"]+1;
    		$res = Classify::create($arr);
    		if($res){
    			return ajaxReturn(0,'','success');
    		}else{
    			return ajaxReturn(1,'','创建分类失败');
    		}
    	}else{
    		// 直接创建顶级分类
    		$arr["class_level"] = 1;
    		$arr["class_parent_id"] = 0;
    		$res = Classify::create($arr);
    		if($res){
    			return ajaxReturn(0,'','success');
    		}else{
    			return ajaxReturn(1,'','创建分类失败');
    		}
    	}
    }












    /**
     * 分类列表
     * @param []
     */
    public function showClassifyList(){
    	// 查询所有的分类
    	$allClass = Classify::all();
    	// 整理无限分类
    	$final = $this->getTree($allClass,0);

    	return ajaxReturn(0,$final,"success");
    	
    }







    /**
     * 为新建分类展示父级分类
     */
    public function showClassifyParent(){
    	$allClass = Classify::select('class_name as label','class_id as value')->get();
    	return ajaxReturn(0,$allClass,"success");
    }







	/**
	 * 无限分类递归函数
	 */
	public function getTree($data,$pid){
		static $tree = array();
		// 递归循环
		foreach ($data as $key => $value) {
			if($value['class_parent_id'] == $pid){
				// 设置多级分类层级关系标识
				$str = '';
				for($i = 0 ; $i < $value['class_level'] ; $i ++){
					$str .= "|——"; 
				}
				$value["alias_name"] = $str.$value['class_name'];
				// 加入数组
				$tree[] = $value;
				// 递归
				$this->getTree($data,$value['class_id']);
				// 销毁该选项
				unset($data[$key]);
			}
		}
		return $tree;
	}


    
    
    


    /**
     * 修改分类信息
     */
    public function editClassifyMes(Request $request){
    	// 获取参数
    	// dd($request);
    	$arr['class_name'] = $request->input('class_name','');
        $arr['class_sort'] = $request->input('class_sort','');
    	$arr['class_img'] = $request->input('class_img','');
    	$arr['class_description'] = $request->input('class_remark','');
    	$class_id = $request->input('class_id','');
    	// 执行更新操作
    	$res = Classify::where("class_id",$class_id)->update($arr);
    	if($res){
    		return ajaxReturn(0,$res,'success');
    	}else{	
    		return ajaxReturn(1,$res,'更新数据失败');
    	}	
    }




    /**
     * 删除分类
     */
    public function deleteClassify(Request $request){
    	$id = $request->input('id',0);
    	if($id > 0){
            $goods = Classify::find($id)->haveGoods()->select("goods_id")->first();
            if(empty($goods)){
                $res = Classify::where("class_id",$id)->delete();
                return ajaxReturn(0,$res,'success');
            }else{
                return ajaxReturn(2,'','该分类下有相关商品，不能删除');
            }
    		
    	}else{
    		return ajaxReturn(1,'','无法获取分类信息');
    	}
    }






    /**
     * 查找所有的分类和品牌
     */
    public function findClAndBr(){
        // 查询所有的分类
        $allClass = Classify::select("class_name as label", "class_id as value","class_id", "class_parent_id")->get();
        // 整理无限分类
        $final = $this->getSecondTree($allClass,0);

        // 查找所有品牌
        $brands = Brand::select("brand_id as value","brand_title as label")->get();

        $res['classify'] = $final;
        $res['brand'] = $brands;

        return ajaxReturn(0,$res,"success");
    }






    /**
     * 分类递归
     */
    public function getSecondTree($data,$pid){
        $tree = array();
        // 递归循环
        foreach ($data as $key => $value) {
            if($value['class_parent_id'] == $pid){
                
                $value["children"] = $this->getSecondTree($data,$value['class_id']);
                if(count($value["children"]) == 0){
                    unset($value["children"]);
                }
                // 加入数组
                $tree[] = $value;
                // 销毁该选项
                unset($data[$key]);
            }
        }
        return $tree;
    }
    
}
