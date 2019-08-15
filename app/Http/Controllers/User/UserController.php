<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;

class UserController extends Controller
{

	/**
	 *用户注册
	 * @param [json] $[user_name/user_password] [用户名、密码]
	 */
    public function makeRegister(Request $request){
    	$arr = $request->except(["apitimes","s"]);

      //找到该用户预存的验证码信息，并比对过期时间
      $user = User::where("user_verify_code",$arr['user_verify_code'])->first();
      if(empty($user)){
        return ajaxReturn(4,'','请输入正确的验证码！');
      }
      $current = time();
      if($current > intval($user->user_verify_timeout)){
        return ajaxReturn(5,'','验证码已过期，请重新获取！');
      }

    	if(strlen($arr["user_password"]) > 5 ){

    		// token值
   			$arr['user_access_token'] = $arr['apikey'];
   			unset($arr['apikey']);

  			// 加密密码
  			$arr['user_password'] = Hash::make($arr['user_password']);
        // 注册时间
  			$arr['user_register_time'] = date("Y-m-d H:i:s");

        // 根据邀请码查找父级id
        $parent = User::where('user_recommend',$arr['recommend'])->first();
        if(!empty($parent)){
          $arr['user_parent_id'] = $parent->user_id;
          unset($arr['recommend']);
        }else{
          return ajaxReturn(3,'','该邀请码不存在！');
        }

        // 生成用户的邀请码
        $arr['user_recommend'] = $this->createRecommend();

  			// 当前登陆时间
  			$arr['user_lastlogin_time'] = time();

  			// 创建用户
  			$res = User::where('user_id',$user->user_id)->update($arr);
  			if($res){
  				return ajaxReturn(0,$arr['user_access_token'],'用户注册成功');
  			}else{
  				return ajaxReturn(2,"","用户创建失败,请重启APP后尝试！");
  			}
    		
    	}else{
    		return ajaxReturn(1,'','用户密码不能小于6位！');
    	}
    }





    /**
     * 用户单点登录
     * @param [json] $[name/password] [用户名、密码]
     */
    public function getLogin(Request $request){
    	$arr = $request->except(['apitimes']);
    	$user = User::where('user_phone','=',$arr['name'])
					 ->orWhere('user_name','=',$arr['name'])
					 ->first();
  		$user = json_decode(json_encode($user),true);
  		
  		if(!isset($user)){
  			return ajaxReturn(2,'','抱歉，该用户不存在！');
  		}else{
  				
  			// 检验密码是否正确
  			if(Hash::check($arr['password'], $user['user_password'])){
  				
  				// 更新token和最近登录时间
  				$up = User::where("user_id",$user['user_id'])
  							->update(['user_access_token' => $arr['apikey'],'user_lastlogin_time'=>time()]);
  				if(isset($up)){
            $user["user_access_token"] = $arr['apikey'];
  					return ajaxReturn(0,$user,'success');
  				}else{
  					return ajaxReturn(3,'','更新身份保存失败');
  				}

  			}else{
  				return ajaxReturn(1,'','您输入的的密码有误，请重新输入！');
  			}
  		}
			
    }
    




    /**
     * 修改密码
     * @param [json] $[old_password/new_password] [原密码、新密码]
     */
    public function editPassword(Request $request){
    	$arr = $request->only(['old_password','new_password','token']);
    	
    	// 查找当前用户
    	$user = User::where("user_access_token",$arr['token'])->first();
    	$user = json_decode(json_encode($user),true);
  		if(!isset($user)){
  			return ajaxReturn(2,'','您的账户已在其他地方登陆，请重新登录！');
  		}else{
  				
  			// 检验密码是否正确
  			if(Hash::check($arr['old_password'], $user['user_password'])){
  				
  				// 执行密码更换
  				$new = Hash::make($arr['new_password']);
  				$up = User::where("user_id",$user['user_id'])
  							->update(['user_password' => $new]);
  				if(isset($up)){
  					return ajaxReturn(0,'','success');
  				}else{
  					return ajaxReturn(3,'','更新密码失败');
  				}

  			}else{
  				return ajaxReturn(1,'','您输入的原密码有误，请重新输入！');
  			}
  		}
    	
    }





    
    /**
     * 重置密码（忘记）
     * @param [json] $[name] [description]
     */
    public function rewritePassword(Request $request){
    	$arr = $request->only(['user_phone','user_password',"user_verify_code","apikey"]);
      
      // 查找当前用户
      $user = User::where("user_phone",$arr['user_phone'])->first();
      if(!isset($user)){
        return ajaxReturn(2,'','该用户不存在');
      }

      // 验证码过期时间
      $current = time();
      if($current > intval($user->user_verify_timeout)){
        return ajaxReturn(5,'','验证码已过期，请重新获取！');
      }

      // 执行密码重置
      $new = Hash::make($arr['user_password']);
      $up = User::where("user_id",$user->user_id)
            ->update(['user_password' => $new,"user_access_token"=>$arr['apikey']]);
      if(isset($up)){
        return ajaxReturn(0,$arr['apikey'],'success');
      }else{
        return ajaxReturn(3,'','更新密码失败');
      }   
    }






    /**
     * 个人信息完善
     * @param [json] $[user_name/user_phone/user_headpic/user_sex] [description]
     */
    public function editUserBaseMessage(Request $request){
    	$arr['user_name'] = $request->input('user_name','');
    	$arr['user_phone'] = $request->input('user_phone','');
    	$arr['user_headpic'] = $request->input('user_headpic','');
    	$arr['user_sex'] = $request->input('user_sex','');
    	$token = $request->input('token','');
    	$res = User::where("user_access_token",$token)
    		  ->update($arr);
    	if($res){
    		return ajaxReturn(0,'','sucess');
    	}else{
    		return ajaxReturn(1,'','用户信息修改失败');
    	}
    }
    








    /**
     * 后台展示会员列表
     * @param  $[name/page] 
     */
    public function showUserList(Request $request){

    	$params = Input::all();
    	$page = $params['page'];
    	$offset = ($page - 1)*10 ;

		  // 书写查询条件
      $where = '1=1';

      if($params['name'] != ''){
          $where .= " and shop_user.user_name like '%".$params['name']."%' ";
      }

      $ems = User::whereRaw($where)
                ->orderBy('user_id','asc')
                ->offset($offset)
                ->limit(10)
                ->get();

      $count = User::whereRaw($where)->select('user_id')->count();

      $res['users'] = $ems;
      $res['total'] = $count;

		  return ajaxReturn(0,$res,'success');
    }






    /**
     * 模拟发送短信验证码
     */
    public function dealSMS(Request $request){
      // 获取手机号及类型（1为注册，2为忘记密码）
      $phone = $request->input("phone",null);
      $type = $request->input("type",1);
      if($phone){
        $ifMobile = isMobile($phone);
        if($ifMobile){
          // 检测是否已有手机号
          $hasuser = User::where("user_phone",$phone)->first();
          if(!empty($hasuser)){
            // 该用户已注册过,不能重复注册
            if($hasuser->user_register_time != null && $type == 1){
              return ajaxReturn(5,"","该手机号已被注册！");  
            }else if($hasuser->user_register_time == null && $type == 2){
              return ajaxReturn(6,"","您尚未注册!");  
            }else{
              //生成6位验证码
              $verifyCode = createVerify(6);
              $text = '【苍耳会】：您的验证码是：'.$verifyCode;

              try {
                $qx = qxMessage($phone, $text);
                if ($qx['code'] == 0) {
                    //发送成功，纪录数据
                    $user = array();
                    $user["user_verify_code"] = $verifyCode;
                    $user["user_verify_timeout"] =  strtotime("+2 minute");
                    // 更新用户信息
                    User::where("user_id",$hasuser->user_id)->update($user);
                    return ajaxReturn(0, '', 'success');
                } else {
                    return ajaxReturn(3, '', '发送失败，请重试');
                }
              } catch (\Exception $e) {
                  \Log::debug('DEBUG: '.$e.'。IP:'.$_SERVER['REMOTE_ADDR']);
                  return ajaxReturn(4, '', '服务器错误');
              }
            }
            
          }else{
            if($type == 2){
              return ajaxReturn(7,"","该手机号尚未注册");
            }
            //生成6位验证码
            $verifyCode = createVerify(6);
            $text = '【苍耳会】：您的验证码是：'.$verifyCode;

            try {
              $qx = qxMessage($phone, $text);
              if ($qx['code'] == 0) {
                  //发送成功，纪录数据
                  $user = array();
                  $user["user_verify_code"] = $verifyCode;
                  $user["user_phone"] = $phone;
                  $user["user_verify_timeout"] =  strtotime("+2 minute");
                  // 保存用户信息
                  User::create($user);
                  return ajaxReturn(0, '', 'success');
              } else {
                  return ajaxReturn(3, '', '发送失败，请重试');
              }
            } catch (\Exception $e) {
                \Log::debug('DEBUG: '.$e.'。IP:'.$_SERVER['REMOTE_ADDR']);
                return ajaxReturn(4, '', '服务器错误');
            }
          }
            
        }else{
          return ajaxReturn(2,"","该手机号码格式不正确！");
        }
        
      }else{
        return ajaxReturn(1,'','系统无法接收到您的手机号码');
      }
      
    }




    /**
     * 生成用户唯一的邀请码
     */
    public function createRecommend(){
      $recommend = getRandChar(8);
      $ifrec = User::where('user_recommend',$recommend)->first();
      if(!empty($ifrec)){
        $this->createRecommend();
      }else{
        return $recommend;
      }
    }



}
