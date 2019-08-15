<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class User extends Model
{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'shop_user';

    /**
     * 数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    protected $baseField = ['user_id','user_name','user_headpic','user_parent_id'];


    //每个用户都有一个钱包
    public function userHasWallet() {
        return $this->hasOne(Wallet::class, 'user_id', $this->primaryKey);
    }

    //vip用户会有一个矿机
    public function userHasItc() {
        return $this->hasOne(UserItc::class, 'user_id', $this->primaryKey);
    }

    //vip用户有很多组
    public function userHasGroups() {
        return $this->hasMany(Group::class,'user_id',$this->primaryKey);
    }

    //用户的挖矿记录
    public function userHasDigs() {
        return $this->hasMany(Digrecord::class,'user_id',$this->primaryKey);
    }

    //用户都购买了一个vip礼包
    public function userHasGift() {
        return $this->belongsTo(Gift::class,'user_gift_id','id');
    }

    //用户有很多hcc流水
    public function userHasBill() {
        return $this->hasMany(Bill::class, 'user_id', $this->primaryKey);
    }

    //每个用户都有很多下级
    public function userHasSon() {
        return $this->hasMany(User::class,'user_parent_id',$this->primaryKey)
            ->select($this->baseField);
    }
    public function allSon() {
        return $this->userHasSon()->with(['allSon','userHasGift']);
        /*static $num = 1;
        $hasSon = $this->userHasSon();
        if ($num <= $lv) {
            ++$num;
            $res = $hasSon->with('allSon');
            return $res;
        } else {
            return $hasSon->with('allSon')->select($this->baseField);
        }*/
    }
    //获取无限极下级
    public function userAllSon($user_id) {
        return $this->with('allSon')->where('user_id',$user_id)->get();
    }



    //每个用户都有一个上级
    public function hasParent() {
        return $this->belongsTo(User::class,'user_parent_id','user_id');
    }
    //获取所有上级
    public function allParent() {
        return $this->hasParent()->with('allParent');
    }
    //获取该用户所有上级
    public function userAllParent($user_id) {
        return $this->with('allParent')->find($user_id);
    }


    //用户的城市合伙人（省）
    public function provicePartner() {
        //TODO:
    }
    //用户的城市合伙人（市）
    public function cityPartner() {
        //TODO:
    }
    //用户的城市合伙人（县）
    public function areaPartner() {
        //TODO:
    }




    /**
     * 限制查询只包括指定名称的部门。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfName($query, $name)
    {
        return $query->where('user_name','like', "%{$name}%");
    }


    public static function teamTotal($tree) {
        static $total = 0;
        foreach ($tree as $k => $v) {
            $total += $v->user_has_son_count;
            if (isset($v->userHasSon) && !empty($v->userHasSon)) {
                treeJsTotal($v->userHasSon);
            }
        }
        return $total;
    }


}
