<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
				<el-form-item>
					<el-input v-model="filters.name" placeholder="公司名称"></el-input>
				</el-form-item>
				<el-form-item>
					<el-button type="primary" v-on:click="getComs(1)">查询</el-button>
				</el-form-item>
				<el-form-item>
					<el-button type="success" @click="handleAdd">新增</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="coms" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
			<el-table-column type="selection" width="55">
			</el-table-column>
			<el-table-column type="co_id" width="60">
			</el-table-column>
			<el-table-column prop="co_name" label="公司全称" >
			</el-table-column>
			<el-table-column prop="co_alias" label="公司简称" >
			</el-table-column>
			<el-table-column prop="co_code" label="公司代码" >
			</el-table-column>
			<el-table-column prop="co_tel" label="公司电话" >
			</el-table-column>
			<el-table-column prop="co_addr" label="公司地址" >
			</el-table-column>
			<el-table-column prop="co_remark" label="备注" >
			</el-table-column>
			<el-table-column label="操作" fixed="right">
				<template slot-scope="scope">
					<el-button size="small" @click="handleEdit(scope.row)">编辑</el-button>
					<el-button type="danger" size="small" @click="handleDel(scope.row.co_id)">删除</el-button>
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
		<el-dialog title="编辑子公司信息" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
				<el-form-item label="公司名称" prop="co_name">
					<el-input v-model="editForm.co_name"></el-input>
				</el-form-item>
				<el-form-item label="公司简称" prop="co_alias">
					<el-input v-model="editForm.co_alias"></el-input>
				</el-form-item>
				<el-form-item label="公司代码" prop="co_code">
					<el-input v-model="editForm.co_code"></el-input>
				</el-form-item>
				<el-form-item label="联系电话" prop="co_tel">
					<el-input v-model="editForm.co_tel" ></el-input>
				</el-form-item>
				<el-form-item label="公司地址" prop="co_addr">
					<el-input v-model="editForm.co_addr" ></el-input>
				</el-form-item>
				<el-form-item label="备注" prop="co_remark">
					<el-input v-model="editForm.co_remark" ></el-input>
				</el-form-item>
			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="editFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="editSubmit" :loading="editLoading">提交</el-button>
			</div>
		</el-dialog>

		<!--新增界面-->
		<el-dialog title="添加新的子公司" :visible.sync="addFormVisible" :close-on-click-modal="false">
			<el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
				<el-form-item label="公司名称" prop="co_name">
					<el-input v-model="addForm.co_name"></el-input>
				</el-form-item>
				<el-form-item label="公司简称" prop="co_alias">
					<el-input v-model="addForm.co_alias"></el-input>
				</el-form-item>
				<el-form-item label="公司代码" prop="co_code">
					<el-input v-model="addForm.co_code"></el-input>
				</el-form-item>
				<el-form-item label="联系电话" prop="co_tel">
					<el-input v-model="addForm.co_tel" ></el-input>
				</el-form-item>
				<el-form-item label="公司地址" prop="co_addr">
					<el-input v-model="addForm.co_addr" ></el-input>
				</el-form-item>
				<el-form-item label="备注" prop="co_remark">
					<el-input v-model="addForm.co_remark" ></el-input>
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
	import { getComList, editCompany, addCompany, delCompany } from '../../api/api';

	export default {
		data() {
			return {
				filters: {
					name: ''
				},
				coms: [],
				total: 0,
				page: 1,
				listLoading: false,
				sels: [],//列表选中列

				editFormVisible: false,//编辑界面是否显示
				editLoading: false,
				editFormRules: {
					co_name: [
						{ required: true, message: '请输入公司名称', trigger: 'blur' }
					]
				},
				//编辑界面数据
				editForm: {
					co_id:0,
					co_name: '',
					co_alias: '',
					co_code: '',
					co_tel: '',
					co_addr: '',
					co_remark: ''
				},

				addFormVisible: false,//新增界面是否显示
				addLoading: false,
				addFormRules: {
					co_name: [
						{ required: true, message: '请输入公司名称', trigger: 'blur' }
					]
				},
				//新增界面数据
				addForm: {
					co_name: '',
					co_alias: '',
					co_code: '',
					co_tel: '',
					co_addr: '',
					co_remark: ''
				},
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/company'

			}
		},
		methods: {
			handleCurrentChange(val) {
				this.page = val;
				this.getComs(2);
			},
			//获取用户列表
			getComs(num) {
				if(num == 1)this.page = 1;
				let params = {
				    headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
				    power_str:this.power_str,
					page: this.page,
					name: this.filters.name
				};

				// // console.log(params);return false;
				this.listLoading = true;
				//NProgress.start();
				getComList(params).then((res) => {
					this.total = res.data.data.count;
					this.coms = res.data.data.coms;
					// console.log(this.coms);
					this.listLoading = false;
					//NProgress.done();
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			},
			//显示编辑界面
			handleEdit: function (row) {
				this.editFormVisible = true;
				this.editForm = Object.assign({}, row);
			},
			//显示新增界面
			handleAdd: function () {
				// 重置新增员工选项
				this.addForm = {
					co_name: '',
					co_alias: '',
					co_code: '',
					co_tel: '',
					co_addr: '',
					co_remark: ''
				};
				// 打开dialog
				this.addFormVisible = true;
			},
			//编辑
			editSubmit: function () {
				this.$refs.editForm.validate((valid) => {
					if (valid) {
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							this.editLoading = true;
							let para = {
							    headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
							    user_id:this.user_id,
				    			power_str:this.power_str,
							    co_id: this.editForm.co_id,
							    co_name: this.editForm.co_name,
								co_alias: this.editForm.co_alias,
								co_code: this.editForm.co_code,
								co_tel: this.editForm.co_tel,
								co_addr: this.editForm.co_addr,
								co_remark: this.editForm.co_remark
							};
							editCompany(para).then((res) => {
								if(res.data.code == 0){
									// console.log(res);
									this.editLoading = false;
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs['editForm'].resetFields();
									this.editFormVisible = false;
									this.getComs();
								}else{
									alert(res.data.msg);
									this.editLoading = false;
									this.getComs();
								}
							}).catch((error) =>{
								sessionStorage.removeItem('user');
								this.$router.push('/login');
							});
						});
					}else{
						alert('请完善表单');
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
							let para = {
							    headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
							    user_id:this.user_id,
				    			power_str:this.power_str,
							    co_name: this.addForm.co_name,
								co_alias: this.addForm.co_alias,
								co_code: this.addForm.co_code,
								co_tel: this.addForm.co_tel,
								co_addr: this.addForm.co_addr,
								co_remark: this.addForm.co_remark
							};
							addCompany(para).then((res) => {
								if(res.data.code == 0){
									// console.log(res);
									this.addLoading = false;
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs['addForm'].resetFields();
									this.addFormVisible = false;
									this.getComs();
								}else{
									alert(res.data.msg);
									this.addFormVisible = false;
									this.getComs();
								}		
							}).catch((error) =>{
								sessionStorage.removeItem('user');
								this.$router.push('/login');
							});
								
						});
							
					}else{
						// console.log('请将表单填写完整')
					}
				});
			},
			selsChange: function (sels) {
				this.sels = sels;
			},
			//删除
			handleDel: function (index) {
				this.$confirm('确认删除该子公司信息吗?', '提示', {
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
					delCompany(para).then((res) => {
						console.log(res);
						if(res.data.code == 0){
							this.listLoading = false;
							this.$message({
								message: '删除成功',
								type: 'success'
							});
							this.getComs();
						}else{
							this.listLoading = false;
							alert(res.data.msg);
						}
							
					}).catch((error) =>{
						sessionStorage.removeItem('user');
						this.$router.push('/login');
					});
				}).catch((error) => {
					// console.log(error);
				});
			}
		},
		mounted() {
			this.getComs(1);
		}
	};

</script>

<style scoped>

</style>