<?php
//vip版本
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    protected $table = 'vip_gift';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];


}
