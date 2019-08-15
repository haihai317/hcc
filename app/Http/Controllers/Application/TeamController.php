<?php

namespace App\Http\Controllers\Application;

use App\Model\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TeamController extends Controller
{
    /**
     * 我的团队
     */
    public function myTeam(Request $request) {
        $user_id = $request->get('user_id');
        $ztUser = User::find($user_id)->userHasSon()->with('userHasDigs')->get();
        //$team = $ztUser->withCount('userHasSon')->with(['userHasGift','userHasSon'])->get();
        //$team = User::find($user_id)->allSon;
        $team = User::withCount('userHasSon')->with(['userHasGift','userHasSon'=>function($lv1) {
            $lv1->withCount('userHasSon')->with(['userHasGift','userHasSon'=>function($lv2) {
                $lv2->withCount('userHasSon')->with(['userHasGift','userHasSon'=>function($lv3) {
                    $lv3->withCount('userHasSon')->with(['userHasGift','userHasSon'=>function($lv4) {
                        $lv4->withCount('userHasSon')->with(['userHasGift','userHasSon'=>function($lv5) {}]);
                    }]);
                }]);
            }]);
        }])->find($user_id);
        $myTeam = $team->userHasSon;
        //计算团队总数
        if (count($myTeam)>0) {
            $teamUserNum = User::teamTotal($myTeam);
        } else {
            $teamUserNum = 0;
        }
        $resp = ['team'=>$myTeam, 'count'=>$teamUserNum + $team->user_has_son_count];
        return ajaxReturn(0, $resp);
    }






}
