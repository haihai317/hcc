<?php

namespace App\Http\Controllers\Application;

use App\Model\Bill;
use App\Model\Digrecord;
use App\Model\Group;
use App\Model\User;
use App\Model\Wallet;
use function foo\func;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * app首页
     * @param Request $request
     */
    public function appHome(Request $request) {
        $userId = $request->get('user_id');
        //查询直推成组
        $user = User::find($userId);
        $groups = [];
        $Itc = [];
        if ($user->user_level != 0) {
            $groups = $user->userHasGroups()
                ->with(['gpOneUser','gpTwoUser','gpThreeUser','gpForeUser'])
                ->orderBy('create_time','desc')->get();
            //查询矿机
            $Itc = $user->userHasItc;
        }
        //查询收益排行
        $tops = User::where('user_level', '<>', 0)
            ->with(['userHasWallet'=>function($query) {
                $query->select('user_id','total_income');
            }])->whereHas('userHasWallet',function($q) {
                $q->orderBy('total_income','desc');
            })->get(['user_id','user_headpic','user_name','user_level']);
        //检测今日是否挖矿 TODO：
        $digTedayCount = Digrecord::where('user_id',$userId)->whereDate('dig_time',date('Y-m-d'))->count();
        $is_dig = $digTedayCount != 0 ? false : true;   //当前是否可挖矿
        return ajaxReturn(0, compact('groups','Itc','tops','is_dig'));
    }


    /**
     * 成组充能
     */
    public function groupAll(Request $request) {
        $user_id = $request->get('user_id');
        $group_id = $request->input('group_id',null);
        if (!$group_id) return ajaxReturn(-1,'','参数有空');
        $group = Group::find($group_id);
        //return ajaxReturn(0, $group);
        if ($group->user_id != $user_id) return ajaxReturn(-2,'','非法操作');
        if ($group->status == 0) return ajaxReturn(-3,'','未成组不能充能');
        if ($group->status == 2) return ajaxReturn(-4,'','不能重复充能');
        $group->status = 2;
        $group->save();
        //获取组成员购买的vip
        $ztUserLv = $group->gpOneUser->userHasGift->hcc;
        $user2Lv = $group->gpTwoUser->userHasGift->hcc;
        $user3Lv = $group->gpThreeUser->userHasGift->hcc;
        $user4Lv = $group->gpForeUser->userHasGift->hcc;
        $awardHcc = min($ztUserLv,$user2Lv,$user3Lv,$user4Lv);
        $groupRatio = Config::where('key','group_ratio')->value('value');     //获取成组奖励比例
        $awardVal = $awardHcc * $groupRatio;
        Wallet::walletInCome($user_id, $awardVal);
        //添加流水
        Bill::create(['user_id'=>$user_id,'devote_user_id'=>$user_id,'hcc_price'=>$awardVal,'cate'=>3,'remark'=>'成组充能奖励'.$awardVal.'HCC']);
        //查询下个记录
        $nextGroup = $mUser->userHasGroups()->with(['gpOneUser','gpTwoUser','gpThreeUser','gpForeUser'])
            ->whereIn('status',[0,1])->orderBy('status','desc')
            ->first();
        return ajaxReturn(0, $nextGroup);
    }

    /**
     * 会员挖矿
     */
    public function userDigItc(Request $request) {
        $user_id = $request->get('user_id');
        $user = User::find($user_id);
        $Itc = $user->userHasItc;
        //查询用户最新挖矿记录
        $digs = $user->userHasDigs()->orderBy('dig_time','desc')->first();
        if (!empty($digs)) {
            $lastDate = date('Y-m-d',strtotime($digs->dig_time));
            if ($lastDate == date('Y-m-d'))
                return ajaxReturn(-2,'','今日已挖过矿');
        }
        //开始挖矿
        //获取可挖hcc
        $wallet = $user->userHasWallet;
        $lockHcc = $wallet->lock_fee;       //矿机hcc总量
        $digedHcc = $lockHcc * $Itc->speed; //本次能挖hcc
        $blcItcHcc = $lockHcc - $digedHcc;  //矿机剩余hcc
        $Itc->lock_fee = $blcItcHcc;
        $Itc->save();
        //账户变更
        $wallet->lively_fee = $wallet->lively_fee + $digedHcc;
        $wallet->lock_fee = $blcItcHcc;
        $wallet->total_income = $wallet->total_income + $digedHcc;
        $wallet->save();
        //添加流水
        Bill::create(['user_id'=>$user_id,'devote_user_id'=>$user_id,'hcc_price'=>$digedHcc,'cate'=>4,'remark'=>'今日矿机产出'.$digedHcc.'HCC']);
        //添加挖矿记录
        Digrecord::create(['user_id'=>$user_id,'dig_hcc'=>$digedHcc]);
        return ajaxReturn();
    }
}
