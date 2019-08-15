<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'group';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected $baseFiled = ['user_id','user_name','user_headpic','user_gift_id'];

    //每个组都涉及四个人
    //第一个人
    public function gpOneUser() {
        return $this->belongsTo(User::class,'zt_user_id','user_id')->select($this->baseFiled);
    }
    //第二个人
    public function gpTwoUser() {
        return $this->belongsTo(User::class,'member1','user_id')->select($this->baseFiled);
    }
    //第三个人
    public function gpThreeUser() {
        return $this->belongsTo(User::class,'member2','user_id')->select($this->baseFiled);
    }
    //第三个人
    public function gpForeUser() {
        return $this->belongsTo(User::class,'member3','user_id')->select($this->baseFiled);
    }
}
