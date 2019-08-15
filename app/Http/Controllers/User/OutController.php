<?php

namespace App\Http\Controllers\User;

use App\Model\Bill;
use App\Model\Config;
use App\Model\Gift;
use App\Model\Group;
use App\Model\Partner;
use App\Model\User;
use App\Model\UserItc;
use App\Model\VipRemark;
use App\Model\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OutController extends Controller
{
    /**
     * 用户购买vip
     */
    public function buyVip(Request $request) {

        if ($request->isMethod('post')) {
            $gift_id = $request->input('vip_type',null);
            $phone = $request->input('phone',null);
            if (!$gift_id || !$phone) {
                return showMsg('','参数有误', 'error');
            }
            //用户查询
            $user = User::where('user_phone',$phone)->first();
            if (empty($user)) {
                return showMsg('','您输入的手机号未注册苍耳会','error');
            }
            if ($user->user_level != 0) {
                return showMsg('','您已经是苍耳会VIP会员','error');
            }
            DB::beginTransaction();
            $bills = [];    //收益流水记录
            $gift = Gift::find($gift_id);
            //储存订单
            $qUser = new VipRemark();
            $qUser->user_id = $user->user_id;
            $qUser->gift_id = $gift_id;
            $qUser->price = $gift->price;
            $qUser->order_no = $user->user_id.'-'.date('YmdHis').rand(10000,99999);
            $r1 = $qUser->save();
            //TODO: 以下逻辑均为支付成功后回调执行
            //1、账户完善
            $wallet = $user->userHasWallet;
            $wallet->user_id = $user->user_id;
            $wallet->total_fee = $gift->hcc;
            $wallet->lock_fee = $gift->hcc;
            $wallet->hcc_titck = $gift->num;
            $wallet->save();
            $bills[] = ['user_id'=>$user->user_id,'devote_user_id'=>$user->user_id,'hcc_price'=>$gift->hcc,'cate'=>8,'remark'=>"您购买VIP礼包收到了{$gift->hcc}HCC奖励"];
            $user->user_level = $gift->level;
            $user->user_gift_id = $gift_id;
            $r2 = $user->save();
            //2、生成用户矿机
            $itc = new UserItc();
            $itc->user_id = $user->user_id;
            $itc->lock_fee = $gift->hcc;
            $itc->speed = Config::where('key','itc_speed')->value('value');
            $r3 = $itc->save();
            //购买礼包分销计算 1成组 2直推 管理 3城市管理员
            //1、成组
            //查询直接上级用户
            $ztParent = $user->hasParent;
            //查询成组所有者
            $groupUser = $ztParent->hasParent;
            if (!$groupUser) {  //说明直接上级为顶级
            } else {    //非顶级可成组
                $group = Group::where('zt_user_id',$ztParent->user_id)->where('status',0)->first();
                if (!empty($group)) {
                    if (!$group->member1) $group->member1 = $user->user_id;
                    else {
                        if (!$group->member2) $group->member2 = $user->user_id;
                        else {
                            if (!$group->member3) {
                                $group->member3 = $user->user_id;
                                $group->group_finish = date('Y-m-d H:i:s');
                                $group->status = 1;
                            }
                        }
                    }
                    $r4 = $group->save();
                } else {
                    $r4 = Group::create(['user_id'=>$groupUser->user_id,'zt_user_id'=>$ztParent->user_id,'member1'=>$user->user_id]);
                }
            }
            //2、直推奖励及管理奖励 公式 p = a * b ^ (n-1)
            //计算直推
            $ratio = $gift->zt_ratio;   //此礼包直推收益比例
            $hccPrice = $gift->hcc;
            //计算一级奖励 直推
            $reward_lv1 = $this->teamReward($ratio,$hccPrice,1);
            Wallet::walletInCome($ztParent->user_id, $reward_lv1);
            $bills[] = ['user_id'=>$ztParent->user_id,'devote_user_id'=>$user->user_id,'hcc_price'=>$reward_lv1,'cate'=>1,'remark'=>"您收到了{$reward_lv1}HCC奖励，其中".($reward_lv1/2).'存入矿机。'];
            //计算二级奖励
            if (!empty($groupUser)) {
                $reward_lv2 = $this->teamReward($ratio,$hccPrice,2);
                Wallet::walletInCome($groupUser->user_id, $reward_lv2);
                $bills[] = ['user_id'=>$groupUser->user_id,'devote_user_id'=>$user->user_id,'hcc_price'=>$reward_lv2,'cate'=>2,'remark'=>"您收到了{$reward_lv2}HCC奖励，其中".($reward_lv2/2).'存入矿机。'];
                //计算三级奖励
                $userLv3 = $groupUser->hasParent;
                if (!empty($userLv3)) {
                    $reward_lv3 = $this->teamReward($ratio,$hccPrice,3);
                    Wallet::walletInCome($userLv3->user_id, $reward_lv3);
                    $bills[] = ['user_id'=>$userLv3->user_id,'devote_user_id'=>$user->user_id,'hcc_price'=>$reward_lv3,'cate'=>2,'remark'=>"您收到了{$reward_lv3}HCC奖励，其中".($reward_lv3/2).'存入矿机。'];
                    //计算四级奖励
                    $userLv4 = $userLv3->hasParent;
                    if (!empty($userLv4)) {
                        $reward_lv4 = $this->teamReward($ratio,$hccPrice,4);
                        Wallet::walletInCome($userLv4->user_id, $reward_lv4);
                        $bills[] = ['user_id'=>$userLv4->user_id,'devote_user_id'=>$user->user_id,'hcc_price'=>$reward_lv4,'cate'=>2,'remark'=>"您收到了{$reward_lv4}HCC奖励，其中".($reward_lv4/2).'存入矿机。'];
                        //计算五级收益
                        $userLv5 = $userLv4->hasParent;
                        if (!empty($userLv5)) {
                            $reward_lv5 = $this->teamReward($ratio,$hccPrice,5);
                            Wallet::walletInCome($userLv5->user_id, $reward_lv5);
                            $bills[] = ['user_id'=>$userLv5->user_id,'devote_user_id'=>$user->user_id,'hcc_price'=>$reward_lv5,'cate'=>2,'remark'=>"您收到了{$reward_lv5}HCC奖励，其中".($reward_lv5/2).'存入矿机。'];
                        }
                    }
                }
            }
            DB::table('bill')->insert($bills);

            //城市合伙人佣金计算
            //查询区级城市合伙人
            $parter = Partner::getPartner($user->province,$user->city,$user->area);
            if ($parter) {
                Wallet::parterIncome($parter->user_id, $hccPrice, $user->user_id);
            }
            if ($r1 && $r2 && $r3 && $r4) {
                DB::commit();
                return showMsg(url('vip/buy'),'您已成为苍耳会VIP'.$gift->level.'会员');
            } else {
                DB::rollback();
                echo 'error'; exit;
            }
        }
        //查询vip类型
        $vips = Gift::get();
        return view('vip_pay',compact('vips'));
    }
}
