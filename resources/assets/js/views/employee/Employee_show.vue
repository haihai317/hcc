<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
				<el-form-item>
					<el-input v-model="filters.name" placeholder="姓名"></el-input>
				</el-form-item>
				<el-form-item>
					<!-- 级联选择器 -->
					<el-cascader
				    expand-trigger="hover"
				    :options="de_options"
				    v-model="filters.em_de_value"
				    placeholder="所属部门"
				    >
				  	</el-cascader>
				</el-form-item>	
				<el-form-item>
					<el-button type="primary" v-on:click="getUsers(1)">查询</el-button>
				</el-form-item>
				<el-form-item>
					<el-button type="success" @click="handleAdd">新增</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="users" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
			<el-table-column type="selection" width="55">
			</el-table-column>
			<el-table-column type="em_id" width="60">
			</el-table-column>
			<el-table-column prop="em_name" label="姓名" >
			</el-table-column>
			<el-table-column prop="em_tel" label="联系电话" >
			</el-table-column>
			<el-table-column prop="role_name" label="职位" >
			</el-table-column>
			<el-table-column prop="de_name" label="所属部门" >
			</el-table-column>
			<el-table-column prop="co_name" label="所在公司" >
			</el-table-column>
			<el-table-column label="操作" fixed="right">
				<template slot-scope="scope">
					<el-button size="small" @click="handleEdit(scope.row)">编辑</el-button>
					<el-button type="danger" size="small" @click="handleDel(scope.row.em_id)">删除</el-button>
				</template>
			</el-table-column>
		</el-table>

		<!--工具条-->
		<el-col :span="24" class="toolbar">
			<!-- <el-button type="danger" @click="batchRemove" :disabled="this.sels.length===0">批量删除</el-button> -->
			<el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="10" :total="total" style="float:right;">
			</el-pagination>
		</el-col>

		<!--编辑界面-->
		<el-dialog title="编辑员工信息" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
				<el-form-item label="员工姓名" prop="em_name">
					<el-input v-model="editForm.em_name"></el-input>
				</el-form-item>
				<el-form-item label="联系电话" prop="em_tel">
					<el-input v-model="editForm.em_tel" ></el-input>
				</el-form-item>
				<el-form-item label="所属部门" prop="em_de_value">
					<!-- 级联选择器 -->
					<el-cascader
				    expand-trigger="hover"
				    :options="de_options"
				    v-model="editForm.em_de_value"
				    >
				  	</el-cascader>
				</el-form-item>	
				<el-form-item label="职位"  prop="em_role_id">
					<el-select v-model="editForm.em_role_id" placeholder="请选择">
					    <el-option
					      v-for="item in role_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
				  	</el-select>
				</el-form-item>

			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="editFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="editSubmit" :loading="editLoading">提交</el-button>
			</div>
		</el-dialog>

		<!--新增界面-->
		<el-dialog title="添加新员工" :visible.sync="addFormVisible" :close-on-click-modal="false">
			<el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
				<el-form-item label="员工姓名" prop="em_name">
					<el-input v-model="addForm.em_name"></el-input>
				</el-form-item>
				<el-form-item label="联系电话" prop="em_tel">
					<el-input v-model="addForm.em_tel" ></el-input>
				</el-form-item>
				<el-form-item label="登录密码" prop="em_password">
					<el-input v-model="addForm.em_password" show-password></el-input>
				</el-form-item>
				<el-form-item label="所属部门" prop="em_de_value">
					<!-- 级联选择器 -->
					<el-cascader
				    expand-trigger="hover"
				    :options="de_options"
				    v-model="addForm.em_de_value"
				    >
				  	</el-cascader>
				</el-form-item>	
				<el-form-item label="职位"  prop="em_role_id">
					<el-select v-model="addForm.em_role_id" placeholder="请选择">
					    <el-option
					      v-for="item in role_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
				  	</el-select>
				</el-form-item>

			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="addFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="addSubmit" :loading="addLoading">提交</el-button>
			</div>
		</el-dialog>
	</section>
</template>

<script>
	import util from '../../common/js/util'
	//import NProgress from 'nprogress'
	import { getEmployeeListPage, delEm, editEm, addEmployee,getDeAndRole } from '../../api/api';

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
				sels: [],//列表选中列

				editFormVisible: false,//编辑界面是否显示
				editLoading: false,
				editFormRules: {
					em_name: [
						{ required: true, message: '请输入姓名', trigger: 'blur' }
					],
					em_tel: [
						{ required: true, message: '请输入员工电话', trigger: 'blur' }
					],
					em_role_id: [
						{ required: true, message: '请选择职位', trigger: 'change' }
					],
					em_de_value: [
						{ required: true, message: '请选择部门', trigger: 'change' }
					]
				},
				//编辑界面数据
				editForm: {
					em_name: '',
					em_tel: '',
					em_password:'',
					em_role_id:"",
					em_de_value:[]
				},

				addFormVisible: false,//新增界面是否显示
				addLoading: false,
				addFormRules: {
					em_name: [
						{ required: true, message: '请输入姓名', trigger: 'blur' }
					],
					em_tel: [
						{ required: true, message: '请输入员工电话', trigger: 'blur' }
					],
					em_password: [
						{ required: true, message: '请输入员工登录密码', trigger: 'blur' }
					],
					em_role_id: [
						{ required: true, message: '请选择职位', trigger: 'change' }
					],
					em_de_value: [
						{ required: true, message: '请选择部门', trigger: 'change' }
					]
				},
				//新增界面数据
				addForm: {
					em_name: '',
					em_tel: '',
					em_password:'',
					em_role_id:"",
					em_de_value:[]
				},

				// 新增员工提供的公司部门选择器
				de_options:[],
				role_options:[],
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/employee'

			}
		},
		methods: {
			//性别显示转换
			formatSex: function (row, column) {
				return row.sex == 1 ? '男' : row.sex == 0 ? '女' : '未知';
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
					name: this.filters.name,
					em_de_value: this.filters.em_de_value
				};

				// // console.log(params);return false;
				this.listLoading = true;
				//NProgress.start();
				getEmployeeListPage(params).then((res) => {
					// this.total = res.data.total;
					this.users = res.data.data.ems;
					// console.log(this.users);
					this.listLoading = false;
					//NProgress.done();
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			},
			//删除
			handleDel: function (index) {
				this.$confirm('确认删除该记录吗?', '提示', {
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
								id: index 
							};
					delEm(para).then((res) => {
						this.listLoading = false;
						//NProgress.done();
						this.$message({
							message: '删除成功',
							type: 'success'
						});
						this.getUsers();
					});
				}).catch((error) =>{
					// sessionStorage.removeItem('user');
					// this.$router.push('/login');
				});
			},
			//显示编辑界面
			handleEdit: function (row) {
				this.editFormVisible = true;
				this.editForm = Object.assign({}, row);
				this.editForm.em_de_value = [row.em_co_id,row.em_de_id];
				// // console.log(this.de_options);
				// // console.log(this.editForm.em_de_value );
			},
			//编辑
			editSubmit: function () {
				this.$refs.editForm.validate((valid) => {
					if (valid) {
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							this.editLoading = true;
							//NProgress.start();
							let para = {
								headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
							    user_id:this.user_id,
				    			power_str:this.power_str,
							    em_name:this.editForm.em_name,
							    em_id:this.editForm.em_id,
								em_tel:this.editForm.em_tel,
								em_role_id:this.editForm.em_role_id,
								em_de_value:this.editForm.em_de_value
							}

							editEm(para).then((res) => {
								this.editLoading = false;
								this.editFormVisible = false;
								if(res.data.code == 0){
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs['editForm'].resetFields();
									this.getUsers();
								}else{
									alert(res.data.msg);
								}
									
							}).catch((error) =>{
								sessionStorage.removeItem('user');
								this.$router.push('/login');
							});
						});
					}
				});
			},
			//显示新增界面
			handleAdd: function () {
				// 加载效果
				this.listLoading = true;
				// 重置新增员工选项
				this.addForm = {
					em_name: '',
					em_tel: '',
					em_password: '',
					em_role_id:"",
					em_de_value:[]
				};
				// 打开dialog
				this.addFormVisible = true;
			},
			//新增
			addSubmit: function () {
				// console.log(this.addForm);
				this.$refs.addForm.validate((valid) => {
					if (valid) {
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							this.addLoading = true;
							//NProgress.start();
							let para = {
								headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
							    user_id:this.user_id,
				    			power_str:this.power_str,
							    em_name:this.addForm.em_name,
								em_tel:this.addForm.em_tel,
								em_password:this.addForm.em_password,
								em_role_id:this.addForm.em_role_id,
								em_de_value:this.addForm.em_de_value
							}
							addEmployee(para).then((res) => {
								// console.log(res);
								this.addLoading = false;
								if(res.data.code == 0){
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs['addForm'].resetFields();
									this.addFormVisible = false;
									this.getUsers();
								}else{
									alert(res.data.msg);
								}
									
							}).catch((error) =>{
								sessionStorage.removeItem('user');
								this.$router.push('/login');
							});
						});
							
					}
				});
			},
			selsChange: function (sels) {
				this.sels = sels;
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
				getDeAndRole(para).then((res) => {
					// // console.log(res);return false;
					// 关闭加载
					this.listLoading = false;
					let arr = res.data.data.des
					// console.log(arr);
					// 以下将获取到的公司部门信息整理为联动选择器需要的数据
					for (var i = arr.length - 1; i >= 0; i--) {
						let children = [];
						let haha = arr[i].children
						for (var j = haha.length - 1; j >= 0; j--) {
							let hehe = {value:haha[j].de_id,label:haha[j].de_name};
							children.push(hehe);
						}
						let smalljson = {value:arr[i].value,label:arr[i].label,children:children}
						this.de_options.push(smalljson); 
					}
					// console.log(this.de_options);

					// 整理职位信息
					this.role_options = res.data.data.roles;
					// console.log(res.data.data.roles);
					
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			}
		},
		mounted() {
			this.getUsers(1);
			this.findDeRole();
		}
	};

</script>

<style scoped>

</style>