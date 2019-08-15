<?php
use Illuminate\Http\Request;

Route::group(['namespace'=>'Application'],function(){
    //app首页
    Route::post('home', 'HomeController@appHome');
    //个人中心
    Route::post('mine', 'AuthController@mine');


    //vip用户路由
    Route::group(['middleware'=>'isvip'],function(){
        //成组充能
        Route::post('group/charge', 'HomeController@groupAll');
        //今日挖矿
        Route::post('digitc', 'HomeController@userDigItc');
        //vip查看我的团队
        Route::post('team/show', 'TeamController@myTeam');
        //账户总览
        Route::post('mywallet', 'AuthController@myWallet');
        //收支
        Route::post('mybills', 'AuthController@myBills');
        //查看团队用户对我的贡献
        Route::post('devotes', 'AuthController@showTeamUserDevote');
        //转账页面
        Route::post('transfer', 'AuthController@transferHcc');
    });
});
