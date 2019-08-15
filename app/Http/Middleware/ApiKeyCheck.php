<?php

namespace App\Http\Middleware;

use Closure;

class ApiKeyCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 获取验证参数
        $apikey = $request->input("apikey",'');
        $apitimes = $request->input("apitimes",0);

        // 组织相对应的验证参数
        $checkKey = md5(md5($apitimes.'blackchainshop'));
        $checkTimes = time();
        $resultTimes = $checkTimes - $apitimes;

        // 验证时间戳是否超时
        if( $resultTimes < 30 ){
            // 验证key是否对应
            if($apikey == $checkKey){
                return $next($request);
            }else{
                return ajaxReturn(456,'','身份认证失败');
            }
        }else{
            return redirect("middlewareRefuse/2");
        }
    }
}
