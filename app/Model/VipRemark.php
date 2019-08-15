<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VipRemark extends Model
{
    protected $table = 'vip_remark';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
