<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::any('vip/buy', 'User\OutController@buyVip');


//管理后台路由 
 
Route::get('/', function(){
	return view('welcome');
});


// 登录操作
Route::post('login', 'Admin\LoginController@adminLogin');
// 退出操作
Route::post('logout',"Admin\LoginController@adminLogout");
// 中间件返回失败操作
// Route::get('middlewareRefuse/{id}',"Auth\LoginController@middlewareRefuse");
Route::get('middlewareRefuse/{id}',function($id){
	if($id == 1){
		return ajaxReturn(789,'','用户身份过期，请重新登录！');
	}else if($id == 2){
		return ajaxReturn(456,"","请求超时，请重新提交请求！");
	}else if($id == 3){
		return ajaxReturn(123,"","您没有权限进行当前操作");
	}
});




// 员工管理板块
Route::group(['prefix'=>'/employee/','namespace'=>'Admin','middleware' => ['adminLogin','checkRole']],function(){
	// 按条件搜索员工列表
	Route::post('showList','EmployeeController@showEmList');
	// 查找部门和职位信息
	Route::post('findDeAndRole','EmployeeController@getDeAndRole');
	// 添加员工操作
	Route::post('addEmployee','EmployeeController@createNewEm');
	// 编辑员工
	Route::post('editEm','EmployeeController@editEmMes');
	// 删除员工
	Route::post('delEm','EmployeeController@deleteEm');
});




// 角色管理板块
Route::group(['prefix'=>'/role/','namespace'=>'Admin','middleware' => ['adminLogin','checkRole']],function(){
	// 搜索所有的职位信息
	Route::post('showList','RoleController@showRoleList');
	// 添加新的职位
	Route::post('addRole','RoleController@makeAddRole');
	// 删除某职位
	Route::post('delRole',"RoleController@makeDel");
	// 修改职位基本信息
	Route::post('editRole',"RoleController@editRoleMes");
	// 禁用某职位
	// Route::post('delRole',"RoleController@makeDel");
});






// 权限管理板块
Route::group(['prefix'=>'/power/','namespace'=>'Admin','middleware' => ['adminLogin','checkRole']],function(){
	// 搜索所有的职位信息
	Route::post('showList','PowerController@showPowerList');
	// 添加新的职位
	Route::post('addPower','PowerController@makeAddPower');
	// 获取所有的父级权限
	Route::post('getParents',"PowerController@getAllParents");
	// 获取授权树结构
	Route::post('getRolePower',"PowerController@getRolepowers");
	// 保存为职位设置的权限
	Route::post('saveRolePower',"PowerController@submitRolePower");
	// 获取某员工现有的所有权限集
	Route::post('adminNodes',"PowerController@userNodes");
	// 删除指定的权限
	Route::post('delPower',"PowerController@removePower");
	// 修改某权限的基本信息
	Route::post('editPower',"PowerController@makeEdit");
});





// 子公司管理板块
Route::group(['prefix'=>'/com/','namespace'=>'Admin','middleware' => ['adminLogin','checkRole']],function(){
	// 展示子公司列表
	Route::post('showList','CompanyController@showComList');
	// 添加子公司操作
	Route::post('addCom','CompanyController@makeCom');
	// 修改子公司信息
	Route::post('editCom','CompanyController@makEdit');
	// 删除指定子公司
	Route::post('delIt','CompanyController@delTheCom');
	// 新增部门时查找所有子公司信息
	Route::post('findComPoint','CompanyController@findComPoint');
});






// 部门管理板块
Route::group(['prefix'=>'/department/','namespace'=>'Admin','middleware' => ['adminLogin','checkRole']],function(){
	// 展示部门列表
	Route::post('showList','DepartmentController@showDeList');
	// 添加子公司操作
	Route::post('addDe','DepartmentController@makeDe');
	// 修改子公司信息
	Route::post('editDe','DepartmentController@makEdit');
	// 删除指定子公司
	Route::post('delIt','DepartmentController@delTheDe');
});










// 会员管理
Route::group(['prefix'=>'/user/','namespace'=>"User",'middleware'=>['adminLogin','checkRole']],function(){
	// 展示会员列表
	Route::post('show-list','UserController@showUserList');
});






// 商品管理
Route::group(['prefix'=>'/goods/','namespace'=>"Goods",'middleware'=>['adminLogin','checkRole']],function(){
	// 商品分类
	// 新增商品分类
	Route::post('new-classify','ClassifyController@makeNewClassify');
	// 查找商品分类列表
	Route::post('show-classify-list','ClassifyController@showClassifyList');
	// 新建分类时查找父级分类
	Route::post('show-parent-classify',"ClassifyController@showClassifyParent");
	// 修改分类信息
	Route::post('edit-classify',"ClassifyController@editClassifyMes");
	// 删除分类
	Route::post('delete-classify',"ClassifyController@deleteClassify");

	// 商品品牌
	// 添加新的品牌
	Route::post('add-brand',"BrandController@addBrand");
	// 展示所有的品牌
	Route::post('show-Brands',"BrandController@showAllBrand");
	// 修改品牌信息
	Route::post('edit-brand',"BrandController@editBrand");
	// 删除品牌
	Route::post('delete-brand','BrandController@delBrand');


	// 查找所有品牌和分类
	Route::post('classify-brand','ClassifyController@findClAndBr');


	// 添加或更新商品
	Route::post('add-goods',"GoodsController@makeAdd");
	// 商品列表
	Route::post('goods-list',"GoodsController@showGoodsList");
	// 下架商品
	Route::post('drop-down-goods',"GoodsController@dropDown");
	// 上架商品
	Route::post('drop-up-goods',"GoodsController@dropUp");
	// 删除商品
	Route::post('delete-goods',"GoodsController@deleteGoods");
	//编辑商品信息
	Route::post('show-edit-mes',"GoodsController@showEditMes");


	// banner图管理
	// 添加新的banner
	Route::post('add-banner',"BannerController@addBanner");
	// 展示所有的banner
	Route::post('show-banners',"BannerController@showAllBanner");
	// 修改banner信息
	Route::post('edit-banner',"BannerController@editBanner");
	// 删除banner
	Route::post('delete-banner','BannerController@delBanner');
});










// 订单管理
Route::group(['prefix'=>'/order/','namespace'=>"Order",'middleware'=>['adminLogin','checkRole']],function(){
	// 展示订单列表
	Route::post('show-list','OrderController@showOrderList');
	// 后台提交物流信息
	Route::post('add-express',"OrderController@addExpressMes");
	// 展示订单详情
	
	
	 
	// 退换订单部分
	Route::post('show-detail',"OrderController@showOrderDetail");
	// 展示在退款退货处理中的订单
	Route::post("refund-list","OrderController@showRefundList");
	// 展示退换订单详情
	Route::post("refund-detail","OrderController@showRefundDetail");
	// 商家拒绝仅退款申请
	Route::post("refuse-onlyMoney","OrderController@refuseBackMoney");
	// 商家同意仅退款申请
	Route::post("agree-onlyMoney","OrderController@agreeBackMoney");
	// 保存商家选定的寄回地址，并改状态为等待买家寄回
	Route::post("save-refund-back-addr","OrderController@saveRefundBackAddr");
	// 商家收到退货并同意退款
	Route::post("do-back-money","OrderController@doBackMoney");
	// 商家填写寄回物流
	Route::post("write-send-express","OrderController@writeRefundSendExpress");
});


// 收货地址模块
Route::group(['prefix'=>'/addr/','namespace'=>'User','middleware' =>['adminLogin','checkRole']],function(){
	// 新增商家收货地址
	Route::post('back-addr-list',"AddressController@backAddrList");
	// 保存商家新增的寄回地址
	Route::post('save-back-addr',"AddressController@saveBackAddr");

});