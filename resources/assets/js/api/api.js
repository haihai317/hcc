import axios from 'axios';

let base = '';



// 登录操作
export const requestLogin = params => { return axios.post(`${base}/login`, params).then(res => res.data); };
// 登出操作
export const logout = params => { return axios.post(`${base}/logout`, params).then(res => res.data); };




// 获取员工列表
export const getEmployeeListPage = params => { return axios.post(`${base}/employee/showList`, params); };

// 获取部门和职位信息
export const getDeAndRole = params =>{ return axios.post(`${base}/employee/findDeAndRole`, params ); };

// 添加员工操作
export const addEmployee = params =>{ return axios.post(`${base}/employee/addEmployee`,params); };

// 删除员工操作
export const delEm = params => { return axios.post(`${base}/employee/delEm`,params); };

// 修改员工信息
export const editEm = params => { return axios.post(`${base}/employee/editEm`,params); };







// 获取角色操作
export const getRoleListPage = params=>{ return axios.post(`${base}/role/showList`,params); };

// 添加角色操作
export const addRole = params =>{ return axios.post(`${base}/role/addRole`,params); };

// 删除某一职位
export const delRole = params =>{ return axios.post(`${base}/role/delRole`,params); };

// 修改职位信息
export const editRole = params=>{ return axios.post(`${base}/role/editRole`,params); };





// 获取权限操作
export const getPowerListPage = params =>{ return axios.post(`${base}/power/showList`,params); };

// 添加权限操作
export const addPower = params =>{ return axios.post(`${base}/power/addPower`,params); };

// 获取所有的父级权限
export const getParents = params =>{ return axios.post(`${base}/power/getParents`, params ); };

// 获取power树结构
export const getRolePower = params => { return axios.post(`${base}/power/getRolePower`,params ); };

// 提交职位的权限设置
export const submitTree = params => { return axios.post(`${base}/power/saveRolePower`,params); };

// 登录后获取拥有的权限
export const getNodes = params => { return axios.post(`${base}/power/adminNodes`, params); };

// 删除权限
export const delPower = params =>{ return axios.post(`${base}/power/delPower`,params); };

// 修改权限信息
export const editPower = params =>{ return axios.post(`${base}/power/editPower`,params); };







// 查看所有的子公司信息
export const getComList = params => { return axios.post(`${base}/com/showList`, params); };

// 提交新的子公司信息
export const addCompany = params => { return axios.post(`${base}/com/addCom`, params); };

// 修改子公司的而信息
export const editCompany = params => { return axios.post(`${base}/com/editCom`,params); };

// 删除单个子公司
export const delCompany = params => { return axios.post(`${base}/com/delIt`,params); };







// 为新建部门查找子公司
export const findComPoint = params =>{ return axios.post(`${base}/com/findComPoint`,params); };

// 提交新建部门
export const addDe = params =>{ return axios.post(`${base}/department/addDe`,params); };

// 获取所有部门列表
export const showDeList = params => { return axios.post(`${base}/department/showList`,params); };

// 删除部门信息
export const delDe = params =>{ return axios.post(`${base}/department/delIt`, params); };

// 修改部门信息
export const editDe = params => { return axios.post(`${base}/department/editDe`, params); };







// 会员管理
// 展示会员列表
export const getUserList = params => { return axios.post(`${base}/user/show-list`, params); };










// 商品管理

// 添加商品分类
export const addClassify = params =>{ return axios.post(`${base}/goods/new-classify`,params); };
// 展示多级父级分类
export const getClassifyParents = params =>{ return axios.post(`${base}/goods/show-parent-classify`,params); };
// 商品分类列表
export const allClassify = params =>{ return axios.post(`${base}/goods/show-classify-list`,params); };
// 修改商品分类信息
export const editClassify = params =>{ return axios.post(`${base}/goods/edit-classify`,params); };
// 删除商品分类
export const delClassify = params =>{ return axios.post(`${base}/goods/delete-classify`,params); };

// 添加商品品牌
export const addBrand = params =>{ return axios.post(`${base}/goods/add-brand`,params);};
// 展示所有的品牌
export const getBrands = params =>{ return axios.post(`${base}/goods/show-Brands`,params);};
// b编辑品牌信息
export const editBrands = params =>{ return axios.post(`${base}/goods/edit-brand`,params);};
// 删除品牌
export const delBrands = params =>{ return axios.post(`${base}/goods/delete-brand`,params);};

// 添加banner
export const addBanner = params =>{ return axios.post(`${base}/goods/add-banner`,params);};
// 展示所有banner
export const getBanners = params =>{ return axios.post(`${base}/goods/show-banners`,params);};
// b编辑banner
export const editBanners = params =>{ return axios.post(`${base}/goods/edit-banner`,params);};
// 删除banner
export const delBanners = params =>{ return axios.post(`${base}/goods/delete-banner`,params);};


// 查找商品所属的所有分类和品牌
export const getClaAndBr = params =>{ return axios.post(`${base}/goods/classify-brand`,params);};


// 添加或者更新商品
export const addGoods = params =>{ return axios.post(`${base}/goods/add-goods`,params);};
// 展示商品列表
export const getGoodsList = params =>{ return axios.post(`${base}/goods/goods-list`,params);};
// 下架商品
export const dropDownGoods = params =>{ return axios.post(`${base}/goods/drop-down-goods`,params); };
// 上架商品
export const dropUpGoods = params =>{ return axios.post(`${base}/goods/drop-up-goods`,params); };
// 删除商品
export const delGoods = params =>{ return axios.post(`${base}/goods/delete-goods`,params); };
//展示待编辑商品的信息
export const showEditMes = params =>{ return axios.post(`${base}/goods/show-edit-mes`,params); };






// 订单模块
// 展示订单列表
export const showOrderList = params =>{ return axios.post(`${base}/order/show-list`,params); };
// 提交物流信息
export const addExpressMes = params =>{ return axios.post(`${base}/order/add-express`,params); };
// 展示订单详情
export const showOrderDetail = params =>{ return axios.post(`${base}/order/show-detail`,params); };


// 订单退换逻辑
// 展示退款退货处理的订单
export const showRefundList = params =>{ return axios.post(`${base}/order/refund-list`,params); };
// 展示退款退货订单详情
export const showRefundDetail = params =>{ return axios.post(`${base}/order/refund-detail`,params); };
//商家拒绝仅退款需求
export const refuseBackMoney = params =>{ return axios.post(`${base}/order/refuse-onlyMoney`,params); };
//商家同意并执行退款
export const agreeBackMoney = params =>{ return axios.post(`${base}/order/agree-onlyMoney`,params); };
// 查找商家的收货地址列表
export const backAddrList = params =>{ return axios.post(`${base}/addr/back-addr-list`,params); };
//保存商家的寄回地址
export const saveBackAddr = params=>{ return axios.post(`${base}/addr/save-back-addr`,params); };
//保存商家选定的寄回地址，并改状态为等待买家寄回
export const saveBackAddrMes = params=>{ return axios.post(`${base}/order/save-refund-back-addr`,params); };
//商家收到退货并点击退款
export const doBackMoney = params=>{ return axios.post(`${base}/order/do-back-money`,params); };
// 商家填写退还的物流信息
export const addRefundExpressMes = params=>{ return axios.post(`${base}/order/write-send-express`,params); };
