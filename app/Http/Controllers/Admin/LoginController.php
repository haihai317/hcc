<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
	* @name adminLogin
	* @param 用户名、密码
	* @return 返回用户基本信息
	* @discription 后台员工登录
	*/
	public function adminLogin(Request $request){
		$em_name = $request->input('em_name','');
		$em_password = $request->input('em_password','');

		$user = DB::table('employee')->where('em_tel','=',$em_name)
									 ->orWhere('em_name','=',$em_name)
									 ->get();
		if($user->isEmpty()){
			return ajaxReturn(2,'','抱歉，该用户不存在！');
		}else{
			foreach ($user as $value) {
				// 检验密码是否正确
				if(Hash::check($em_password, $value->em_password)){
					// 将登录信息存入session
					session(['admin' => $value]);
					return ajaxReturn(0,$value,'success');
				}else{
					return ajaxReturn(1,'','您输入的的密码有误，请重新输入！');
				}
			}
		}
			
	}



	/**
	* @name adminLogout
	* @param 
	* @return 返回是否操作成功
	* @discription 后台员工登出
	*/
	public function adminLogout(Request $request){
		$value = $request->session()->pull('admin', 'default');
		return ajaxReturn(0,'','success');
	}
}
