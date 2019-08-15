<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\User;

class UserLogin
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
        $token = $request->input('token');

        // 根据token是否改变判断是否异地登录，决定当前操作是否能执行
        $res = User::select("user_id")->where("user_access_token",$token)->first();
        if(isset($res)){
            // 保存user_id
            $request->attributes->add(["user_id"=>$res->user_id]); 
            return $next($request);
        }else{
            return redirect("middlewareRefuse/1");
        }
        
    }
}
