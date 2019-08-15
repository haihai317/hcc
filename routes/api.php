<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


// 必传的认证参数为apikey、apitimes



// 用户模块路由
Route::group(['prefix'=>'/upload/','namespace'=>'Goods'],function(){
	// 上传图片
	Route::post('upload-img',"BrandController@uploadImg");
});



// 用户模块路由
Route::group(['prefix'=>'/user/','namespace'=>'User','middleware' =>'ApiKeyCheck'],function(){
	// 用户提交注册
	Route::post('register','UserController@makeRegister');
	// 用户单点登录
	Route::post('login',"UserController@getLogin");
	// 用户重置密码
	Route::post('rewritePass',"UserController@rewritePassword");

	// 发送短信验证码
	Route::post("send-message","UserController@dealSMS");
});



// 用户模块路由(需验证身份)
// 需要身份认证的都需要传入token
Route::group(['prefix'=>'/user/','namespace'=>'User','middleware' =>['ApiKeyCheck','userLogin']],function(){
	// 修改密码
	Route::post('editPass',"UserController@editPassword");

	// 修改个人信息
	Route::post('edit-user-mes',"UserController@editUserBaseMessage");
});



// 收货地址模块路由(需验证身份)
Route::group(['prefix'=>'/addr/','namespace'=>'User','middleware' =>['ApiKeyCheck','userLogin']],function(){
	// 新增收货地址
	Route::post('new-addr',"AddressController@makeNewAddr");
	// 修改收货地址信息
	Route::post('edit-addr','AddressController@editAddress');
	// 设为默认地址
	Route::post('default-change',"AddressController@defaultChange");
	// 删除收货地址
	Route::post('del-addr',"AddressController@deleteAddres");
	// 展示收货地址列表
	Route::post('addr-list',"AddressController@showAddressList");

});



// 订单模块
Route::group(["prefix"=>"/order/","namespace"=>'Order',"middleware"=>["ApiKeyCheck","userLogin"]],function(){
	//用户下单，新增一条订单,购物车参数json数组格式为：[{"goods_id":,"goods_title":,"goods_ifspec":,"goods_count":,"spec_id":,"spec_name":},{...}]
	Route::post('new-order','OrderController@makeOrder');

	// 用户申请退款退货换货($order_id/$refund_type/$refund_reason/$refund_img)
	Route::post('new-refund','OrderController@createRefund');

	// 用户查询订单列表
	Route::post('order-list',"OrderApiController@showOrderList");

	// 用户查询订单详情
	Route::post('order-detail',"OrderApiController@showOrderDetail");

	// （测试）将订单置为已付款
	Route::post("have-pay","OrderApiController@makeHavePay");

	// 取消未支付的订单
	Route::post("cancel-order","OrderApiController@cancelOrder");

	// 用户确认收货
	Route::post("complete-order","OrderApiController@completeOrder");

	// 用户申请退换
	Route::post('apply-refund',"OrderApiController@applyForRefund");

	// 用户查看退换进度详情
	Route::post("refund-detail","OrderApiController@showRefundDetail");

	// 用户提交退换货物流信息
	Route::post("send-back-express","OrderApiController@sendBackExpress");

	// 用户确认收到换货
	Route::post("get-change","OrderApiController@getRefundGoods");
});



// 商城模块（不需要身份认证）
Route::group(["prefix"=>'/goods/',"namespace"=>"Goods","middleware"=>['ApiKeyCheck']],function(){
	// 展示商城首页
	Route::post('shop-index',"GoodsApiController@showIndex");
	// 点击主页一级分类滚动条，查询相应的二级分类和商品
	Route::post('shop-classify-index',"GoodsApiController@theClassifyIndex");
	// 展示商品分类页的一二级分类
	Route::post('classify-page','GoodsApiController@classifyPage');
	// 查询商品的第三级分类
	Route::post('third-classify',"GoodsApiController@showThirdClass");
	//查询二三级分类下的商品列表
	Route::post('show-class-goods',"GoodsApiController@showClassGoods");
	// 查询商品详情
	Route::post('show-detail','GoodsApiController@showGoodsDetail');
});