<?php 

// 接口返回数据整理
function ajaxReturn($code=0, $data='', $msg='success') {
    $resp = array(
        'code' => $code,
        'data'   => $data,
        'msg'    => $msg
    );
    return $resp;
}







/**
 * 随机生成要求位数个字符
 * @param length 规定几位字符
 */
function getRandChar($length){
    $str = null;
    $strPol = "0123456789abcdefghijklmnopqrstuvwxyz";//大小写字母以及数字
    $max = strlen($strPol)-1;

    for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];
    }
    return $str;
}








/**
 * 千讯信通短信验证码
 * @route location api
 * @return boolean
 */
function qxMessage($_phone, $_text) {
    $url = env('QX_URL');

    $phone = $_phone;
    $text = $_text;

    $time = date('yyyyMMddHHmmss');
    $uid = env('QX_UID');
    $key = env('QX_KEY');
    $sign = md5($uid.$key.$time);   //签名

    $post_data = array();
    $post_data['uid'] = $uid;
    $post_data['timestamp'] = $time;
    $post_data['sign'] = $sign;
    $post_data['mobile'] = $phone;
    $post_data['text'] = $text;
    $o = '';
    foreach ($post_data as $k=>$v) {
        $o.="$k=".urlencode($v).'&';
    }
    $post_data = substr($o,0,-1);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, true);
}





/**
 * 验证手机号码格式
 * @param string $phone
 * @return boolean
 */
function isMobile($phone) {
    //preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $phone) ? true : false;
    return preg_match('/^1[3456789]\d{9}$/', $phone) ? true : false;
}




//生成6位数字验证码
function createVerify($nums) {
    $num = '0,1,2,3,4,5,6,7,8,9';
    $list = explode(',', $num);
    $authVerify = '';
    for ($i = 0; $i < $nums; $i++) {
        $randnum = rand(0, 9);
        $authVerify .= $list[$randnum];
    }
    return $authVerify;
}











function showMsg($url, $msg, $type='success') {
    $data = ['msg'=>$msg, 'url'=>$url, 'type'=>$type];
    return view('common.msg', compact('data'));
}



//计算团队总数
function treeJsTotal($tree) {
    static $total = 0;
    foreach ($tree as $k => $v) {
        $total += $v->userHasSonCount;
        if (isset($v->userHasSon) && !empty($v->userHasSon)) {
            treeJsTotal($v->userHasSon);
        }
    }
    return $total;
}

//将tree数组，转化为一般线性数组
function treeToArr($tree) {
    static $arr = [];
    foreach ($tree as $key => $val) {
        $arr[] = $val;
        if (isset($val['allSonCols']) && !empty($val['allSonCols'])) {
            treeToArr($val['allSonCols']);
        }
    }
    return $arr;
}

//将tree数组，转化为一般线性id数组
function treeToArrIds($tree) {
    static $arr = [];
    foreach ($tree as $key => $val) {
        $arr[] = $val['col_id'];
        if (isset($val['allSonCols']) && !empty($val['allSonCols'])) {
            treeToArrIds($val['allSonCols']);
        }
    }
    return $arr;
}






