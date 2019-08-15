<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\Department;
use Illuminate\Support\Facades\Input;

class DepartmentController extends Controller
{
    // 获取所有部门的列表
    public function showDeList(Request $request){
    	$page = $request->input('page',1);
    	$offset = ($page - 1)*10 ;
    	$name = $request->input('name','');

    	// 查询子公司数据和数量
    	if($name != ''){
    		$de = Department::join('company','company.co_id','=','department.de_co_id')
    						->select('department.*','company.co_name')
    						->ofName($name)
    						->orderBy('de_sort','asc')
    						->offset($offset)
    						->limit(10)
    						->get();
    		$count = Department::ofName($name)->count();
    	}else{
    		$de = Department::join('company','company.co_id','=','department.de_co_id')
    						->select('department.*','company.co_name')
    						->orderBy('de_sort','asc')
    						->offset($offset)
    						->limit(10)
    						->get();
    		$count = Department::select('role_id')->count();
    	}		

    	// 整理数据
    	$res['des'] = $de;
    	$res['count'] = $count;
    	return ajaxReturn(0,$res,'success');
    }

    // 新建一个新的部门
    public function makeDe(Request $request){

    	// 实例化模型
    	$de = new Department;
        // 获取插入数据
        $params = Input::except(['headers','user_id','power_str']);
        // 保存
        $res = $de->create($params);
        if($res){
        	return ajaxReturn(0,$res,'success');
        }else{
        	return ajaxReturn(1,$res,'创建数据失败，请重试');
        }
    }


    // 修改部门信息
    public function makEdit(Request $request){
    	$id = $request->input('de_id',0);
    	if($id > 0){
    		// 获取更新参数
    		$params = Input::except(['headers','de_id','user_id','power_str']);
            $params['de_updatetime'] = date('Y-m-d H:i:s');
    		// 批量更新
	        $res = department::where('de_id',$id)
					          ->update($params);
	        if($res){
	        	return ajaxReturn(0,'','success');
	        }else{
	        	return ajaxReturn(2,'','修改失败，请重试');
	        }
    		
    	}else{
    		return ajaxReturn(1,'','无法获取部门信息，请联系管理员');
    	}    	
    }

    // 删除指定部门
    public function delTheDe(Request $request){
    	$id = $request->input('id',0);

    	if($id > 0){
    		// 查询该部门下有无员工
    		$ems = Department::find($id)->haveEms()->select("em_id")->first();
    		if(empty($ems)){
    			$res = Department::destroy($id);
	    		// 其他写法：$res = Compony::where('co_id', $id)->delete();
	    		if($res){
		        	return ajaxReturn(0,'','success');
		        }else{
		        	return ajaxReturn(3,'','删除失败，请重试');
		        }
    		}else{
    			return ajaxReturn(2,'','该部门下尚有从属员工，无法删除！');
    		}		
    	}else{
    		return ajaxReturn(1,'','无法获取部门信息，请联系管理员');
    	}
    }
}
