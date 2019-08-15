<?php

namespace App\Http\Controllers;

use App\Model\Config;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 团队奖励计算
     * @param $ratio 直推比例 $hccPrice 礼包包含hcc $lv 等级
     */
    protected function teamReward($ratio,$hccPrice,$lv) {
        $groupRatio = Config::where('key','manage_ratio')->value('value');
        $cf = $lv - 1;
        $p = $ratio * pow($groupRatio,$cf);
        return $hccPrice * $p;
    }
}
