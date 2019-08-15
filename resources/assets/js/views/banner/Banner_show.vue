<template>
	<section>
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" >
				<el-form-item>
					<el-button type="success" @click="handleAdd">新增</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="banners" highlight-current-row v-loading="listLoading"  style="width: 100%;">
			<el-table-column type="index" width="55">
			</el-table-column>
			<el-table-column type="banner_id" width="60">
			</el-table-column>
			<el-table-column prop="banner_name" label="轮播图名称" >
			</el-table-column>
			<el-table-column prop="banner_img" label="轮播图片" >
				<template slot-scope="scope">
					<img :src="scope.row.banner_img" class="avatar">
				</template>
			</el-table-column>
			<el-table-column prop="banner_url" label="轮播图链接" >
			</el-table-column>
			<el-table-column prop="banner_sort" label="轮播图排序" >
			</el-table-column>
			<el-table-column label="操作" fixed="right">
				<template slot-scope="scope">
					<el-button size="small" @click="handleEdit(scope.row)">编辑</el-button>
					<el-button type="danger" size="small" @click="handleDel(scope.row.banner_id)">删除</el-button>
				</template>
			</el-table-column>
		</el-table>


		<!--编辑界面-->
		<el-dialog title="编辑轮播图信息" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
				<el-form-item label="轮播图名称" prop="banner_name">
					<el-input v-model="editForm.banner_name"></el-input>
				</el-form-item>
				<div style="width=100%;height:50px;font-size:15px;line-height:70px;text-indent:10px;">轮播图片</div>
	            <div class="el-upload" style="margin-left:100px;margin-bottom:30px;">
	                <el-upload class="avatar-uploader"
	                  action="http://shop.loveme.fun/api/upload/upload-img"
	                  :show-file-list="false"
	                  :on-success="AvatarSuccess"
	                  :before-upload="beforeAvatarUpload"
	                  >
	                  <img v-if="editForm.banner_img" :src="editForm.banner_img" class="avatar">
	                  <i v-else class="el-icon-plus avatar-uploader-icon"></i>
	                </el-upload>
	            </div>
				<el-form-item label="轮播图链接" prop="banner_url">
					<el-input v-model="editForm.banner_url" ></el-input>
				</el-form-item>
				<el-form-item label="轮播图排序" prop="banner_sort">
					<el-input v-model.number="editForm.banner_sort"></el-input>
				</el-form-item>
			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="editFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="editSubmit" :loading="editLoading">提交</el-button>
			</div>
		</el-dialog>

		<!--新增界面-->
		<el-dialog title="添加新的轮播图" :visible.sync="addFormVisible" :close-on-click-modal="false">
			<el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
				<el-form-item label="轮播图名称">
					<el-input v-model="addForm.banner_name"></el-input>
				</el-form-item>
				<div style="width=100%;height:50px;font-size:15px;line-height:70px;text-indent:10px;">轮播图片:</div>
	            <div class="el-upload" style="margin-left:100px;margin-bottom:30px;">
	                <el-upload class="avatar-uploader"
	                  action="http://shop.loveme.fun/api/upload/upload-img"
	                  :show-file-list="false"
	                  :on-success="handleAvatarSuccess"
	                  :before-upload="beforeAvatarUpload"
	                  >
	                  <img v-if="addForm.banner_img" :src="addForm.banner_img" class="avatar">
	                  <i v-else class="el-icon-plus avatar-uploader-icon"></i>
	                </el-upload>
	            </div>
				<el-form-item label="轮播图链接" >
					<el-input v-model="addForm.banner_url"></el-input>
				</el-form-item>
				<el-form-item label="轮播图排序" prop="banner_sort">
					<el-input v-model.number="addForm.banner_sort"></el-input>
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
	import { getBanners, editBanners, addBanner, delBanners } from '../../api/api';

	export default {
		data() {
			return {
				banners: [],
				listLoading: false,

				editFormVisible: false,//编辑界面是否显示
				editLoading: false,
				editFormRules: {
					banner_sort: [
						{ type:'number', message: '请输入数字类型的轮播图排序', trigger: 'blur' }
					],
					banner_img:[
						{ required: true, message: '请上传轮播图片', trigger: 'blur' }
					]
				},
				//编辑界面数据
				editForm: {
					banner_id:0,
					banner_name: '',
					banner_img: '',
					banner_url: '',
					banner_sort:0
				},

				addFormVisible: false,//新增界面是否显示
				addLoading: false,
				addFormRules: {
					banner_sort: [
						{ type:'number', message: '请输入数字类型的轮播图排序', trigger: 'blur' }
					],
					banner_img:[
						{ required: true, message: '请上传轮播图片', trigger: 'blur' }
					]
				},
				//新增界面数据
				addForm: {
					banner_name: '',
					banner_img: '',
					banner_url: '',
					banner_sort:0
				},
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/banner_list'

			}
		},
		methods: {
			handleAvatarSuccess(res, file) {
				console.log(res);
				if(res.code == 0){
		            this.addForm.banner_img = res.data.url;
		        }else{
		            alert(res.msg);
		        }
		        
		    },
		    AvatarSuccess(res, file) {
				if(res.code == 0){
		            this.editForm.banner_img = res.data.url;
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
			//获取用户列表
			getbanners() {
				let params = {
				    headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
				    power_str:this.power_str
				};

				// // console.log(params);return false;
				this.listLoading = true;
				//NProgress.start();
				getBanners(params).then((res) => {
					console.log(res);
					this.banners = res.data.data;
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
					banner_name: '',
					banner_img: '',
					banner_url: '',
					banner_sort:''
				};
				// 打开dialog
				this.addFormVisible = true;
			},
			//编辑
			editSubmit: function () {
				this.$refs.editForm.validate((valid) => {
					if (valid) {
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							if(this.editForm.banner_img == ''){
								this.$message({
									message: '请上传轮播图片',
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
							    banner_id: this.editForm.banner_id,
							    banner_name: this.editForm.banner_name,
								banner_img: this.editForm.banner_img,
								banner_url: this.editForm.banner_url,
								banner_sort:this.editForm.banner_sort
							};
							editBanners(para).then((res) => {
								if(res.data.code == 0){
									// console.log(res);
									this.editLoading = false;
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs['editForm'].resetFields();
									this.editFormVisible = false;
									this.getbanners();
								}else{
									alert(res.data.msg);
									this.editLoading = false;
									this.getbanners();
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
							if(this.addForm.banner_img == ''){
								this.$message({
									message: '请上传轮播图片',
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
							    banner_name: this.addForm.banner_name,
								banner_img: this.addForm.banner_img,
								banner_url: this.addForm.banner_url,
								banner_sort: this.addForm.banner_sort
							};
							addBanner(para).then((res) => {
								if(res.data.code == 0){
									// console.log(res);return false;
									this.addLoading = false;
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs['addForm'].resetFields();
									this.addFormVisible = false;
									this.getbanners();
								}else{
									alert(res.data.msg);
									this.addFormVisible = false;
									this.getbanners();
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
					delBanners(para).then((res) => {
						if(res.data.code == 0){
							this.listLoading = false;
							this.$message({
								message: '删除成功',
								type: 'success'
							});
							this.getbanners();
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
			this.getbanners();
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