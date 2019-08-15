<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\Employee;
use App\Model\Company;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    
	/**
	* @name createNewEm
	* @param 定义函数或者方法的参数信息
	* @return 定义函数或者方法的返回信息
	* @discription 生成新的员工
	*/
	public function createNewEm(Request $request){
		// 整理插入数据
		$params = [];
		$params = Input::except(['headers','em_de_value','user_id','power_str']);
		$em_de_value  = $request->input('em_de_value',[]);
		// 参数有空
		if($em_de_value == [] ){
			return  ajaxReturn(1,'','请将表单填写完整');
		}

		$params['em_co_id'] = $em_de_value[0];
		$params['em_de_id'] = $em_de_value[1];
		// 加密密码
		$params['em_password'] = Hash::make($params['em_password']);
		$emid = Employee::create($params);
		return ajaxReturn(0,$emid,'success');
	}


	/**
	* @name showEmList
	* @param name 、 page
	* @return 相应条件下的员工列表和总数
	* @discription 查询员工列表
	*/
	public function showEmList(Request $request){
		// dd($request);
		$params = Input::all();
    	$page = $params['page'];
    	$offset = ($page - 1)*10 ;

		// 书写查询条件
        $where = '1=1';

        if($params['name'] != ''){
            $where .= " and employee.em_name like '%".$params['name']."%' ";
        }
        if(!empty($params['em_de_value'])){
            $where .=" and employee.em_co_id = ".$params['em_de_value'][0]." and employee.em_de_id = {$params['em_de_value'][1]}  ";
        }

        $ems = Employee::join('company','company.co_id','=','employee.em_co_id')
                          ->join('department','department.de_id','=','employee.em_de_id')
                          ->join('role','role.role_id','=','employee.em_role_id')
                          ->select('employee.*','company.co_name','department.de_name','role.role_name')
                          ->whereRaw($where)
                          ->orderBy('em_id','asc')
                          ->offset($offset)
                          ->limit(10)
                          ->get();

        $count = Employee::whereRaw($where)->select('em_id')->count();

        $res['ems'] = $ems;
        $res['count'] = $count;

		return ajaxReturn(0,$res,'success');
	}




	/**
	* @name getDeAndRole
	* @param 
	* @return 所有职位名称信息和所有公司下的相关部门信息
	* @discription 查询现有职位和部门
	*/

	public function getDeAndRole(){
		// 查询所有的职位
		$roles = DB::table('role')->where('role_status',1)
								  ->select('role_id as value','role_name as label')
								  ->get();

		$res['roles'] = $roles;

		// 所有部门和他所属的公司
		$co = Company::select('co_id','co_name')->get();

		$co = json_decode(json_encode($co),true);

		foreach ($co as $key => $value) {
			$des = Company::find($value['co_id'])->departments;
			$res['des'][$key] = ['value'=>$value['co_id'],'label'=>$value['co_name'],'children'=>$des];
		}


		return ajaxReturn(0,$res,'success');
	}





	// 修改员工信息
	public function editEmMes(Request $request){
		$id = $request->input('em_id',0);
    	if($id > 0){
    		// 获取更新参数
    		$params = [];
			$params = Input::except(['headers','em_de_value','user_id','power_str']);
			$params['em_updatetime'] = date('Y-m-d H:i:s');
			$em_de_value  = $request->input('em_de_value',[]);
			// 参数有空
			if($em_de_value == [] ){
				return  ajaxReturn(1,'','请将表单填写完整');
			}

			$params['em_co_id'] = $em_de_value[0];
			$params['em_de_id'] = $em_de_value[1];
    		// 批量更新
	        $res = Employee::where('em_id',$id)
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


	// 删除员工信息
	public function deleteEm(Request $request){
    	$id = $request->input('id',0);

    	if($id > 0){
			$res = Employee::destroy($id);
    		// 其他写法：$res = Compony::where('co_id', $id)->delete();
    		if($res){
	        	return ajaxReturn(0,'','success');
	        }else{
	        	return ajaxReturn(3,'','删除失败，请重试');
	        }
			
    	}else{
    		return ajaxReturn(1,'','无法获取部门信息，请联系管理员');
    	}
    }
}
