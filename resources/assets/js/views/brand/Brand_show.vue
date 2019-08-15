<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
				<el-form-item>
					<el-input v-model="filters.name" placeholder="品牌名称"></el-input>
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
			<el-table-column type="index" width="55">
			</el-table-column>
			<el-table-column type="brand_id" width="60">
			</el-table-column>
			<el-table-column prop="brand_title" label="品牌名称" >
			</el-table-column>
			<el-table-column prop="brand_img" label="品牌logo" >
				<template slot-scope="scope">
					<img :src="scope.row.brand_img" class="avatar">
				</template>
			</el-table-column>
			<el-table-column prop="brand_descript" label="品牌简介" >
			</el-table-column>
			<el-table-column label="操作" fixed="right">
				<template slot-scope="scope">
					<el-button size="small" @click="handleEdit(scope.row)">编辑</el-button>
					<el-button type="danger" size="small" @click="handleDel(scope.row.brand_id)">删除</el-button>
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
		<el-dialog title="编辑品牌信息" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
				<el-form-item label="品牌名称" prop="brand_title">
					<el-input v-model="editForm.brand_title"></el-input>
				</el-form-item>
				<div style="width=100%;height:50px;font-size:15px;line-height:70px;text-indent:10px;">品牌LOGO：</div>
	            <div class="el-upload" style="margin-left:100px;margin-bottom:30px;">
	                <el-upload class="avatar-uploader"
	                  action="http://shop.loveme.fun/api/upload/upload-img"
	                  :show-file-list="true"
	                  :on-success="AvatarSuccess"
	                  :before-upload="beforeAvatarUpload"
	                  >
	                  <img v-if="editForm.brand_img" :src="editForm.brand_img" class="avatar">
	                  <i v-else class="el-icon-plus avatar-uploader-icon"></i>
	                </el-upload>
	            </div>
				<el-form-item label="品牌简介" prop="brand_descript">
					<el-input v-model="editForm.brand_descript" ></el-input>
				</el-form-item>
			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="editFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="editSubmit" :loading="editLoading">提交</el-button>
			</div>
		</el-dialog>

		<!--新增界面-->
		<el-dialog title="添加新的品牌" :visible.sync="addFormVisible" :close-on-click-modal="false">
			<el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
				<el-form-item label="品牌名称" prop="brand_title">
					<el-input v-model="addForm.brand_title"></el-input>
				</el-form-item>
				<div style="width=100%;height:50px;font-size:15px;line-height:70px;text-indent:10px;">品牌LOGO：</div>
	            <div class="el-upload" style="margin-left:100px;margin-bottom:30px;">
	                <el-upload class="avatar-uploader"
	                  action="http://shop.loveme.fun/api/upload/upload-img"
	                  :show-file-list="true"
	                  :on-success="handleAvatarSuccess"
	                  :before-upload="beforeAvatarUpload"
	                  >
	                  <img v-if="addForm.brand_img" :src="addForm.brand_img" class="avatar">
	                  <i v-else class="el-icon-plus avatar-uploader-icon"></i>
	                </el-upload>
	            </div>
				<el-form-item label="品牌简介" prop="brand_descript">
					<el-input v-model="addForm.brand_descript"></el-input>
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
	import { getBrands, editBrands, addBrand, delBrands } from '../../api/api';

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
					brand_title: [
						{ required: true, message: '请输入品牌名称', trigger: 'blur' }
					],
					brand_img:[
						{ required: true, message: '请上传品牌图片', trigger: 'blur' }
					]
				},
				//编辑界面数据
				editForm: {
					brand_id:0,
					brand_title: '',
					brand_img: '',
					brand_descript: ''
				},

				addFormVisible: false,//新增界面是否显示
				addLoading: false,
				addFormRules: {
					brand_title: [
						{ required: true, message: '请输入品牌名称', trigger: 'blur' }
					],
					brand_img:[
						{ required: true, message: '请上传品牌图片', trigger: 'blur' }
					]
				},
				//新增界面数据
				addForm: {
					brand_title: '',
					brand_img: '',
					brand_descript: ''
				},
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/brand'

			}
		},
		methods: {
			handleAvatarSuccess(res, file) {
				if(res.code == 0){
		            this.addForm.brand_img = res.data.url;
		        }else{
		            alert(res.msg);
		        }
		        
		    },
		    AvatarSuccess(res, file) {
				if(res.code == 0){
		            this.editForm.brand_img = res.data;
		        }else{
		            alert(res.msg);
		        }
		        
		    },
		    beforeAvatarUpload(file) {
		          
	          const isLt2M = file.size / 1024 / 1024 < 2;
	          
	          if (!isLt2M) {
	            this.$message.error('上传头像图片大小不能超过 2MB!');
	          };
	          return isLt2M;
		    },
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
				getBrands(params).then((res) => {
					console.log(res);
					this.total = res.data.data.count;
					this.coms = res.data.data.coms;
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
					brand_title: '',
					brand_img: '',
					brand_descript: ''
				};
				// 打开dialog
				this.addFormVisible = true;
			},
			//编辑
			editSubmit: function () {
				this.$refs.editForm.validate((valid) => {
					if (valid) {
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							if(this.editForm.brand_img == ''){
								this.$message({
									message: '请上传品牌LOGO',
									type: 'warning'
								});
								return false;
							}

							// console.log(this.editForm);return false;
							this.editLoading = true;
							let para = {
							    headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
							    user_id:this.user_id,
				    			power_str:this.power_str,
							    brand_id: this.editForm.brand_id,
							    brand_title: this.editForm.brand_title,
								brand_img: this.editForm.brand_img,
								brand_descript: this.editForm.brand_descript
							};
							editBrands(para).then((res) => {
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
				// console.log(this.addForm);return false;
				this.$refs.addForm.validate((valid) => {
					if (valid) {
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							if(this.addForm.brand_img == ''){
								this.$message({
									message: '请上传品牌LOGO',
									type: 'warning'
								});
								return false;
							}
							this.addLoading = true;
							let para = {
							    headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
							    user_id:this.user_id,
				    			power_str:this.power_str,
							    brand_title: this.addForm.brand_title,
								brand_img: this.addForm.brand_img,
								brand_descript: this.addForm.brand_descript
							};
							addBrand(para).then((res) => {
								if(res.data.code == 0){
									// console.log(res);return false;
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
				this.$confirm('确认删除该品牌信息吗?', '提示', {
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
					delBrands(para).then((res) => {
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
.el-upload {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
 .avatar-uploader .el-upload {
    /*border: 1px dashed #d9d9d9;*/
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }
  .avatar-uploader .el-upload:hover {
    border-color: #409EFF;
  }
  .avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 78px;
    height: 78px;
    line-height: 78px;
    text-align: center;
  }

  .avatar {
    width: 78px;
    height: 78px;
    display: block;
  }
</style>