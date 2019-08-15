<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class CheckRole
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
        // 查询员工的职位是否具有该后台操作的权限
        // 获取传递的员工id和权限url
        $em_id = $request->input('user_id');
        $power_url = $request->input('power_str');
        // dd($power_url);
        if($power_url == 'common'){
          // 无需验证权限的接口
          return $next($request);
        }else{
          // 查询该员工所属职位是否有该功能权限
          $record = DB::table("role_power")
                    ->join('role',"role.role_id",'=','role_power.bind_role_id')
                    ->join('employee',"role.role_id",'=','employee.em_role_id')
                    ->join('power',"power.power_id",'=','role_power.bind_power_id')
                    ->where([["employee.em_id",'=',$em_id],['power.power_url','=',$power_url]])
                    ->select("role_power.bind_id")
                    ->first();

          if(isset($record)){
              // 如果有权限记录，说明该员工有该权限，放行
              return $next($request);
          }else{
              // 如果没有权限，阻止
              return redirect("middlewareRefuse/3");
          }
        }
          
    }
}
