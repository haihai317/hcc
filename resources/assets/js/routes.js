// 路由文件


import Login from './views/Login.vue'
import NotFound from './views/404.vue'
import Home from './views/Home.vue'
import Main from './views/Main.vue'
import Table from './views/nav1/Table.vue'
import Form from './views/nav1/Form.vue'
import user from './views/nav1/user.vue'
import Page4 from './views/nav2/Page4.vue'
import Page5 from './views/nav2/Page5.vue'
import Page6 from './views/nav3/Page6.vue'
import echarts from './views/charts/echarts.vue'

// 员工管理
import Employee from './views/employee/Employee_show.vue'
// 角色管理
import Role from './views/role/Role_show.vue'
// 权限管理
import Power from './views/power/Power_show.vue'
// 职位权限的设置
import RolePower from './views/role/role_power.vue'
// 子公司管理
import Company from './views/company/Company_show.vue'
// 部门管理
import Department from './views/department/Department_show.vue'
// 会员列表
import User from './views/customer/User_show.vue'
// 商品分类管理
import Classify from './views/classify/Classify_show.vue'
// 商品管理
import GoodsList from './views/goods_list/Goods_show.vue'
// 商品品牌管理
import Brand from  './views/brand/Brand_show.vue'

// 新建商品
import NewGoods from "./views/new_goods/NewGoods_show.vue";
// 修改商品
import EditGoods from "./views/edit_goods/EditGoods_show.vue";

//订单列表
import OrderList from "./views/order_list/Order_show.vue";

// 订单详情
import OrderDetail from "./views/order_detail/Detail_show.vue";

// 退换列表
import RefundList from "./views/refund_list/Refund_show.vue";
// 退换操作详情
import RefundDetail from "./views/refund_list/Refund_detail.vue";

// 轮播图管理
import BannerList from "./views/banner/Banner_show.vue";

let routes = [
    {
        path: '/login',
        component: Login,
        name: '',
        hidden: true
    },
    {
        path: '/404',
        component: NotFound,
        name: '',
        hidden: true
    },
    {
        path: '/',
        component: Home,
        name: '系统管理',
        iconCls: 'el-icon-message',//图标样式class
        children: [
            { path: '', component: Main, name: '主页' ,hidden:true},
            { path: '/employee', component: Employee, name: '员工管理' },
            { path: '/role',component: Role,name: '角色管理'},
            { path: '/power', component: Power, name: '权限管理' },
            { path: '/company', component: Company, name: '子公司管理' },
            {path:'/RolePower',component:RolePower,name:'职位授权',hidden:true},
            {path:'/department',component:Department,name:'部门管理'}
        ]
    },
    {
        path: '/',
        component: Home,
        name: '会员管理',
        iconCls: 'el-icon-message',//图标样式class
        children: [
            { path: '/customer', component: User, name: '会员列表' }
        ]
    },
    {
        path: '/',
        component: Home,
        name: '商品管理',
        iconCls: 'el-icon-message',//图标样式class
        children: [
            { path: '/classify', component: Classify, name: '商品分类' },
            { path: '/goods_list', component: GoodsList, name: '商品列表' },
            { path: '/brand', component: Brand, name: '商品品牌' },
            {path:'/NewGoods',component:NewGoods,name:'添加商品',hidden:true},
            {path:'/EditGoods',component:EditGoods,name:'编辑商品',hidden:true},
        ]
    },
    {
        path: '/',
        component: Home,
        name: '订单管理',
        iconCls: 'el-icon-tickets',//图标样式class
        children: [
            { path: '/order_list', component: OrderList, name: '订单列表' },
            { path: '/refund_list', component: RefundList, name: '退换订单' },
            {path:'/order_detail',component:OrderDetail,name:'订单详情',hidden:true},
            {path:'/refund_detail',component:RefundDetail,name:'退换详情',hidden:true}
        ]
    },
    {
        path: '/',
        component: Home,
        name: '商城设置',
        iconCls: 'el-icon-tickets',//图标样式class
        children: [
            { path: '/banner_list', component: BannerList, name: '轮播图管理' },
        ]
    },
    {
        path: '/',
        component: Home,
        name: '导航二',
        iconCls: 'fa fa-id-card-o',
        children: [
            { path: '/page4', component: Page4, name: '页面4' },
            { path: '/page5', component: Page5, name: '页面5' }
        ]
    },
    {
        path: '/',
        component: Home,
        name: '',
        iconCls: 'fa fa-address-card',
        leaf: true,//只有一个节点
        children: [
            { path: '/page6', component: Page6, name: '导航三' }
        ]
    },
    {
        path: '/',
        component: Home,
        name: 'Charts',
        iconCls: 'fa fa-bar-chart',
        children: [
            { path: '/echarts', component: echarts, name: 'echarts' }
        ]
    },
    {
        path: '*',
        hidden: true,
        redirect: { path: '/404' }
    }
    
];

export default routes;