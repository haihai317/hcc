<?php
//用户矿机
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserItc extends Model
{
    protected $table = 'hcc_itc';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
