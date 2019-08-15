<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" >
				<el-form-item>
					<el-button type="success" @click="handleAdd">新增分类</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="powers" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
			<el-table-column type="index" width="60">
			</el-table-column>
			<el-table-column prop="class_name" label="分类名称" >
			</el-table-column>
			<el-table-column  label="分类结构" prop="alias_name" width="400">
			</el-table-column>
			<el-table-column prop="class_img" label="轮播图片" >
				<template slot-scope="scope">
					<img :src="scope.row.class_img" class="avatar">
				</template>
			</el-table-column>
			<el-table-column prop="class_level" label="分类等级" :formatter="formType" >
			</el-table-column>
			<el-table-column prop="class_sort" label="排序" >
			</el-table-column>
			<el-table-column prop="class_description" label="备注" >
			</el-table-column>
			<el-table-column label="操作" fixed="right" width="170">
				<template slot-scope="scope">
					<el-button type="primary" size="small" @click="handleEdit(scope.row)">编辑</el-button>
					<el-button type="warning" size="small" @click="handleDel(scope.row.class_id)">删除</el-button>
				</template>
			</el-table-column>
		</el-table>



		<!--编辑界面-->
		<el-dialog title="编辑分类信息" :visible.sync="editFormVisible" :close-on-click-modal="false">
			<el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
				<el-form-item label="分类名称" prop="class_name">
					<el-input v-model="editForm.class_name"></el-input>
				</el-form-item>

				<div style="width=100%;height:50px;font-size:15px;line-height:70px;text-indent:10px;">分类图片</div>
	            <div class="el-upload" style="margin-left:100px;margin-bottom:30px;">
	                <el-upload class="avatar-uploader"
	                  action="http://shop.loveme.fun/api/upload/upload-img"
	                  :show-file-list="false"
	                  :on-success="AvatarSuccess"
	                  :before-upload="beforeAvatarUpload"
	                  >
	                  <img v-if="editForm.class_img" :src="editForm.class_img" class="avatar">
	                  <i v-else class="el-icon-plus avatar-uploader-icon"></i>
	                </el-upload>
	            </div>

				<el-form-item label="分类排序" prop="class_sort">
					<el-input v-model.number="editForm.class_sort"></el-input>
				</el-form-item>

				<el-form-item label="分类描述">
					<el-input v-model="editForm.class_description" ></el-input>
				</el-form-item>

			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="editFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="editSubmit" :loading="editLoading">提交</el-button>
			</div>
		</el-dialog>

		<!--新增界面-->
		<el-dialog title="添加新分类" :visible.sync="addFormVisible" :close-on-click-modal="false">
			<el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
				<el-form-item label="分类名称" prop="class_name">
					<el-input v-model="addForm.class_name"></el-input>
				</el-form-item>

				<el-form-item label="分类类型" prop="class_type" :change="showParent()">
				      	<el-radio v-model="addForm.class_type" label="1">根分类</el-radio>
				      	<el-radio v-model="addForm.class_type" label="2">子分类</el-radio>
				</el-form-item>

				<el-form-item label="父分类" >

					<el-select v-model="addForm.class_parent_id" :disabled="parent_hide" placeholder="请选择">
					    <el-option
					      v-for="item in parent_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
				 	</el-select>

				</el-form-item>

				<div style="width=100%;height:50px;font-size:15px;line-height:70px;text-indent:10px;">分类图片:</div>
	            <div class="el-upload" style="margin-left:100px;margin-bottom:30px;">
	                <el-upload class="avatar-uploader"
	                  action="http://shop.loveme.fun/api/upload/upload-img"
	                  :show-file-list="false"
	                  :on-success="handleAvatarSuccess"
	                  :before-upload="beforeAvatarUpload"
	                  >
	                  <img v-if="addForm.class_img" :src="addForm.class_img" class="avatar">
	                  <i v-else class="el-icon-plus avatar-uploader-icon"></i>
	                </el-upload>
	            </div>

				<el-form-item label="分类排序" prop="class_sort">
					<el-input v-model.number="addForm.class_sort"></el-input>
				</el-form-item>

				<el-form-item label="分类描述">
					<el-input v-model="addForm.class_remark" ></el-input>
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
	import { allClassify, delClassify, editClassify, addClassify,getClassifyParents } from '../../api/api';

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
					class_name: [
						{ required: true, message: '请输入分类名称', trigger: 'blur' },
						{ min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur' }
					],
					class_sort:[
						{ required: true, message: '请填写分类显示排序', trigger: 'blur' },
						{ type: 'number', message: '排序必须为数字值'  }
					]
				},
				//编辑界面数据
				editForm: {
					class_name: '',
					class_description:'',
					class_sort:null,
					class_img:null,
					class_id:0
				},

				addFormVisible: false,//新增界面是否显示
				addLoading: false,
				addFormRules: {
					class_name: [
						{ required: true, message: '请输入分类名称', trigger: 'blur' },
						{ min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur' }
					],
					class_type:[
						{ required: true, message: '请选择分类类型', trigger: 'change' }
					],
					class_sort:[
						{ required: true, message: '请填写分类显示排序', trigger: 'blur' },
						{ type: 'number', message: '排序必须为数字值'  }
					]
				},
				//新增界面数据
				addForm: {
					class_name: '',
					class_type: 1,
					class_parent_id:'',
					class_remark:'',
					class_img:null,
					class_sort:null
				},
				parent_options:[
					
				],
				parent_hide:true,
				edit_parent_hide:true,
				// 调用接口时，验证用户分类的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/classify'

			}
		},
		methods: {
			// 新增上传成功
			handleAvatarSuccess(res, file) {
				console.log(res);
				if(res.code == 0){
		            this.addForm.class_img = res.data.url;
		        }else{
		            alert(res.msg);
		        }
		        
		    },
		    AvatarSuccess(res, file) {
				if(res.code == 0){
		            this.editForm.class_img = res.data.url;
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
			//类型显示转换
			formType: function (row, column) {
				return "第"+row.class_level+"级";
			},
			// 当选择了节点类型后，显示父节点选择框
			showParent:function(){
				if(this.addForm.class_type == 2){
					this.parent_hide = false
				}else{
					this.parent_hide = true
					this.addForm.class_parent_id = ''
				}
			},
			//获取用户列表
			getAllClassify() {
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
				allClassify(params).then((res) => {
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
				this.$confirm('确认删除该分类吗?', '提示', {
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
					delClassify(para).then((res) => {
						// console.log(res);
						this.listLoading = false;
						if(res.data.code == 0){
							this.$message({
								message: '删除成功',
								type: 'success'
							});
							this.getAllClassify();
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
			},
			//显示新增界面
			handleAdd: function () {
				// 加载效果
				this.addFormVisible = true;
				// 重置新增员工选项
				this.addForm = {
					class_name: '',
					class_type: "1",
					class_parent_id:'',
					class_img:null,
					class_remark:'',
					class_sort:null
				};
				let para = {
					headers:{
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					user_id:this.user_id,
	    			power_str:this.power_str
				};
				getClassifyParents(para).then((res) => {
					// console.log(res);return false;
					this.addLoading = false;
					//NProgress.done();
					this.parent_options = res.data.data;
					// console.log(this.parent_options);
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
								class_name:this.editForm.class_name,
								class_img:this.editForm.class_img,
								class_remark:this.editForm.class_description,
								class_sort:this.editForm.class_sort,
								class_id:this.editForm.class_id
							};

							editClassify(para).then((res) => {
								console.log(res);
								if(res.data.code == 0){
									this.editLoading = false;
									//NProgress.done();
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs.editForm.resetFields();
									this.editFormVisible = false;
									this.getAllClassify();
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
				// console.log(this.addForm);return false;
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
								class_name:this.addForm.class_name,
								class_img:this.addForm.class_img,
								class_type:parseInt(this.addForm.class_type),
								class_remark:this.addForm.class_remark,
								class_sort:this.addForm.class_sort,
								class_parent_id:parseInt(this.addForm.class_parent_id)
							};

							addClassify(para).then((res) => {
								if(res.data.code == 0){
									// console.log(res);return false;
									this.addLoading = false;
									//NProgress.done();
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									this.$refs.addForm.resetFields();
									this.addFormVisible = false;
									this.getAllClassify();
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
			this.getAllClassify();
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