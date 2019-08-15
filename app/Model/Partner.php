<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $table = 'partner';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];


    //查询用户的城市合伙人
    public static function getPartner($pCode,$cCode,$aCode) {
        $partner = self::where('server_code',$aCode)->first();
        if (empty($partner)) {
            $partner = self::where('server_code',$cCode)->first();
            if (empty($partner)) {
                $partner = self::where('server_code',$pCode)->first();
                if (empty($partner)) {
                    $partner = false;
                }
            }
        }
        return $partner;
    }



}
