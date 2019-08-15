<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
				<el-form-item>
					<el-input v-model="filters.name" placeholder="姓名"></el-input>
				</el-form-item>
				
				<el-form-item>
					<el-button type="primary" v-on:click="getUsers(1)">查询</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="users" highlight-current-row v-loading="listLoading"  style="width: 100%;">
			<el-table-column type="index" width="60">
			</el-table-column>
			<el-table-column prop="user_name" label="昵称" >
			</el-table-column>
			<el-table-column prop="user_phone" label="联系电话" >
			</el-table-column>
			<el-table-column prop="user_headpic" label="头像" >
			</el-table-column>
			<el-table-column prop="user_sex" label="性别" :formatter="formatSex">
			</el-table-column>
			<el-table-column prop="user_register_time" label="注册时间" >
			</el-table-column>
			<!-- <el-table-column label="操作" fixed="right">
				<template slot-scope="scope">
					<el-button size="small" @click="handleEdit(scope.row)">编辑</el-button>
					<el-button type="danger" size="small" @click="handleDel(scope.row.em_id)">删除</el-button>
				</template>
			</el-table-column> -->
		</el-table>

		<!--工具条-->
		<el-col :span="24" class="toolbar">
			<!-- <el-button type="danger" @click="batchRemove" :disabled="this.sels.length===0">批量删除</el-button> -->
			<el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="20" :total="total" style="float:right;">
			</el-pagination>
		</el-col>

	</section>
</template>

<script>
	import util from '../../common/js/util'
	//import NProgress from 'nprogress'
	import { getUserList } from '../../api/api';

	export default {
		data() {
			return {
				filters: {
					name: '',
					em_de_value:[]
				},
				users: [],
				total: 0,
				page: 1,
				listLoading: false,
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/customer'

			}
		},
		methods: {
			//性别显示转换
			formatSex: function (row, column) {
				return row.user_sex == 1 ? '男' : row.user_sex == 2 ? '女' : '未知';
			},
			handleCurrentChange(val) {
				this.page = val;
				this.getUsers(2);
			},
			//获取用户列表
			getUsers(num) {
				if(num == 1)this.page = 1;
				// console.log()
				let params = {
				    headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
				    power_str:this.power_str,
					page: this.page,
					name: this.filters.name
				};

				// console.log(params);return false;
				this.listLoading = true;
				//NProgress.start();
				getUserList(params).then((res) => {
					console.log(res);
					this.total = res.data.data.total;
					this.users = res.data.data.users;
					// console.log(this.users);
					this.listLoading = false;
					//NProgress.done();
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			}
		},
		mounted() {
			this.getUsers(1);
		}
	};

</script>

<style scoped>

</style>