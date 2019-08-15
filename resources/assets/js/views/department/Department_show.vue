<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
				<el-form-item>
					<el-input v-model="filters.name" placeholder="部门名称"></el-input>
				</el-form-item>
				<el-form-item>
					<el-button type="primary" v-on:click="getDeparts(1)">查询</el-button>
				</el-form-item>
				<el-form-item>
					<el-button type="success" @click="handleAdd">新增</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="departs" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
			<el-table-column type="selection" width="55">
			</el-table-column>
			<el-table-column type="de_id" width="60">
			</el-table-column>
			<el-table-column prop="de_name" label="部门名称" >
			</el-table-column>
			<el-table-column prop="de_code" label="部门代码" >
			</el-table-column>
			<el-table-column prop="co_name" label="所属公司" >
			</el-table-column>
			<el-table-column prop="de_status" label="部门状态" :formatter="formStatus" >
			</el-table-column>
			<el-table-column prop="de_remark" label="备注" >
			</el-table-column>
			<el-table-column label="操作" fixed="right">
				<template slot-scope="scope">
					<el-button size="small" @click="handleEdit(scope.row)">编辑</el-button>
					<el-button type="danger" size="small" @click="handleDel(scope.row.de_id)">删除</el-button>
				</template>
			</el-table-column>
		</el-table>

		<!--工具条-->
		<el-col :span="24" class="toolbar">
			<!-- <el-button type="danger" @click="batchRemove" :disabled="this.sels.length===0">批量删除</el-button> -->
			<el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="20" :total="total" style="float:right;">
			</el-pagination>
		</el-col>

		<!--编辑界面-->
		<el-dialog title="编辑部门信息" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
				<el-form-item label="部门名称" prop="de_name">
					<el-input v-model="editForm.de_name"></el-input>
				</el-form-item>
				<el-form-item label="部门排序" prop="de_sort">
					<el-input v-model.number="editForm.de_sort"></el-input>
				</el-form-item>
				<el-form-item label="部门代码" prop="de_code">
					<el-input v-model="editForm.de_code"></el-input>
				</el-form-item>
				<el-form-item label="所属公司" prop="de_co_id">
					<el-select v-model="editForm.de_co_id" placeholder="请选择">
					    <el-option
					      v-for="item in comList"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
				  	</el-select>
				</el-form-item>
				<el-form-item label="备注" prop="de_remark">
					<el-input v-model="editForm.de_remark" ></el-input>
				</el-form-item>
			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="editFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="editSubmit" :loading="editLoading">提交</el-button>
			</div>
		</el-dialog>

		<!--新增界面-->
		<el-dialog title="添加新的部门" :visible.sync="addFormVisible" :close-on-click-modal="false">
			<el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
				<el-form-item label="部门名称" prop="de_name">
					<el-input v-model="addForm.de_name"></el-input>
				</el-form-item>
				<el-form-item label="部门排序" prop="de_sort">
					<el-input v-model.number="addForm.de_sort"></el-input>
				</el-form-item>
				<el-form-item label="部门代码" prop="de_code">
					<el-input v-model="addForm.de_code"></el-input>
				</el-form-item>
				<el-form-item label="所属公司" prop="de_co_id">
					<el-select v-model="addForm.de_co_id" placeholder="请选择">
					    <el-option
					      v-for="item in comList"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
				  	</el-select>
				</el-form-item>
				<el-form-item label="备注" prop="de_remark">
					<el-input v-model="addForm.de_remark" ></el-input>
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
	import { findComPoint , showDeList, editDe, addDe, delDe } from '../../api/api';

	export default {
		data() {
			return {
				filters: {
					name: ''
				},
				departs: [],
				total: 0,
				page: 1,
				listLoading: false,
				sels: [],//列表选中列

				editFormVisible: false,//编辑界面是否显示
				editLoading: false,
				editFormRules: {
					de_name: [
						{ required: true, message: '请输入部门名称', trigger: 'blur' }
					],
					de_co_id: [
						{ required: true, message: '请选择所属公司', trigger: 'blur' }
					],
					de_sort: [
						{ required: true, message: '请输入部门排序数字', trigger: 'blur' },
      					{ type: 'number', message: '序列必须为数字值'}
					]
				},
				//编辑界面数据
				editForm: {
					de_id:'',
					de_name: '',
					de_code: '',
					de_co_id:'',
					de_sort: '',
					de_remark:''
				},

				addFormVisible: false,//新增界面是否显示
				addLoading: false,
				addFormRules: {
					de_name: [
						{ required: true, message: '请输入部门名称', trigger: 'blur' }
					],
					de_co_id: [
						{ required: true, message: '请选择所属公司', trigger: 'blur' }
					],
					de_sort: [
						{ required: true, message: '请输入部门排序数字', trigger: 'blur' },
      					{ type: 'number', message: '序列必须为数字值'}
					]
				},
				//新增界面数据
				addForm: {
					de_name: '',
					de_code: '',
					de_co_id:'',
					de_sort: '',
					de_remark:''
				},
				comList:[],
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/department'

			}
		},
		methods: {
			//状态显示转换
			formStatus: function (row, column) {
				return row.de_status == 1 ? '已启用' : '已禁用';
			},
			handleCurrentChange(val) {
				this.page = val;
				this.getDeparts(2);
			},
			//获取用户列表
			getDeparts(num) {
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

				// console.log(params);return false;
				this.listLoading = true;
				//NProgress.start();
				showDeList(params).then((res) => {
					this.total = res.data.data.count;
					this.departs = res.data.data.des;
					// console.log(this.departs);
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
				if(this.comList.length == 0){
					this.getAllComs();
				}
			},
			//显示新增界面
			handleAdd: function () {
				// 重置新增员工选项
				this.addForm = {
					de_name: '',
					de_code: '',
					de_co_id:'',
					de_sort: '',
					de_remark:''
				};
				// 打开dialog
				this.addFormVisible = true;
				this.addLoading = true;
				this.getAllComs();
				
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
							    de_id:this.editForm.de_id,
							    de_name: this.editForm.de_name,
								de_code: this.editForm.de_code,
								de_co_id: this.editForm.de_co_id,
								de_sort: this.editForm.de_sort,
								de_remark: this.editForm.de_remark
							};
							editDe(para).then((res) => {
								if(res.data.code == 0){
									// console.log(res);
									this.editLoading = false;
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs['editForm'].resetFields();
									this.editFormVisible = false;
									this.getDeparts();
								}else{
									alert(res.data.msg);
									this.editLoading = false;
									this.getDeparts();
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
							let para = {
							    headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
							    user_id:this.user_id,
				    			power_str:this.power_str,
							    de_name: this.addForm.de_name,
								de_code: this.addForm.de_code,
								de_co_id: this.addForm.de_co_id,
								de_sort: this.addForm.de_sort,
								de_remark: this.addForm.de_remark
							};
							addDe(para).then((res) => {
								if(res.data.code == 0){
									// console.log(res);
									this.addLoading = false;
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs['addForm'].resetFields();
									this.addFormVisible = false;
									this.getDeparts();
								}else{
									alert(res.data.msg);
									this.addFormVisible = false;
									this.getDeparts();
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
			//删除
			handleDel: function (index) {
				this.$confirm('确认删除该子公司信息吗?', '提示', {
					type: 'warning'
				}).then(() => {
					this.listLoading = true;
					let para = {
						headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
					 	user_id:this.user_id,
				    	power_str:this.power_str,
					 	id: index
					};
					// // console.log(index);return false;
					delDe(para).then((res) => {
						this.listLoading = false;
						// console.log(res);
						if(res.data.code == 0){
							this.$message({
								message: '删除成功',
								type: 'success'
							});
							this.getDeparts();
						}else{
							alert(res.data.msg);
						}
							
					});
				}).catch((error) =>{
					// sessionStorage.removeItem('user');
					// this.$router.push('/login');
				});
			},
			// 获取所有子公司信息
			getAllComs:function(){
				// 查找所有子公司信息
				let params = {
					headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
				    power_str:this.power_str,
				};
				findComPoint(params).then((res)=>{
					this.addLoading = false;
					if(res.data.code == 0){
						// console.log(res.data.data);
						this.comList = res.data.data;
					}else{
						alert(res.data.msg);
					}
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			}
		},
		mounted() {
			this.getDeparts(1);
		}
	};

</script>

<style scoped>

</style>