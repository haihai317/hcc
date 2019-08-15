<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
				<el-form-item>
					<el-input v-model="filters.name" placeholder="职位名称"></el-input>
				</el-form-item>
				<el-form-item>
					<el-button type="primary" v-on:click="getRoles(1)">查询</el-button>
				</el-form-item>
				<el-form-item>
					<el-button type="success" @click="handleAdd">新建职位</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="roles" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
			<el-table-column type="selection" width="55">
			</el-table-column>
			<el-table-column type="role_id" width="60">
			</el-table-column>
			<el-table-column prop="role_name" label="职位名称" >
			</el-table-column>
			<el-table-column prop="role_code" label="职位代码" >
			</el-table-column>
			<el-table-column prop="role_status" label="职位状态"	:formatter="formStatus" >
			</el-table-column>
			<el-table-column prop="role_level" label="职位等级" >
			</el-table-column>
			<el-table-column label="操作">
				<template slot-scope="scope">
					<el-button type="text" size="small" @click="handleEdit(scope.row)">编辑</el-button>
					<el-button type="text" size="small" @click="openPower(scope.row.role_id,scope.row.role_name)">修改权限</el-button>
					<!-- <el-button type="text" size="small" @click="handleNo(scope.row.role_id)">禁用</el-button> -->
					<el-button type="text" size="small" @click="handleDel(scope.row.role_id)">删除</el-button>
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
		<el-dialog title="修改职位信息" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
				<el-form-item label="职位名称" prop="role_name">
					<el-input v-model="editForm.role_name"></el-input>
				</el-form-item>

				<el-form-item label="职位状态" prop="role_status">
				      	<el-radio v-model="editForm.role_status" label="1">启用</el-radio>
				      	<el-radio v-model="editForm.role_status" label="2">禁用</el-radio>
				</el-form-item>

				<el-form-item label="职位编码">
					<el-input v-model="editForm.role_code" ></el-input>
				</el-form-item>

				<!-- <el-form-item label="职位等级">
					<el-input v-model.number="addForm.role_level" placeholder="以数字标识"></el-input>
				</el-form-item> -->
			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="editFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="editSubmit" :loading="editLoading">提交</el-button>
			</div>
		</el-dialog>

		<!--新增界面-->
		<el-dialog title="添加新职位" :visible.sync="addFormVisible" :close-on-click-modal="false">
			<el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
				<el-form-item label="职位名称" prop="role_name">
					<el-input v-model="addForm.role_name"></el-input>
				</el-form-item>

				<el-form-item label="职位状态" prop="role_status">
				      	<el-radio v-model="addForm.role_status" label="1">启用</el-radio>
				      	<el-radio v-model="addForm.role_status" label="2">禁用</el-radio>
				</el-form-item>

				<el-form-item label="职位编码">
					<el-input v-model="addForm.role_code" ></el-input>
				</el-form-item>

				<!-- <el-form-item label="职位等级">
					<el-input v-model.number="addForm.role_level" placeholder="以数字标识"></el-input>
				</el-form-item> -->

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
	import { getRoleListPage, delRole,  editRole, addRole,getDeAndRole } from '../../api/api';

	export default {
		data() {
			return {
				filters: {
					name: ''
				},
				roles: [],
				total: 0,
				page: 1,
				listLoading: false,
				sels: [],//列表选中列

				editFormVisible: false,//编辑界面是否显示
				editLoading: false,
				editFormRules: {
					role_name: [
						{ required: true, message: '请输入职位名称', trigger: 'blur' },
						{ min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur' }
					],
					role_status:[
						{ required: true, message: '请选择职位状态', trigger: 'change' }
					]
				},
				//编辑界面数据
				editForm: {
					role_name: '',
					role_code: '',
					role_status:1
				},

				addFormVisible: false,//新增界面是否显示
				addLoading: false,
				addFormRules: {
					role_name: [
						{ required: true, message: '请输入职位名称', trigger: 'blur' },
						{ min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur' }
					],
					role_status:[
						{ required: true, message: '请选择职位状态', trigger: 'change' }
					]
				},
				//新增界面数据
				addForm: {
					role_name: '',
					role_code: '',
					role_status:1
				},
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/role'

			}
		},
		methods: {
			//状态显示转换
			formStatus: function (row, column) {
				return row.role_status == 1 ? '已启用' : '已禁用';
			},
			
			handleCurrentChange(val) {
				this.page = val;
				this.getRoles(2);
			},
			//获取用户列表
			getRoles(num) {
				if(num == 1)this.page = 1;
				let params = {
				    headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
				    power_str:this.power_str,
					page: this.page,
					name:this.filters.name
				};

				this.listLoading = true;
				getRoleListPage(params).then((res) => {
					// console.log(res);
					this.total = res.data.data.total;
					this.roles = res.data.data.roles;
					// console.log(this.roles);
					this.listLoading = false;
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			},
			//删除
			handleDel: function (index) {
				this.$confirm('确认删除该职位吗?', '提示', {
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
					delRole(para).then((res) => {
						this.listLoading = false;
						// console.log(res);
						if(res.data.code == 0){
							this.$message({
								message: '删除成功',
								type: 'success'
							});
						}else{
							alert(res.data.msg);
						}
							
						this.getRoles();
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
				this.editForm.role_status = this.editForm.role_status+'';
			},
			//显示新增界面
			handleAdd: function () {
				// 加载效果
				this.addFormVisible = true;
				// 重置新增员工选项
				this.addForm = {
					role_name: '',
					role_code: '',
					role_status:"1"
				};
			},
			//编辑
			editSubmit: function () {
				this.$refs.editForm.validate((valid) => {
					if (valid) {
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							this.editLoading = true;
							//NProgress.start();
							let para = {
								headers:{'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
								user_id:this.user_id,
				    			power_str:this.power_str,
								role_id:this.editForm.role_id,
								role_name: this.editForm.role_name,
								role_code: this.editForm.role_code,
								role_status:parseInt(this.editForm.role_status)
							}
							editRole(para).then((res) => {
								this.editLoading = false;
								if(res.data.code == 0){
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs['editForm'].resetFields();
									this.editFormVisible = false;
									this.getRoles();
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
			//新增
			addSubmit: function () {
				// console.log(this.addForm);
				this.$refs.addForm.validate((valid) => {
					if (valid) {
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							this.addLoading = true;
							//NProgress.start();
							// let para = Object.assign({}, this.addForm);
							let para = {
								headers:{
									'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
								},
								user_id:this.user_id,
				    			power_str:this.power_str,
								role_name:this.addForm.role_name,
								role_code:this.addForm.role_code,
								role_status:this.addForm.role_status
							};
							addRole(para).then((res) => {
								if(res.data.code == 0){
									// console.log(res);
									this.addLoading = false;
									//NProgress.done();
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs.addForm.resetFields();
									this.addFormVisible = false;
									this.getRoles();
								}else{
									this.addLoading = false;
									alert('提交操作失败，请重试或联系开发人员！');
								}
									
							}).catch((error) =>{
								sessionStorage.removeItem('user');
								this.$router.push('/login');
							});
						});
							
					}else{
						alert('请将表单填写完整')
					}
				});
			},
			selsChange: function (sels) {
				this.sels = sels;
			},
			// 打开权限设置页面
			openPower:function(id,name){
				// // console.log(id);
				this.$router.push({name:'职位授权',params:{id:id,role_name:name}});
			}
		},
		mounted() {
			this.getRoles(1);
		}
	};

</script>

<style scoped>

</style>