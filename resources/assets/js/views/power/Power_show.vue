<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" >
				<el-form-item>
					<el-button type="success" @click="handleAdd">新增权限</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="powers" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
			<el-table-column type="selection" width="55">
			</el-table-column>
			<el-table-column type="power_id" width="60">
			</el-table-column>
			<el-table-column prop="power_name" label="权限名称" >
			</el-table-column>
			<el-table-column  label="权限结构" >
				<template slot-scope="scope">
			        <span v-if="scope.row.power_type == 2">|———{{ scope.row.power_name }}</span>
			        <span v-else>{{ scope.row.power_name }}</span>
			     </template>
			</el-table-column>
			<el-table-column prop="power_url" label="路由" >
			</el-table-column>
			<el-table-column prop="power_status" label="状态" :formatter="formStatus" >
			</el-table-column>
			<el-table-column prop="power_type" label="类型" :formatter="formType" >
			</el-table-column>
			<el-table-column prop="power_sort" label="排序" >
			</el-table-column>
			<el-table-column prop="power_remark" label="备注" >
			</el-table-column>
			<el-table-column label="操作" fixed="right" width="170">
				<template slot-scope="scope">
					<el-button type="primary" size="small" @click="handleEdit(scope.row)">编辑</el-button>
					<!-- <el-button type="text" size="small" @click="handleDel(scope.$index, scope.row)">禁用</el-button> -->
					<el-button type="warning" size="small" @click="handleDel(scope.row.power_id)">删除</el-button>
				</template>
			</el-table-column>
		</el-table>

		<!--工具条-->
		<el-col :span="24" class="toolbar">
			<!-- <el-button type="danger" @click="batchRemove" :disabled="this.sels.length===0">批量删除</el-button> -->
			<!-- <el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="20" :total="total" style="float:right;"> -->
			<!-- </el-pagination> -->
		</el-col>


		<!--编辑界面-->
		<el-dialog title="编辑权限信息" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
				<el-form-item label="权限名称" prop="power_name">
					<el-input v-model="editForm.power_name"></el-input>
				</el-form-item>

				<el-form-item label="权限状态" prop="power_status">
				      	<el-radio v-model="editForm.power_status" label="1">启用</el-radio>
				      	<el-radio v-model="editForm.power_status" label="2">禁用</el-radio>
				</el-form-item>

				<el-form-item label="权限路由" prop="power_url">
					<el-input v-model="editForm.power_url" ></el-input>
				</el-form-item>

				<el-form-item label="父模块" >
					<el-select v-model="editForm.power_parent_id" :disabled="edit_parent_hide" placeholder="请选择">
					    <el-option
					      v-for="item in parent_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
				 	</el-select>
				</el-form-item>
				<el-form-item label="权限排序" prop="power_sort">
					<el-input v-model.number="editForm.power_sort"></el-input>
				</el-form-item>

				<el-form-item label="权限备注">
					<el-input v-model="editForm.power_remark" ></el-input>
				</el-form-item>
			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="editFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="editSubmit" :loading="editLoading">提交</el-button>
			</div>
		</el-dialog>

		<!--新增界面-->
		<el-dialog title="添加新权限" :visible.sync="addFormVisible" :close-on-click-modal="false">
			<el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
				<el-form-item label="权限名称" prop="power_name">
					<el-input v-model="addForm.power_name"></el-input>
				</el-form-item>

				<el-form-item label="权限状态" prop="power_status">
				      	<el-radio v-model="addForm.power_status" label="1">启用</el-radio>
				      	<el-radio v-model="addForm.power_status" label="2">禁用</el-radio>
				</el-form-item>

				<el-form-item label="权限路由" prop="power_url">
					<el-input v-model="addForm.power_url" ></el-input>
				</el-form-item>

				<el-form-item label="权限类型" prop="power_type" :change="showParent()">
				      	<el-radio v-model="addForm.power_type" label="1">模块</el-radio>
				      	<el-radio v-model="addForm.power_type" label="2">节点</el-radio>
				</el-form-item>

				<el-form-item label="父模块" >
					<el-select v-model="addForm.power_parent_id" :disabled="parent_hide" placeholder="请选择">
					    <el-option
					      v-for="item in parent_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
				 	</el-select>
				</el-form-item>

				

				<el-form-item label="权限排序" prop="power_sort">
					<el-input v-model.number="addForm.power_sort"></el-input>
				</el-form-item>

				<el-form-item label="权限备注">
					<el-input v-model="addForm.power_remark" ></el-input>
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
	import { getPowerListPage, delPower, editPower, addPower,getParents } from '../../api/api';

	export default {
		data() {
			return {
				powers: [],
				total: 0,
				page: 1,
				listLoading: false,
				sels: [],//列表选中列

				editFormVisible: false,//编辑界面是否显示
				editLoading: false,
				editFormRules: {
					power_name: [
						{ required: true, message: '请输入权限名称', trigger: 'blur' },
						{ min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur' }
					],
					power_status:[
						{ required: true, message: '请选择权限状态', trigger: 'change' }
					],
					power_url:[
						{ required: true, message: '请输入权限路由', trigger: 'blur' }
					],
					power_type:[
						{ required: true, message: '请选择权限类型', trigger: 'change' }
					],
					power_sort:[
						{ required: true, message: '请填写权限显示排序', trigger: 'blur' },
						{ type: 'number', message: '排序必须为数字值'  }
					]
				},
				//编辑界面数据
				editForm: {
					power_name: '',
					power_status:1,
					power_parent_id:'',
					power_remark:'',
					power_sort:null
				},

				addFormVisible: false,//新增界面是否显示
				addLoading: false,
				addFormRules: {
					power_name: [
						{ required: true, message: '请输入权限名称', trigger: 'blur' },
						{ min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur' }
					],
					power_status:[
						{ required: true, message: '请选择权限状态', trigger: 'change' }
					],
					power_url:[
						{ required: true, message: '请输入权限路由', trigger: 'blur' }
					],
					power_type:[
						{ required: true, message: '请选择权限类型', trigger: 'change' }
					],
					power_sort:[
						{ required: true, message: '请填写权限显示排序', trigger: 'blur' },
						{ type: 'number', message: '排序必须为数字值'  }
					]
				},
				//新增界面数据
				addForm: {
					power_name: '',
					power_type: 1,
					power_status:1,
					power_parent_id:'',
					power_remark:'',
					power_sort:null
				},
				parent_options:[
					
				],
				parent_hide:true,
				edit_parent_hide:true,
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/power'

			}
		},
		methods: {
			//状态显示转换
			formStatus: function (row, column) {
				return row.power_status == 1 ? '已启用' : '已禁用';
			},
			//类型显示转换
			formType: function (row, column) {
				return row.power_type == 1 ? '模块' : '节点';
			},
			// 当选择了节点类型后，显示父节点选择框
			showParent:function(){
				if(this.addForm.power_type == 2){
					this.parent_hide = false
				}else{
					this.parent_hide = true
					this.addForm.power_parent_id = ''
				}
			},
			//获取用户列表
			getPowers() {
				let params = {
				    headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
				    power_str:this.power_str,
				};

				// // console.log(params);return false;
				this.listLoading = true;
				//NProgress.start();
				getPowerListPage(params).then((res) => {
					this.powers = res.data.data;
					// console.log(this.powers);
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
					 	id: index,
					 	user_id:this.user_id,
				    	power_str:this.power_str,
					};
					delPower(para).then((res) => {
						// console.log(res);
						this.listLoading = false;
						if(res.data.code == 0){
							this.$message({
								message: '删除成功',
								type: 'success'
							});
							this.getPowers();
						}else{
							alert(res.data.msg);
						}
							
					}).catch((error) =>{
						sessionStorage.removeItem('user');
						this.$router.push('/login');
					});
				}).catch((error) => {
					// console.log(error);
				});
			},
			//显示编辑界面
			handleEdit: function (row) {
				this.editFormVisible = true;
				this.editForm = Object.assign({}, row);
				this.editForm.power_status = this.editForm.power_status + '';
				// console.log(this.editForm.power_parent_id);
				if(this.editForm.power_parent_id == 0){
					this.editForm.power_parent_id = '无';
					this.edit_parent_hide = true;
				}else{
					this.edit_parent_hide = false;
				}
				// console.log(this.edit_parent_hide);
				let para = {
					headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				 	user_id:this.user_id,
			    	power_str:this.power_str
				};
				getParents(para).then((res) => {
					this.addLoading = false;
					//NProgress.done();
					this.parent_options = res.data.data;
					this.addLoading = false;
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			},
			//显示新增界面
			handleAdd: function () {
				// 加载效果
				this.addFormVisible = true;
				// 重置新增员工选项
				this.addForm = {
					power_name: '',
					power_type: "1",
					power_status:"1",
					power_parent_id:'',
					power_remark:'',
					power_sort:null
				};
				let para = {
					headers:{
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					user_id:this.user_id,
	    			power_str:this.power_str
				};
				getParents(para).then((res) => {
					this.addLoading = false;
					//NProgress.done();
					this.parent_options = res.data.data;
					// console.log(this.addForm);
					this.addLoading = false;
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			},
			//编辑
			editSubmit: function () {
				this.$refs.editForm.validate((valid) => {
					if (valid) {
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							this.editLoading = true;
							//NProgress.start();
							let para = {
								headers:{
									'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
								},
								user_id:this.user_id,
				    			power_str:this.power_str,
								power_id:this.editForm.power_id,
								power_name:this.editForm.power_name,
								power_status:parseInt(this.editForm.power_status),
								power_url:this.editForm.power_url,
								power_remark:this.editForm.power_remark,
								power_sort:this.editForm.power_sort,
								power_parent_id:parseInt(this.editForm.power_parent_id)
							};

							editPower(para).then((res) => {
								// console.log(res);
								if(res.data.code == 0){
									this.editLoading = false;
									//NProgress.done();
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs.editForm.resetFields();
									this.editFormVisible = false;
									this.getPowers();
								}else{
									this.editLoading = false;
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
								power_name:this.addForm.power_name,
								power_type:parseInt(this.addForm.power_type),
								power_status:parseInt(this.addForm.power_status),
								power_remark:this.addForm.power_remark,
								power_sort:this.addForm.power_sort,
								power_url:this.addForm.power_url,
								power_parent_id:parseInt(this.addForm.power_parent_id)
							};
							addPower(para).then((res) => {
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
									this.getPowers();
								}else{
									this.addLoading = false;
									alert(res.data.msg);
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
			}
		},
		mounted() {
			this.getPowers();
		}
	};

</script>

<style scoped>

</style>