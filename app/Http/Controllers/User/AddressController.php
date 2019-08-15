<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\Address;

class AddressController extends Controller
{
    


    /**
     * 新建收货地址
     * @param [json] $[name] [description]
     */
    public function makeNewAddr(Request $request){
    	$user_id = $request->get('user_id');

    	if($user_id > 0){
    		// 是否设置为默认
	    	$isDefault = 1;
	    	$res = Address::where('addr_user_id',$user_id)->count();
	    	if($res > 0){
	    		$isDefault = 2;
	    	}

	    	// 整理要插入的数组
	    	$arr['addr_user_id'] = $user_id;
	    	$arr['addr_realname'] = $request->input('addr_realname','');
	    	$arr['addr_mobile'] = $request->input('addr_mobile','');
	    	$arr['addr_province'] = $request->input('addr_province','');
	    	$arr['addr_city'] = $request->input('addr_city','');
	    	$arr['addr_area'] = $request->input('addr_area','');
	    	$arr['addr_address'] = $request->input('addr_address','');
            $arr['addr_isdefault'] = $isDefault;
	    	$arr['addr_status'] = 1;
	    	$arr['addr_createtime'] = date("Y-m-d H:i:s");

	    	// 执行插入
	    	$data = Address::create($arr);
	    	return ajaxReturn(0,'','success');
    	}else{
    		return ajaxReturn(1,'','未找到相关用户');
    	}
    }




    /**
     * 修改收货地址
     * @param  $[realname/mobile/province/city/address] 
     */
    public function editAddress(Request $request){
		$addr_id = $request->input('addr_id');

    	// 整理要插入的数组
    	$arr['addr_realname'] = $request->input('realname','');
    	$arr['addr_mobile'] = $request->input('mobile','');
    	$arr['addr_province'] = $request->input('province','');
    	$arr['addr_city'] = $request->input('city','');
    	$arr['addr_area'] = $request->input('area','');
    	$arr['addr_address'] = $request->input('address','');

    	// 执行插入
    	$data = Address::where("addr_id",$addr_id)->update($arr);
    	if($data){
    		return ajaxReturn(0,'','success');
    	}else{
    		return ajaxReturn(1,'','修改收货地址失败');
    	}
    }





    /**
     * 设为默认地址
     * @param  $[addr_id] 
     */
    public function defaultChange(Request $request){
    	$addr_id = $request->input('addr_id');
    	$user_id = $request->get('user_id');

    	if($addr_id > 0){
	    	$res = Address::where('addr_id',$addr_id)->first();
	    	if($res->addr_isdefault == 1){
	    		return ajaxReturn(2,'','该地址已经是默认地址了');
	    	}else if($res->addr_isdefault == 2){

	    		// 将原默认地址设为非默认地址
	    		$oldAddr = Address::where([["addr_user_id",'=',$user_id],['addr_isdefault','=',1]])->update(['addr_isdefault'=>2]);
	    		// 将该地址设为默认地址
	    		Address::where('addr_id',$addr_id)->update(["addr_isdefault"=>1]);
	    		return ajaxReturn(0,'','success');
	    	}
    	}else{
    		return ajaxReturn(1,'','未找到相关收货地址');
    	}
    }







    /**
     * 删除收货地址
     */
    public function deleteAddres(Request $request){
    	$addr_id = $request->input('addr_id',0);
    	$user_id = $request->get('user_id',0);
    	if($addr_id > 0){
    		$res = Address::where("addr_id",$addr_id)->first();
    		if($res->addr_isdefault == 1){
    			// 如果该地址是默认地址，则删除后修改其他地址为默认地址
    			$other = Address::where([["addr_user_id",'=',$user_id],["addr_id","<>",$addr_id],["addr_status","=",1]])->first();
    			if(isset($other)){
    				Address::where('addr_id',$other->addr_id)->update(["addr_isdefault"=>1]);
    			}

    			// 删除该地址
    			Address::where("addr_id",$addr_id)->update(["addr_isdefault"=>2,"addr_status"=>2]);
    			return ajaxReturn(0,$other,'删除成功');
    		}else{
                Address::where("addr_id",$res->addr_id)->update(["addr_isdefault"=>2,"addr_status"=>2]);
                return ajaxReturn(0,$res,'删除成功');
            }
    	}else{
    		return ajaxReturn(1,'','无法确定收货地址！');
    	}
    }








    /**
     * 收货地址列表
     * @param  $[user_id] 
     */
    public function showAddressList(Request $request){
    	$user_id = $request->get('user_id');
    	if($user_id > 0){
    		// 查找
    		$res = Address::where([["addr_user_id",'=',$user_id],["addr_status","=",1]])->get();
    		return ajaxReturn(0,$res,'success');
    		
    	}else{
    		return ajaxReturn(1,'','无法确定用户');
    	}
    }






    /**
     * 展示商家的收货地址
     */
    public function backAddrList(){
        $res = Address::where([["addr_user_id",'=',0],["addr_status","=",1]])->get();
        for ($i=0; $i < count($res) ; $i++) { 
            $res[$i]["value"] = $res[$i]['addr_id'];
            $res[$i]["label"] = $res[$i]['addr_province'].' '.$res[$i]['addr_city'].' '.$res[$i]['addr_area'].' '.$res[$i]['addr_address'].' '.$res[$i]['addr_realname'].' '.$res[$i]['addr_mobile'];
        }
        return ajaxReturn(0,$res,"success");
    }







    /**
     * 新增商家的收货地址
     */
    public function saveBackAddr(Request $request){
        $addrForm = $request->input("addrForm",[]);
        $addrForm["addr_user_id"] = 0;
        $addrForm["addr_isdefault"] = 0;
        $addrForm["addr_status"] = 1;
        $addrForm["addr_createtime"] = date("Y-m-d H:i:s");

        // 保存新的寄回地址
        $res = Address::create($addrForm);

        return ajaxReturn(0,$res,"success");
    }

}
