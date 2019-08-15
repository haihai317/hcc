<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\Company;
use Illuminate\Support\Facades\Input;

class CompanyController extends Controller
{
    // 获取所有子公司的列表
    public function showComList(Request $request){
        $page = $request->input('page',1);
        $offset = ($page - 1)*10 ;
        $name = $request->input('name','');

        // 查询子公司数据和数量
        if($name != ''){
            $company = Company::ofName($name)
                                ->orderBy('co_id','asc')
                                ->offset($offset)
                                ->limit(10)
                                ->get();
            $count = Company::ofName($name)->count();
        }else{
            $company = Company::orderBy('co_id','asc')
                                ->offset($offset)
                                ->limit(10)
                                ->get();
            $count = Company::count();
        }

        // 整理数据
        $res['coms'] = $company;
        $res['count'] = $count;
        return ajaxReturn(0,$res,'success');
        	
    }

    // 新建一个新的子公司
    public function makeCom(Request $request){

    	// 实例化模型
    	$com = new Company;
        // 获取插入数据
        $params = Input::except(['headers','user_id','power_str']);
        // 保存
        $res = $com->create($params);
        if($res){
        	return ajaxReturn(0,$res,'success');
        }else{
        	return ajaxReturn(1,$res,'创建数据失败，请重试');
        }
    }


    // 修改子公司
    public function makEdit(Request $request){
    	$id = $request->input('co_id',0);
    	if($id > 0){
    		// 获取更新参数
    		$params = Input::except(['headers','co_id','user_id','power_str']);
            $params['co_updatetime'] = date('Y-m-d H:i:s');
    		// 批量更新
	        $res = Company::where('co_id',$id)
					          ->update($params);
	        if($res){
	        	return ajaxReturn(0,'','success');
	        }else{
	        	return ajaxReturn(2,'','修改失败，请重试');
	        }
    		
    	}else{
    		return ajaxReturn(1,'','无法获取子公司信息，请联系管理员');
    	}    	
    }

    // 删除指定子公司
    public function delTheCom(Request $request){
    	$id = $request->input('id',0);
    	if($id > 0){
    		// 查询院系下是否有专业
            $de = Company::find($id)->departments()->select('de_id')->first();
            if(empty($de)){
                // 没有相关部门，直接删除
                $res = Company::destroy($id);
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
    		return ajaxReturn(1,'','无法获取子公司信息，请联系管理员');
    	}
    }


    // 为新建部门查找所有子公司
    public function findComPoint(){
        // 查询子公司数据和数量
        $company = Company::select('co_id as value','co_name as label')->orderBy('co_id','asc')->get();
        // 整理数据
        return ajaxReturn(0,$company,'success');
    }
}
