<template>
	<el-row class="container">
		<el-col :span="24" class="header">
			<el-col :span="10" class="logo" :class="collapsed?'logo-collapse-width':'logo-width'">
				{{collapsed?'':sysName}}
			</el-col>
			<!-- <el-col :span="10">
				<div class="tools" @click.prevent="collapse">
					<i class="fa fa-align-justify"></i>
				</div>
			</el-col> -->
			<el-col :span="4" class="userinfo">
				<el-dropdown trigger="hover">
					<span class="el-dropdown-link userinfo-inner"><img :src="this.sysUserAvatar" /> {{sysUserName}}</span>
					<el-dropdown-menu slot="dropdown">
						<el-dropdown-item>我的消息</el-dropdown-item>
						<el-dropdown-item>设置</el-dropdown-item>
						<el-dropdown-item divided @click.native="logout">退出登录</el-dropdown-item>
					</el-dropdown-menu>
				</el-dropdown>
			</el-col>
		</el-col>
		<el-col :span="24" class="main">
			<aside :class="collapsed?'menu-collapsed':'menu-expanded'">
				<!--导航菜单-->
				<el-menu :default-active="$route.path" class="el-menu-vertical-demo" @open="handleopen" @close="handleclose" @select="handleselect"
					 unique-opened router v-show="!collapsed">
					<template v-for="(item,index) in nodes" v-if="!item.hidden">
						<el-submenu :index="index+''" v-if="!item.leaf">
							<template slot="title"><i :class="item.power_icon"></i>{{item.power_name}}</template>
							<el-menu-item v-for="child in item.children" :index="child.power_url" :key="child.power_url" v-if="!child.hidden">{{child.power_name}}</el-menu-item>
						</el-submenu>
						<el-menu-item v-if="item.leaf&&item.children.length>0" :index="item.children[0].power_url"><i :class="item.power_icon"></i>{{item.children[0].power_name}}</el-menu-item>
					</template>
				</el-menu>
			</aside>
			<section class="content-container">
				<div class="grid-content bg-purple-light">
					<el-col :span="24" class="breadcrumb-container">
						<strong class="title">{{$route.name}}</strong>
						<el-breadcrumb separator="/" class="breadcrumb-inner">
							<el-breadcrumb-item v-for="item in $route.matched" :key="item.path">
								{{ item.name }}
							</el-breadcrumb-item>
						</el-breadcrumb>
					</el-col>
					<el-col :span="24" class="content-wrapper">
						<transition name="fade" mode="out-in">
							<router-view></router-view>
						</transition>
					</el-col>
				</div>
			</section>
		</el-col>
	</el-row>
</template>

<script>
	import util from '../common/js/util'
	//import NProgress from 'nprogress'
	import { getNodes,logout } from '../api/api';

	export default {
		data() {
			return {
				sysName:'区块链商城系统',
				collapsed:false,
				sysUserName: '',
				sysUserAvatar: '',
				form: {
					name: '',
					region: '',
					date1: '',
					date2: '',
					delivery: false,
					type: [],
					resource: '',
					desc: ''
				},
				nodes:[{
					path:"/",name:"无管理权限",iconCls:"el-icon-message",
					children:[{
						path:"/",name:"主页"		
					}]
				}],
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'common'
			}
		},
		methods: {
			onSubmit() {
				// console.log('submit!');
			},
			handleopen() {
				// console.log(this.$router);
			},
			handleclose() {
				// console.log(this.$router);
			},
			handleselect: function (a, b) {
			},
			//退出登录
			logout: function () {
				var _this = this;
				this.$confirm('确认退出吗?', '提示', {
					//type: 'warning'
				}).then(() => {
					// 退出操作，清除后台的session
					let params = {
					    headers: {
					        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					    },
					    user_id:this.user_id,
				    	power_str:this.power_str,
					};
					logout(params).then((res) => {
						// console.log(res);
					});
					this.$store.commit('STOREUSERINFO','123');
					sessionStorage.removeItem('user');
					_this.$router.push('/login');
				}).catch((error) =>{
					// sessionStorage.removeItem('user');
					// this.$router.push('/login');
				});
			},
			havenode:function(){
				let user = JSON.parse(sessionStorage.getItem('user'));
				// user = JSON.parse(user)
				// console.log(user);
				let params = {
				    headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
				    power_str:this.power_str,
				    em_id:user.user_alias
				};
				getNodes(params).then((res) => {
					this.nodes = res.data.data;
					console.log(res);
					// this.listLoading = false;
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			}
		},
		mounted() {
			var user =  JSON.parse(sessionStorage.getItem('user'));
			if (user.user_alias) {
				this.sysUserName = user.username;
				this.sysUserAvatar = '/admin.jpg';
				// // console.log(user);
			}

			// 查询管理者的权限
			this.havenode();

		}
	};

</script>

<style scoped lang="scss">
	@import '../styles/vars.scss';
	
	.container {
		position: absolute;
		top: 0px;
		bottom: 0px;
		width: 100%;
		.header {
			height: 60px;
			line-height: 60px;
			background: #4a688d;
			color:#72e0d5;
			.userinfo {
				text-align: right;
				padding-right: 35px;
				float: right;
				.userinfo-inner {
					cursor: pointer;
					color:#fff;
					img {
						width: 40px;
						height: 40px;
						border-radius: 20px;
						margin: 10px 0px 10px 10px;
						float: right;
					}
				}
			}
			.logo {
				//width:230px;
				height:60px;
				font-size: 22px;
				padding-left:20px;
				padding-right:20px;
				border-color: rgba(238,241,146,0.3);
				border-right-width: 1px;
				border-right-style: solid;
				img {
					width: 40px;
					float: left;
					margin: 10px 10px 10px 18px;
				}
				.txt {
					color:#fff;
				}
			}
			.logo-width{
				width:230px;
			}
			.logo-collapse-width{
				width:60px
			}
			.tools{
				padding: 0px 23px;
				width:14px;
				height: 60px;
				line-height: 60px;
				cursor: pointer;
			}
		}
		.main {
			display: flex;
			// background: #324057;
			position: absolute;
			top: 60px;
			bottom: 0px;
			overflow: hidden;
			aside {
				flex:0 0 230px;
				width: 230px;
				// position: absolute;
				// top: 0px;
				// bottom: 0px;
				.el-menu{
					height: 100%;
					background-color: #fff;
					color:#fff;
				}
				.collapsed{
					width:60px;
					.item{
						position: relative;
					}
					.submenu{
						position:absolute;
						top:0px;
						left:60px;
						z-index:99999;
						height:auto;
						display:none;
					}

				}
			}
			.menu-collapsed{
				flex:0 0 60px;
				width: 60px;
			}
			.menu-expanded{
				flex:0 0 230px;
				width: 230px;
			}
			.content-container {
				// background: #f1f2f7;
				flex:1;
				// position: absolute;
				// right: 0px;
				// top: 0px;
				// bottom: 0px;
				// left: 230px;
				overflow-y: scroll;
				padding: 20px;
				.breadcrumb-container {
					//margin-bottom: 15px;
					.title {
						width: 200px;
						float: left;
						color: #475669;
					}
					.breadcrumb-inner {
						float: right;
					}
				}
				.content-wrapper {
					background-color: #fff;
					box-sizing: border-box;
				}
			}
		}
	}
</style>