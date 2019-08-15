<?php
namespace App\Lib;
use App\Http\Model\Charge;
use App\Http\Model\Employee;
use Illuminate\Support\Facades\DB;

/**
 * 添加一条工资信息
 */
function addSalaryItem($em_id,$type,$fee,$work_id,$base_fee,$ratio,$time,$fun_month=null) {
    $time1 = $time;
    if (in_array($type,[1,4])) {
        $time1 = strtotime('-1 month',$time1);
    }
    $time1 = date('Y-m',$time1);
    $insertData = [
        'gzmx_em_id' => $em_id,
        'gzmx_type' => $type,
        'gzmx_fee' => $fee,
        'gzmx_work_id' => $work_id,
        'gzmx_base_fee' => $base_fee,
        'gzmx_ratio' => $ratio,
        'gzmx_time' => $time,
        'gzmx_month' => $time1
    ];
    if ($fun_month) $insertData['gzmx_tcmonth_num'] = $fun_month;
    DB::table('salay_detail')->insert($insertData);
}

/**
 * 资政业务提成计算
 *
 */
function salaryCredit($em_id, $credit_id, $charge_id=null) {
    //计算资政提成
    //查询该资政业务利润
    $creditInfo = DB::table('credit')->select('credit_real_profit','credit_type')->where('credit_id',$credit_id)->first();
    $creTc = $creditInfo->credit_real_profit * config('salary.credit_percentage');
    $creTc = round($creTc,2);
    $crePer = config('salary.credit_percentage');
    addSalaryItem($em_id,2,$creTc,$credit_id,$creditInfo->credit_real_profit,$crePer,time());

    //判断是否为资质业务
    if ($creditInfo->credit_type == '资质') {     //计算管理提成
        $role_id = Employee::getEmRole($em_id);
        $subcom = Employee::getEmComId($em_id);

        //查询总经理
        $zjl = Employee::where('em_co_id',$subcom)->where('em_role_id',2)->value('em_id');
        $zjl_cre_per = config('salary.cre_zjl_percentage');
        $zjl_cre_tc = round($creditInfo->credit_real_profit * $zjl_cre_per, 2);
        addSalaryItem($zjl,6,$zjl_cre_tc,$credit_id,$creditInfo->credit_real_profit,$zjl_cre_per,time());
        if ($role_id!=2) {
            if (in_array($role_id,[3,4,7,9,10])) {
                //查询财税副总
                $cwfz = Employee::where('em_co_id',$subcom)->where('em_role_id',3)->value('em_id');
                $cwfz_cre_per = config('salary.cre_fz_percentage');
                $cwfz_cre_tc = round($creditInfo->credit_real_profit * $cwfz_cre_per, 2);
                addSalaryItem($cwfz,6,$cwfz_cre_tc,$credit_id,$creditInfo->credit_real_profit,$cwfz_cre_per,time());
            } else if (in_array($role_id,[13,17])) {
                //查询话务经理
                $hwjl = Employee::where('em_co_id',$subcom)->where('em_role_id',17)->value('em_id');
                $hwjl_cre_per = config('salary.cre_fz_percentage');
                $hwjl_cre_tc = round($creditInfo->credit_real_profit * $hwjl_cre_per, 2);
                addSalaryItem($cwfz,6,$hwjl_cre_tc,$credit_id,$creditInfo->credit_real_profit,$hwjl_cre_per,time());
            }else {
                //查询业务副总
                $ywfz = Employee::where('em_co_id',$subcom)->where('em_role_id',12)->value('em_id');
                $ywfz_cre_per = config('salary.cre_fz_percentage');
                $ywfz_cre_tc = round($creditInfo->credit_real_profit * $ywfz_cre_per, 2);
                addSalaryItem($ywfz,6,$ywfz_cre_tc,$credit_id,$creditInfo->credit_real_profit,$ywfz_cre_per,time());
            }
        }
    }
    //判断关联新记账业务
    if ($charge_id) salaryNewCharge($em_id,$charge_id,null);
}

/**
 * 新记账业务提成结算
 */
function salaryNewCharge($em_id, $charge_id) {
    //查询记账信息
    $chargeInfo = Charge::select('ar_amount')->join('agreement','ar_charge_id','charge_id')->where('charge_id',$charge_id)->first();
    //获取新记账提成比例
    $newChaPer = config('salary.newcha_percentage');
    //计算提成
    $newChaTc = round($chargeInfo->ar_amount*$newChaPer,2);
    addSalaryItem($em_id,3,$newChaTc,$charge_id,$chargeInfo->ar_amount,$newChaPer,time());

    $role_id = Employee::getEmRole($em_id);
    $subcom = Employee::getEmComId($em_id);

    if (in_array($role_id,[7,9,10])) {
        //查询所在部门
        $depart = Employee::where('em_id',$em_id)->value('em_department_id');
        //查询财务部长
        $acc_pid = Employee::where('em_co_id',$subcom)->where('em_department_id',$depart)->where('em_isdeparter',1)->value('em_id');
        //获取新记账管理提成参数
        $newChaBzPer = config('salary.newcha_cwbz_percentage');
        //计算新记账管理提成
        $newChaBzTc = round($chargeInfo->ar_amount*$newChaBzPer,2);
        addSalaryItem($acc_pid,7,$newChaBzTc,$charge_id,$chargeInfo->ar_amount,$newChaBzPer,time());
    }

    //if ($credit_id) salaryCredit($em_id,$credit_id,null);
}


/**
 * 添加联系人
 */
function addLinkMan($link_tel) {
    DB::table('linkman')->insert(['link_tel'=>$link_tel]);
}


/**
 * curl 简单封装
 * @param $url 请求网址
 * @param bool $params 请求参数
 * @param int $ispost 请求方式
 * @param int $https https协议
 * @return bool|mixed
 */
function httpCurl($url, $params = false, $authToken=[], $ispost = true, $https = true)
{
    $httpInfo = array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    if (!empty($authToken)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER,$authToken );//这里设置token验证权限
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($https) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    }
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }

    $response = curl_exec($ch);

    if ($response === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    //$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //$httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);
    return json_decode($response, true);
}

