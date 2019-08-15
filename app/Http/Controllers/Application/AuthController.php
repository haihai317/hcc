<?php

namespace App\Http\Controllers\Application;

use App\Model\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * 个人中心
     */
    public function mine(Request $request) {
        $user_id = $request->get('user_id');
        //获取个人信息
        $user = User::find($user_id);
        //获取我购买的vip信息
        $vip_info = $user->userHasGift;
        //获取账户
        $wallet = $user->userHasWallet;
        //计算昨日收益
        $yestadey = date('Y-m-d',strtotime('-1 day'));  //昨日日期
        $ytdIncome = $user->userHasBill()->where('type',1)
            ->whereDate('create_time',$yestadey)
            ->sum('hcc_price');
        //统计本月收支
        $Mbill = $user->userHasBill()->whereYear('create_time', Carbon::now()->year)
            ->whereMonth('create_time', Carbon::now()->month);
        //dd($Mbill);
        //收入统计
        $income = $Mbill->where('type',1)->sum('hcc_price');
        //支出统计
        $expend = $Mbill->where('type',2)->sum('hcc_price');
        $resp = [
            'user_info' => $user,
            'in_or_out' => ['yesterday_income'=>$ytdIncome,'month_income'=>$income,'month_expend'=>$expend]
        ];
        return ajaxReturn(0, $resp);
    }


    /**
     * 账户总览
     */
    public function myWallet(Request $request) {
        $user_id = $request->get('user_id');
        //获取账户模型
        $wallet = User::find($user_id)->userHasWallet;
        return ajaxReturn(0, $wallet);
    }


    /**
     * 收支记录
     */
    public function myBills(Request $request) {
        $user_id = $request->get('user_id');
        $date = $request->input('date', date('Y-m'));    //年-月
        $page = $request->input('page', 1);
        $offset = ($page - 1) * 20;
        $year = Carbon::parse($date)->year;
        $month = Carbon::parse($date)->month;
        $mUser = User::find($user_id);
        $mbill = $mUser->userHasBill()->whereYear('create_time',$year)
            ->whereMonth('create_time',$month)
            ->limit(20)->offset($offset);
        $bills = $mbill->get();
        $income = $mbill->where('type',1)->sum('hcc_price'); //收入统计
        $expend = $mbill->where('type',2)->sum('hcc_price'); //支出统计
        $totalHcc = $mUser->userHasWallet->total_fee;
        $resp = compact('bills','income','expend','totalHcc');
        return ajaxReturn(0, $resp);
    }


    /**
     * 查看团队成员贡献
     */
    public function showTeamUserDevote(Request $request) {
        $userId = $request->get('user_id');
        $devoteId = $request->input('devote_user_id', null);
        if (!$devoteId) return ajaxReturn(-1,'','参数有空');
        $devoteUser = User::find($devoteId,['user_id','user_phone','user_headpic','user_gift_id','user_level']);
        $devotes = User::find($userId)->userHasBill()->where('devote_user_id',$devoteId)->get();
        return ajaxReturn(0,$devotes);
    }



    /**
     * 快速转账
     */
    public function transferHcc(Request $request) {
        $opt = $request->input('opt', 'view');
        if ($opt == 'check') {
            $phone = $request->input('phone', null);
            if (!$phone) return ajaxReturn(-1,'','参数有空');
            $toUser = User::where('user_phone',$phone)->first(['user_id','user_phone','user_headpic','user_name']);
            if (empty($toUser)) return ajaxReturn(-3, '', '用户不存在');
            return ajaxReturn(0,$toUser);
        }
        $user_id = $request->get('user_id');
        $user = User::with('userHasWallet')->find($user_id);
        $wallet = $user->userHasWallet;
        if ($opt == 'do') {
            $phone = $request->input('phone', null);
            $hcc = $request->input('hcc', null);
            $remark = $request->input('remark', '无备注信息');
            if (!$phone || !$hcc) return ajaxReturn(-1,'','参数有空');
            //检测余额
            if ($hcc > $wallet->lively_fee) return ajaxReturn(-2,'','账户月不足');
            $toUser = User::where('user_phone',$phone)->first();
            if (empty($toUser)) return ajaxReturn(-3, '', '用户不存在');
            DB::beginTransaction(); //事务
            try {
                $toUserWallet = $toUser->userHasWallet;
                $toUserWallet->lively_fee += $hcc;
                $toUserWallet->total_fee += $hcc;
                $toUserWallet->save();
                $bills[] = ['user_id'=>$toUser->user_id,'devote_user_id'=>$user->user_id,'hcc_price'=>$hcc,'type'=>1,'cate'=>5,'remark'=>$remark];
                $wallet->lively_fee -= $hcc;
                $wallet->total_fee -= $hcc;
                $wallet->save();
                $bills[] = ['user_id'=>$user->user_id,'devote_user_id'=>$toUser->user_id,'hcc_price'=>$hcc,'type'=>2,'cate'=>5,'remark'=>"转出{$hcc}hcc。"];
                DB::table('bill')->insert($bills);
                DB::commit();
                return ajaxReturn();
            } catch (\Exception $e) {
                DB::rollback();
                return ajaxReturn(-100,'','服务器异常');
            }
        }
        return ajaxReturn(0, $wallet);
    }


    /**
     * 用户vip升级
     */
    public function vipLevelUp(Request $request) {

    }










}
