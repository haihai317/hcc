

require('./bootstrap');

window.Vue = require('vue');



// 引入需要的组件和文件
import babelpolyfill from 'babel-polyfill'

import Vue from 'vue'

import VueRouter from 'vue-router'
import routes from './routes'

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'

import store from './vuex/store'
import Vuex from 'vuex'

import 'font-awesome/css/font-awesome.min.css'

// 引入富文本框需要的东西
// import 'bootstrap/js/dist/modal.js'
// import 'bootstrap/js/dist/dropdown.js'
// import 'bootstrap/js/dist/tooltip.js'
// import 'bootstrap/dist/js/bootstrap.bundle.min.js'
// import 'bootstrap/dist/css/bootstrap.css'
// import 'font-awesome/css/font-awesome.css'
// import 'summernote'
// import 'summernote/dist/lang/summernote-zh-CN.js'
// import 'summernote/dist/summernote.css'


// import Mock from './mock'
// Mock.bootstrap();

// 实例化路由文件
Vue.use(VueRouter)
const router = new VueRouter({
  routes
})
// 实例化ui
Vue.use(ElementUI)
// 实例化vuex
Vue.use(Vuex)




// 打开页面前的钩子函数，验证登录
router.beforeEach((to, from, next) => {
  // 跳转登录页，清除缓存
  if (to.path == '/login') {
    sessionStorage.removeItem('user');
  }

  // 判断缓存是否存在
  let user = sessionStorage.getItem('user');
  // console.log(user);
  // 不存在跳转登录
  if(!user && to.path != '/login'){
    // 重新登陆
    next({ path: '/login' })
  }else{
    next()
  }
})







// 引入单页文件
Vue.component('App', require('./App.vue'));
// 实例化
const app = new Vue({
    el: '#app',
    router,
    store
});
