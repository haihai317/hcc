<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\Role;
use Illuminate\Support\Facades\Input;

class RoleController extends Controller
{
    
    /**
	* @name showRoleList
	* @param none
	* @return 所有的职位信息
	* @discription 查看现有职位列表
	*/

	public function showRoleList(Request $request){
		$page = $request->input('page',1);
		$name = $request->input('name',1);
		// offset处填写的参数
		$offset = ($page - 1)*10 ;
		
        // 查询role
        if($name != ''){
        	$ems = Role::ofName($name)
		    			->orderBy('role_id','asc')
		    			->offset($offset)
		    			->limit(10)
		    			->get();
	        // 查询员工总数
	        $count = Role::ofName($name)->count();
        }else{
        	$ems = Role::orderBy('role_id','asc')
	        			->offset($offset)
	        			->limit(10)
	        			->get();
	        // 查询员工总数
	        $count = Role::count();
        }
        
        $roles['roles'] = $ems;
        $roles['count'] = $count;
		return ajaxReturn(0,$roles,'success');
	}


    
    /**
	* @name makeAddRole
	* @param name/code/status/level;
	* @return 是否创建成功
	* @discription 新建一个职位
	*/

	public function makeAddRole(Request $request){
		$arr  = Input::except(['headers','user_id','power_str']);
		$arr['role_status'] = intval($arr['role_status']);
		// 生成新职位
		$role = Role::create($arr);

		if($role){
			return ajaxReturn(0,$role,'success');
		}else{
			return ajaxReturn(1,'','success');
		}
	}



	// 删除某一职位
	public function makeDel(Request $request){
		$id = $request->input('id',0);
		if($id > 0){
    		// 查询该部门下有无员工
    		$ems = Role::find($id)->haveEms()->select("em_id")->first();

    		if(empty($ems)){
    			// 删除该职位
    			$res = Role::destroy($id);
    			// 删除该职位拥有的权限
    			DB::table('role_power')->where('bind_role_id',$id)->delete();
	    		if($res){
		        	return ajaxReturn(0,'','success');
		        }else{
		        	return ajaxReturn(3,'','删除失败，请重试');
		        }
    		}else{
    			return ajaxReturn(2,'','该职位下尚有从属员工，无法删除！');
    		}		
    	}else{
    		return ajaxReturn(1,'','无法获取职位信息，请联系管理员');
    	}
	}




	// 修改该职位的信息
	public function editRoleMes(Request $request){
		$id = $request->input('role_id',0);
    	if($id > 0){
    		// 获取更新参数
    		$params = Input::except(['headers','role_id','user_id','power_str']);
    		$params['role_updatetime'] = date('Y-m-d H:i:s');
    		// 批量更新
	        $res = Role::where('role_id',$id)
					          ->update($params);
	        if($res){
	        	return ajaxReturn(0,'','success');
	        }else{
	        	return ajaxReturn(2,'','修改失败，请重试');
	        }
    		
    	}else{
    		return ajaxReturn(1,'','无法获取职位信息，请联系管理员');
    	}  
	}
	
}
