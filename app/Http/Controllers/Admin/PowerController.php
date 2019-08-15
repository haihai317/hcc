<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\Power;
use Illuminate\Support\Facades\Input;

class PowerController extends Controller
{
    
    
    /**
	* @name showPowerList
	* @param none
	* @return 所有的权限信息
	* @discription 查看现有权限列表
	*/

	public function showPowerList(Request $request){
        // 查询所有的模块权限
        $power = Power::where('power_type',1)
        			->orderBy('power_sort','asc')
        			->get();
        $power = json_decode(json_encode($power),true);

        // 查询所有的模块权限下的节点权限，并有序插入到数组中
        $count = 0;
        foreach ($power as $key => $value) {
        	$power_son = Power::where('power_parent_id',$value['power_id'])
        						->orderBy('power_sort','asc')
        						->get();
        	$power_son = json_decode(json_encode($power_son),true);
        	// var_dump($power_son);
        	// 将查到的节点权限插入数组中
        	if($power_son != []){
        		array_splice($power,$key+1+$count,0,$power_son);
        		$count += count($power_son);
        	}	
        }

		return ajaxReturn(0,$power,'success');
	}


    
    /**
	* @name makeAddPower
	* @param name/type/status/remark/sort/parent_id/url;
	* @return 是否创建成功
	* @discription 新建一个权限
	*/

	public function makeAddPower(Request $request){
		$arr  = Input::except(['headers','user_id','power_str']);

		if($arr['power_type'] == 2 && $arr['power_parent_id'] == null){
			return ajaxReturn(2,'','未选择指定的父权限');
		}
		
		$power = Power::create($arr);

		if($power){
			return ajaxReturn(0,$power,'success');
		}else{
			return ajaxReturn(1,'','创建权限失败');
		}
	}

	/**
	* @name getAllParents
	* @param 
	* @return 所有的父级权限
	* @discription 查询获取所有的父级权限
	*/
	public function getAllParents(){

		// 查询所有的职位
		$powers = DB::table('power')->where('power_type',1)
								  ->select('power_id as value','power_name as label')
								  ->get();

		return ajaxReturn(0,$powers,'success');
	}
	

	/**
	* @name getRolepowers
	* @param role_id
	* @return 权限树结构
	* @discription 为职位授权获取权限树结构
	*/
	public function getRolepowers(Request $request){

		// 查找用户现有的权限
		$role_id = $request->input('role_id',0);
		// var_dump($role_id);
		if($role_id > 0){
			$hasId = DB::table('role_power')->where('bind_role_id',$role_id)
								   ->select('bind_power_id')
								   ->get();

			$hasId = json_decode(json_encode($hasId),true);
			// 整理用户现有权限
			if($hasId != []){
				for ($i=0; $i < count($hasId) ; $i++) { 
					$idArr[$i] = $hasId[$i]['bind_power_id'];
				}
			}else{
				$idArr = [];
			}
				

		}else{
			return ajaxReturn(2,'','无法获取用户信息');
		}

		// 查询所有的父职位
		$powers = Power::where('power_type',1)
					  ->select('power_id as id','power_name as label')
					  ->orderBy('power_sort','asc')
					  ->get();

		// 查询并整理父权限下的子权限
		$powers = json_decode(json_encode($powers),true);
		foreach ($powers as $key => &$value) {
			$children =  Power::where([["power_type",'=',2],['power_parent_id','=',$value['id']]])
					  		   ->select('power_id as id','power_name as label')
					  		   ->orderBy('power_sort','asc')
					           ->get();
			$value['children'] = json_decode(json_encode($children),true);
		}

		$res['powers'] = $powers;
		$res['hasId'] = $idArr;

		return ajaxReturn(0,$res,'success');
			
	}


	/**
	* @name submitRolePower
	* @param role_id/treeNode
	* @return 权限树结构
	* @discription 提交权限树结构
	*/
	public function submitRolePower(Request $request){
		$tree = $request->input('treeNode',[]);
		$id = $request->input('role_id',0);
		if($tree != [] && Intval($id) > 0){
			DB::table('role_power')->where('bind_role_id',$id)->delete();
			$arr = [];
			for ($i=0; $i < count($tree) ; $i++) { 
				$arr[$i] = ['bind_role_id'=>$id,'bind_power_id'=>$tree[$i]];
			}
			$num = DB::table('role_power')->insert($arr);
			if($num){
				return ajaxReturn(0,$num,'success');
			}else{
				return ajaxReturn(0,'','添加权限失败，请刷新页面重试！');
			}
			
		}else{
			return ajaxReturn(1,'','操作失败，请刷新页面重试！');
		}
	}






	/**
	* @name userNodes
	* @param em_id
	* @return 获取员工的权限集合
	* @discription 获取员工的的权限集合
	*/
	public function userNodes(Request $request){
		$em_id = $request->input('em_id',0);
		if($em_id==0) return ajaxReturn(1,[],'无法获取用户信息');

		// 查询用户的所有可用二级权限
		$secondNodes = DB::table('power')
					   ->join('role_power','role_power.bind_power_id','=','power.power_id')
					   ->join('role','role.role_id','=','role_power.bind_role_id')
					   ->join('employee','employee.em_role_id','=','role.role_id')
					   ->where([['.power.power_type','=',2],['employee.em_id','=',$em_id]])
					   ->select('power.*')
					   ->orderBy('power_sort','asc')
					   ->get();


		// 查询所有的一级权限
		$firstNodes = DB::table('power')->where('power_type',1)->orderBy('power_sort','asc')->get();

		$firstNodes = json_decode(json_encode($firstNodes),true);
		$secondNodes = json_decode(json_encode($secondNodes),true);

		$arr = [];
		for ($i=0; $i < count($firstNodes) ; $i++) {  
			$firstNodes[$i]['children'] = [];
			for ($j=0; $j < count($secondNodes) ; $j++) {
				if($secondNodes[$j]['power_parent_id'] == $firstNodes[$i]['power_id']){
					array_push($firstNodes[$i]['children'],$secondNodes[$j]);
				}
			}
			if(count($firstNodes[$i]['children']) > 0){
				array_push($arr,$firstNodes[$i]);
			}
		}

		return ajaxReturn(0,$arr,'success');

	}
	



	// 删除指定的power
	public function removePower(Request $request){
		$id = $request->input('id',0);
		if($id > 0){
    		// 查询该部门下有无员工
    		$powers = Power::where('power_parent_id',$id)->select('power_id')->first();
    		if(empty($powers)){
    			$ems = DB::table('role_power')->where('bind_power_id',$id)->first();
				if(empty($ems)){
	    			// 删除该职位
	    			$res = Power::destroy($id);
		    		if($res){
			        	return ajaxReturn(0,'','success');
			        }else{
			        	return ajaxReturn(3,'','删除失败，请重试');
			        }
	    		}else{
	    			return ajaxReturn(2,'','该权限尚被某职位持有，无法删除');
	    		}	
    		}else{
    			return ajaxReturn(4,'','该权限下有子权限，无法删除');
    		}
	    			
    	}else{
    		return ajaxReturn(1,'','无法获取权限信息，请联系管理员');
    	}
	}



	// 修改权限的基本信息
	public function makeEdit(Request $request){
		$id = $request->input('power_id',0);
    	if($id > 0){
    		// 获取更新参数
    		$params = Input::except(['headers','power_id','user_id','power_str']);
    		$params['power_updatetime'] = date('Y-m-d H:i:s');
    		// 批量更新
	        $res = Power::where('power_id',$id)
					          ->update($params);
	        if($res){
	        	return ajaxReturn(0,'','success');
	        }else{
	        	return ajaxReturn(2,'','修改失败，请重试');
	        }
    		
    	}else{
    		return ajaxReturn(1,'','无法获取权限信息，请联系管理员');
    	}    
	}
}
