<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
				<el-form-item>
					<el-input v-model="filters.goods_title" placeholder="商品名称"></el-input>
				</el-form-item>
				<el-form-item>
					<!-- 级联选择器 -->
					<el-cascader
				    expand-trigger="hover"
				    :options="class_options"
				    v-model="filters.goods_classify_value"
				    placeholder="商品所属分类"
				    >
				  	</el-cascader>
				</el-form-item>	
				<el-form-item>
					<el-select v-model="filters.goods_brand_id" placeholder="商品所属品牌">
					    <el-option
					      v-for="item in brand_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
					 </el-select>
				</el-form-item>
				<el-form-item>
					<el-select v-model="filters.goods_status" placeholder="商品状态">
					    <el-option
					      v-for="item in status_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
					 </el-select>
				</el-form-item>
				<el-form-item>
					<el-button type="primary" v-on:click="getGoods(1)">查询</el-button>
				</el-form-item>
				<el-form-item>
					<el-button type="success" @click="handleAdd">添加商品</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="users"  v-loading="listLoading"  style="width: 100%;">
			<el-table-column type="index" width="55">
			</el-table-column>
			<el-table-column prop="goods_title" label="商品名称">
			</el-table-column>
			<el-table-column  label="商品图片" >
				<template slot-scope="scope">
                  <img :src="scope.row.goods_main_img" width="70">
                </template>
			</el-table-column>
			<el-table-column prop="goods_status" label="商品状态" :formatter="formatStatus">
			</el-table-column>
			<el-table-column prop="goods_store" label="库存"  :formatter="formatStore">
			</el-table-column>
			<el-table-column prop="goods_marketprice" label="市场价" >
			</el-table-column>
			<el-table-column prop="class_name" label="所属分类" >
			</el-table-column>
			<el-table-column prop="brand_title" label="所属品牌" >
			</el-table-column>
			<el-table-column prop="goods_status" label="多规格" :formatter="formatSpec">
			</el-table-column>
			<el-table-column label="操作" fixed="right">
				<template slot-scope="scope">
					<el-button size="small" type="primary" plain @click="handleEdit(scope.row.goods_id)">编辑</el-button>
					<el-button v-if="scope.row.goods_status == 2" type="danger" size="small" @click="handleDel(scope.row.goods_id)">删除</el-button>
					<el-button v-if="scope.row.goods_status == 1" size="small" @click="dropDown(scope.row.goods_id)" style="margin-top:10px;">置为下架</el-button>
					<el-button v-if=" scope.row.goods_status == 2" size="small" @click="dropUp(scope.row.goods_id)" style="margin-top:10px;">重新上架</el-button>
				</template>
			</el-table-column>
		</el-table>

		<!--工具条-->
		<el-col :span="24" class="toolbar">
			<!-- <el-button type="danger" @click="batchRemove" :disabled="this.sels.length===0">批量删除</el-button> -->
			<el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="10" :total="total" style="float:right;">
			</el-pagination>
		</el-col>

	</section>
</template>

<script>
	import util from '../../common/js/util'
	//import NProgress from 'nprogress'
	import { getGoodsList, dropDownGoods,dropUpGoods,getClaAndBr,delGoods } from '../../api/api';

	export default {
		data() {
			return {
				filters: {
					goods_title: '',
					goods_classify_value:[],
					goods_brand_id:null,
					goods_status:1
				},
				users: [],
				total: 0,
				page: 1,
				listLoading: false,
				sels: [],//列表选中列
				// 新增员工提供的公司部门选择器
				de_options:[],
				class_options:[],
				brand_options:[],
				status_options:[
					{label:"已上架",value:1},
					{label:"已下架",value:2},
					{label:"已售罄",value:3},
				],
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/goods_list'

			}
		},
		methods: {
			//库存
			formatStore: function (row, column) {
				if(row.goods_ifspec  == 1){
					// 使用商品表中的库存
					return  row.goods_store;
				}else if(row.goods_ifspec  == 2){
					// 规格库存之和
					let num = 0;
					for (var i = row.spec_list.length - 1; i >= 0; i--) {
						num += row.spec_list[i].spec_store
					}
					return num;
				}
			},
			//状态
			formatStatus: function (row, column) {
				if(row.goods_status  == 1){
					return  '已上架';
				}else if(row.goods_status  == 2){
					return  '已下架';
				}else{
					return  '已售罄';
				}
			},
			//多规格
			formatSpec: function (row, column) {
				return row.goods_ifspec == 1?"无":"有";
			},
			handleCurrentChange(val) {
				this.page = val;
				this.getGoods(2);
			},
			//获取用户列表
			getGoods(num) {
				if(num == 1)this.page = 1;
				// console.log()
				let params = {
				    headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
				    power_str:this.power_str,
					page: this.page,
					goods_title: this.filters.goods_title,
					goods_classify_value: this.filters.goods_classify_value,
					goods_brand_id:this.filters.goods_brand_id,
					goods_status:this.filters.goods_status
				};

				// // console.log(params);return false;
				this.listLoading = true;
				//NProgress.start();
				getGoodsList(params).then((res) => {
					// console.log(res);return false;
					this.total = res.data.data.count;
					this.users = res.data.data.ems;
					// console.log(this.users);
					this.listLoading = false;
					//NProgress.done();
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			},
			//下架商品
			dropDown: function (index) {
				this.$confirm('确认下架该商品吗?', '提示', {
					type: 'warning'
				}).then(() => {
					this.listLoading = true;
					//NProgress.start();
					let para = { 
								headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
							    user_id:this.user_id,
				    			power_str:this.power_str,
								goods_id: index 
							};
					dropDownGoods(para).then((res) => {
						this.listLoading = false;
						// console.log(res);
						if(res.data.code == 0){
							//NProgress.done();
							this.$message({
								message: '下架成功',
								type: 'success'
							});
							this.getGoods();
						}else{
							this.$message({
								message: res.data.msg,
								type: 'warning'
							});
						}
							
					});
				}).catch((error) =>{
					// sessionStorage.removeItem('user');
					// this.$router.push('/login');
				});
			},
			//上架商品
			dropUp: function (index) {
				this.$confirm('确认重新上架该商品吗?', '提示', {
					type: 'warning'
				}).then(() => {
					this.listLoading = true;
					//NProgress.start();
					let para = { 
								headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
							    user_id:this.user_id,
				    			power_str:this.power_str,
								goods_id: index 
							};
					dropUpGoods(para).then((res) => {
						this.listLoading = false;
						// console.log(res);
						if(res.data.code == 0){
							//NProgress.done();
							this.$message({
								message: '上架成功',
								type: 'success'
							});
							this.getGoods();
						}else{
							this.$message({
								message: res.data.msg,
								type: 'warning'
							});
						}
							
					});
				}).catch((error) =>{
					// sessionStorage.removeItem('user');
					// this.$router.push('/login');
				});
			},
			//上架商品
			handleDel: function (index) {
				this.$confirm('确认删除该商品的相关内容吗?', '提示', {
					type: 'warning'
				}).then(() => {
					this.listLoading = true;
					//NProgress.start();
					let para = { 
								headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
							    user_id:this.user_id,
				    			power_str:this.power_str,
								goods_id: index 
							};
					delGoods(para).then((res) => {
						this.listLoading = false;
						// console.log(res);
						if(res.data.code == 0){
							//NProgress.done();
							this.$message({
								message: '商品删除成功',
								type: 'success'
							});
							this.getGoods();
						}else{
							this.$message({
								message: res.data.msg,
								type: 'warning'
							});
						}
							
					});
				}).catch((error) =>{
					// sessionStorage.removeItem('user');
					// this.$router.push('/login');
				});
			},
			// 查询所有职位和部门信息
			findDeRole:function(){
				let para = {
					headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
	    			power_str:this.power_str
				}
				// 查询部门信息
				getClaAndBr(para).then((res) => {
					// console.log(res);return false;
					// 关闭加载
					this.listLoading = false;
					this.brand_options = res.data.data.brand
					
					// 整理职位信息
					this.class_options = res.data.data.classify;
					console.log(res);
					
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			},
			//显示新增界面
			handleAdd: function(){
				this.$router.push({name:'添加商品',params:{}});
			},
			//显示编辑界面
			handleEdit: function(id){
				this.$router.push({name:'编辑商品',params:{id:id}});
			}
		},
		mounted() {
			this.getGoods(1);
			this.findDeRole();
		}
	};

</script>

<style scoped>

</style>