<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallet';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    //账户动态收入
    public static function walletInCome($userId, $hcc) {
        $wallet = self::where('user_id', $userId)->first();
        //获取收益静态存入比例
        $ratio = Config::where('key','income_static_ratio')->value('value');
        $staticIncome = $hcc * $ratio;
        $liveIncome = $hcc - $staticIncome;
        $wallet->total_fee = $wallet->total_fee + $hcc;
        $wallet->lively_fee = $wallet->lively_fee + $liveIncome;
        $wallet->lock_fee = $wallet->lock_fee + $staticIncome;
        $wallet->total_income = $wallet->total_income + $hcc;
        $wallet->save();
    }

    //城市合伙人收益
    public static function parterIncome($userId, $hcc, $devote_user_id) {
        $wallet = self::where('user_id', $userId)->first();
        //获取城市合伙人收益比例
        $ratio = Config::where('key','partner_ratio')->value('value');
        $incomeHcc = $hcc * $ratio;
        $wallet->total_fee = $wallet->total_fee + $incomeHcc;
        $wallet->lively_fee = $wallet->lively_fee + $incomeHcc;
        $wallet->total_income = $wallet->total_income + $incomeHcc;
        $wallet->save();
        Bill::create(['user_id'=>$userId,'devote_user_id'=>$devote_user_id,'hcc_price'=>$incomeHcc,'cate'=>9,'remark'=>"您是城市合伙人，收到了{$incomeHcc}HCC奖励。"]);
    }






}
