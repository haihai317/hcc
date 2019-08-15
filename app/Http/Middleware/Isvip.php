<?php

namespace App\Http\Middleware;

use App\Model\User;
use Closure;

class Isvip
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
        $user_id = $request->get('user_id');
        if (User::find($user_id)->user_level == 0) {
            return ajaxReturn(444,'','非vip用户');
        }
        return $next($request);
    }
}
