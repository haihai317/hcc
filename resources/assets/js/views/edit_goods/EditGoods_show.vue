<template>
	<el-form ref="form" :model="form" :rules="formVisible" label-width="80px" @submit.prevent="onSubmit" v-loading="listLoading" style="margin:20px;width:80%;min-width:600px;">
		<el-form-item label="商品名称" prop="goods_title">
			<el-input v-model="form.goods_title"></el-input>
		</el-form-item>
		<el-form-item label="商品简述">
			<el-input v-model="form.goods_descript"></el-input>
		</el-form-item>
		<div style="width=100%;height:50px;font-size:14px;line-height:70px;text-indent:10px;color:#606266">商品主图：</div>
        <div class="el-upload" style="margin-left:100px;margin-bottom:30px;">
            <el-upload class="avatar-uploader"
              action="http://shop.loveme.fun/api/upload/upload-img"
              :show-file-list="false"
              :on-success="handleAvatarSuccess"
              :before-upload="beforeAvatarUpload"
              >
              <img v-if="form.goods_main_img" :src="form.goods_main_img" class="avatar">
              <i v-else class="el-icon-plus avatar-uploader-icon"></i>
            </el-upload>
        </div>
		<div style="width=100%;height:50px;font-size:14px;line-height:70px;text-indent:10px;color:#606266">商品副图：</div>
		<div style="width:100px;float:left;height:100px;"></div>
		<div v-for="(img_url,index) in goodsImgUrl" style="display:block;float:left;margin-right:20px;margin-bottom:20px;">
		    <img style="height:100px;display:block;" :src="img_url" alt="">
		    <el-button type="text" @click="removeFuTu(img_url)">删除</el-button>
		</div>
		<div style="clear:both;"></div>
        <div class="el-upload" style="margin-left:100px;margin-bottom:30px;">
            <el-upload
			  action="http://shop.loveme.fun/api/upload/upload-img"
			  list-type="picture-card"
			  :show-file-list="true"
			  :on-preview="handlePictureCardPreview"
	          :on-success="avatarSuccess"
	          :before-upload="beforeAvatarUpload"
			  :on-remove="handleRemove">
			  <i class="el-icon-plus"></i>
			</el-upload>
			<el-dialog :visible.sync="dialogVisible" size="tiny">
			  <img width="100%" :src="detailImgUrl" alt="">
			</el-dialog>
        </div>
					        
		<el-form-item label="商品库存" prop="goods_store">
			<el-input v-model.number="form.goods_store"></el-input>
		</el-form-item>
		<el-form-item label="商品编码" >
			<el-input v-model="form.goods_sn"></el-input>
		</el-form-item>
		<el-form-item label="商品单位" prop="goods_unit">
			<el-input v-model="form.goods_unit"></el-input>
		</el-form-item>
		<el-form-item label="成本价" prop="goods_costprice">
			<el-input v-model.number="form.goods_costprice"></el-input>
		</el-form-item>
		<el-form-item label="市场价" prop="goods_marketprice">
			<el-input v-model.number="form.goods_marketprice"></el-input>
		</el-form-item>
		<el-form-item label="折后价" prop="goods_saleprice">
			<el-input v-model="form.goods_saleprice"></el-input>
		</el-form-item>
		<el-form-item label="会员价" prop="goods_memberprice">
			<el-input v-model="form.goods_memberprice"></el-input>
		</el-form-item>
		<el-form-item label="物流费" prop="goods_transfee">
			<el-input v-model="form.goods_transfee"></el-input>
		</el-form-item>
		<el-form-item label="所属分类" prop="goods_classify_value">
			<el-cascader
		    expand-trigger="hover"
		    :options="class_options"
		    v-model="form.goods_classify_value"
		    placeholder="请选择分类"
		    >
		  	</el-cascader>
		</el-form-item>
		<el-form-item label="所属品牌" prop="goods_brand_id">
			<el-select v-model="form.goods_brand_id" placeholder="请选择品牌">
			    <el-option
			      v-for="item in brand_options"
			      :key="item.value"
			      :label="item.label"
			      :value="item.value">
			    </el-option>
			 </el-select>
		</el-form-item>
		<el-form-item label="展台上架">
			<el-checkbox-group v-model="form.type">
				<el-checkbox label="精品推荐" name="type"></el-checkbox>
				<el-checkbox label="新品首发" name="type"></el-checkbox>
			</el-checkbox-group>
		</el-form-item>
		<el-form-item label="规格设置">
			<el-switch on-text="开启" off-text="关闭" v-model="form.delivery" @change="openSpec"></el-switch>
			<el-button type="text" v-if="form.delivery"  @click="addSpecItem" style="margin-left:20px;">添加一种属性规格</el-button>
		</el-form-item>
		<el-form-item v-if="form.delivery">
			<div v-for="(spec,index) in form.specArr" style="margin-bottom:20px;">
				<el-input v-model="spec.name" style="width:300px;" placeholder="请输入规格名称"></el-input>
				<el-button type="primary" plain style="margin-left:40px" @click="addSpec2(index)">添加具体项</el-button>
				<el-button type="warning" plain style="margin-left:10px" @click="delSpec(index)">删除</el-button>
				<br>
				<div>
					<el-tag v-for="(tag,i) in spec.children" :key="tag.name" closable  style="margin-right:20px;"@close="closeTag(index,i)"> {{tag.name}} </el-tag>
				</div>
			</div>
		</el-form-item>
		<div></div>
		<el-form-item v-if="form.delivery">
			<div>规格列表：</div>
			<div v-for="(spec,index) in form.specGroup" >
				<el-row>
					<el-col :span="3" style="width:63px;height:63px;line-height:63px;margin-rigth:20px;">
						<div style="width:63px;height:63px;line-height:63px;margin-rigth:20px;">
							<el-upload class="avatar-uploader"
				              action="http://shop.loveme.fun/api/upload/upload-img"
				              :show-file-list="false"
				              :on-success="specSuccess"
				              :before-upload="beforeAvatarUpload"
				              :data="spec"
				              >
				              <img v-if="spec.spec_img" :src="spec.spec_img" class="avatar" style="width:63px;height:63px;line-height:63px;">
				              <i v-else class="el-icon-plus avatar-uploader-icon" style="width:63px;height:63px;line-height:63px;"></i>
				            </el-upload>
				        </div>
					</el-col>
					<el-col :span="8"><el-tag type="success">{{ spec.name }}</el-tag></el-col>
					<el-col :span="2"><el-input v-model="spec.store" placeholder="库存"></el-input></el-col>
					<el-col :span="2"><el-input v-model="spec.marketprice" placeholder="市场价"></el-input></el-col>
					<el-col :span="2"><el-input v-model="spec.costprice" placeholder="成本价"></el-input></el-col>
					<el-col :span="2"><el-input v-model="spec.saleprice" placeholder="活动价"></el-input></el-col>
					<el-col :span="2"><el-input v-model="spec.memberprice" placeholder="会员价"></el-input></el-col>
					<el-col :span="2"><el-input v-model="spec.sn" placeholder="编码"></el-input></el-col>
				</el-row>
				<br>
			</div>
		</el-form-item>

		
		<el-form-item label="商品参数">
			<el-switch on-text="开启" off-text="关闭" v-model="form.ifparams" @change="openParam"></el-switch>
			<el-button type="text" v-if="form.ifparams"  @click="addParamArr" style="margin-left:20px;">添加一种商品参数</el-button>
		</el-form-item>
		<el-form-item v-if="form.ifparams">
			<div v-for="(item,index) in form.paramArr" style="margin-bottom:20px;">
				<el-input v-model="item.param_title" style="width:300px;" placeholder="请输入参数名称"></el-input>
				<el-input v-model="item.param_value" style="width:300px;" placeholder="请输入对应的参数值"></el-input>
				<el-button type="warning" plain style="margin-left:10px" @click="delParamArr(index)">删除</el-button>
			</div>
		</el-form-item>

		<el-form-item label="商品详情" style="margin:100px auto 50;">
			<div :id="uediter_id" style="margin-top:50px;"></div>
		</el-form-item>
			
		<el-button type="success" @click="onSubmit">保存并创建商品</el-button>
		<el-button type="danger" @click="backLast">返回上一级</el-button>
	</el-form>
</template>

<script>
	import util from '../../common/js/util';
	import { getClaAndBr,addGoods,showEditMes } from '../../api/api';
	import '../../uediter/ueditor.config.js';
	import '../../uediter/ueditor.all.min.js';               
	import '../../uediter/ueditor.parse.min.js'; 
	import '../../uediter/lang/zh-cn/zh-cn.js';
	export default {
		data() {
			return {
				uediter_id:Math.ceil(Math.random()*100000) + 'editor',//富文本编辑器
				form: {
					goods_title:'',
					goods_descript:'',
					goods_main_img:'',
					goods_store:'',
					goods_sn:'',
					goods_transfee:0,
					goods_marketprice:"",
					goods_memberprice:0,
					goods_costprice:"",
					goods_saleprice:0,
					goods_unit:'',
					delivery: false,
					type: [],
					goods_classify_value:[],
					goods_brand_id:null,
					specArr:[],// 规格品类数组
					specGroup:[],//最终的规格组合
					detail_text:'',
					ifparams: false,
					paramArr:[]//商品参数数组
				},
				formVisible:{
					goods_title: [
						{ required: true, message: '请输入商品名称', trigger: 'blur' }
					],
					goods_store: [
						{ required: true, message: '库存不能为空', trigger: 'blur' },
      					{ type: 'number', message: '库存必须为数字值'}
					],
					goods_unit:[
						{ required: true, message: '请输入商品计量单位', trigger: 'blur' }
					],
					goods_marketprice:[
						{ required: true, message: '请输入商品市场价', trigger: 'blur' }
					],
					goods_costprice:[
						{ required: true, message: '请输入商品成本价', trigger: 'blur' }
					],
					goods_classify_value:[
						{ required: true, message: '请选择商品所属分类', trigger: 'blur' }
					],
					goods_brand_id:[
						{ required: true, message: '请选择商品所属品牌', trigger: 'blur' }
					]
				},
				class_options:[],
				brand_options:[],
				// 批量上传
				goodsImgUrl: [],
				detailImgUrl:'',
        		dialogVisible: false,
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/goods_list',
				listLoading:false,
				editor:null
			}
		},
		methods: {
			getGoods:function(){
				let id = this.$route.params.id;
				let para = {
					headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
	    			power_str:this.power_str,
	    			id:id
				}
				// 查询部门信息
				showEditMes(para).then((res) => {
					console.log(res);
					// 关闭加载
					this.listLoading = false;
					this.form = res.data.data.form;
					this.goodsImgUrl = res.data.data.goodsImgUrl;
					// 填充商品详情
					this.editor.setContent(this.form.detail_text,false);
					
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			},
			getUEContent() { // 获取内容方法
		　　　　this.form.detail_text = this.editor.getContent();
		　　},
			// 根据当前的规格二维数组，呈现最终规格组合
			showSpecGroup:function(){
				let arr = this.form.specArr[0].children;
				for (var i = 1; i < this.form.specArr.length; i++) {
					// console.log(this.form.specArr[i].children.length);
					if(this.form.specArr[i].children.length > 0){
						let brr = [];
						for (var j = this.form.specArr[i].children.length - 1; j >= 0; j--) {
							for (var k = arr.length - 1; k >= 0; k--) {
								brr.push({"name":arr[k].name+" "+this.form.specArr[i].children[j].name,"spec_img":"","store":"","marketprice":"","costprice":"","saleprice":"","memberprice":"",'sn':""});
							}
							
						}
						arr = brr;
					}
				}
				this.form.specGroup = arr;
				console.log(this.form.specGroup);
			},
			// 关闭规格具体项
			closeTag:function(index,i){
				// 删除相应的规格具体项
				this.form.specArr[index].children.splice(i,1);
				// console.log(this.form.specArr);
				// 重排最终组合
				this.showSpecGroup();
			},
			// 弹窗添加规格具体项
			addSpec2(i) {
		        this.$prompt('请输入规格具体项', '提示', {
		            confirmButtonText: '确定',
		            cancelButtonText: '取消',
		        }).then(({ value }) => {
		            // 将输入的具体项放入规格数组
		            if(value == null){
		            	this.$message({
							message: '规格项目名称不能为空！',
							type: 'warning'
						});
		            }else{
		            	this.form.specArr[i].children.push({"name":value,"spec_img":"","store":"","marketprice":"","costprice":"","saleprice":"","memberprice":"",'sn':""});
						// 重排最终组合
						this.showSpecGroup();
		            }
			            
		        }).catch(() => {
		                 
		        });
		    },
			// 删除一级规格
			delSpec:function(index){
				this.form.specArr.splice(index,1);
				// 重排最终组合
				this.showSpecGroup();
			},
			// 添加一项一级规格
			addSpecItem:function(){
				this.form.specArr.push({"name":"","children":[]});
				// console.log(this.form.specArr);
			},
			// 添加一项商品参数
			addParamArr:function(){
				this.form.paramArr.push({"param_title":"","param_value":""});
				// console.log(this.form.specArr);
			},
			// 删除商品参数
			delParamArr:function(index){
				this.form.paramArr.splice(index,1);
			},
			// 开启或关闭规格开关
			openSpec:function(){
				// 如果开启规格
				if(this.form.delivery == true){

				}else{
					// 如果关闭规格，将整个规格数组清空
					this.form.specArr = [];
					this.form.specGroup = [];
				}
			},
			// 开启或关闭商品参数开关
			openParam:function(){
				// 如果开启规格
				if(this.form.ifparams == true){

				}else{
					// 如果关闭规格，将整个规格数组清空
					this.form.paramArr = [];
				}
			},
			//规格图片上传成功
			specSuccess:function(res, file){
				if(res.code == 0){
					for (var i = this.form.specGroup.length - 1; i >= 0; i--) {
						if(this.form.specGroup[i].name == res.data.name){
							this.form.specGroup[i].spec_img = res.data.url;
							// console.log(this.form.specGroup[i].spec_img);
						}
					}
					// console.log(this.form.specGroup);
		        }else{
		            alert(res.msg);
		        }
			},
			// 主图上传成功
			handleAvatarSuccess(res, file) {
				console.log(res);
				if(res.code == 0){
		            this.form.goods_main_img = res.data.url;
		        }else{
		            alert(res.msg);
		        }
		        // console.log(this.form.goods_main_img);
		    },
		    // 副图上传成功
		    avatarSuccess(res, file) {
				if(res.code == 0){
		            this.goodsImgUrl.push(res.data.url) ;
		        }else{
		            alert(res.msg);
		        }
		        console.log(this.goodsImgUrl);
		    },
		    // 删除副图
		    handleRemove(file, fileList) {
			    let delUrl = file.response.data;
			    for (var i = this.goodsImgUrl.length - 1; i >= 0; i--) {
			    	if(this.goodsImgUrl[i] == delUrl){
			    		this.goodsImgUrl.splice(i,1);
			    	}
			    }
			    // console.log(this.goodsImgUrl);
			},
		    // 删除原有副图
		    removeFuTu(url) {
			    let delUrl = url;
			    for (var i = this.goodsImgUrl.length - 1; i >= 0; i--) {
			    	if(this.goodsImgUrl[i] == delUrl){
			    		this.goodsImgUrl.splice(i,1);
			    	}
			    }
			    // console.log(this.goodsImgUrl);
			},
			// 附图预览
			handlePictureCardPreview(file) {
			    this.detailImgUrl = file.url;
			    this.dialogVisible = true;
			},
			// 上传图片前的检测
		    beforeAvatarUpload(file) {
		          
	          const isLt2M = file.size / 1024 / 1024 < 2;
	          
	          if (!isLt2M) {
	            this.$message.error('上传头像图片大小不能超过 2MB!');
	          };
	          return isLt2M;
		    },
		    // 获取分类和品牌
		    getClAndBr(){
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
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
		    },
		    // 提交表单
			onSubmit() {
				// 获取商品详情
				this.getUEContent();
				// console.log(this.form.specGroup);return false;
				// 商品主图不能为空
				if(this.form.goods_main_img == ""){
					this.$message({
						message: '商品的主图不能为空',
						type: 'warning'
					});
					return false;
				}
				// 验证商品详情和规格内的name值为非空
				let detail = this.form.detail_text;
				if(detail == ''){
					this.$message({
						message: '商品详情不能为空',
						type: 'warning'
					});
					return false;
				}
				for (var i = this.form.specArr.length - 1; i >= 0; i--) {
					if(this.form.specArr[i].name == ''){
						this.$message({
							message: '规格项目名称不能为空！',
							type: 'warning'
						});
						return false;
					}
					if(this.form.specArr[i].children.length == 0){
						this.$message({
							message: '规格项目的具体项不能为空！',
							type: 'warning'
						});
						return false;
					}
				}
				for (var i = this.form.specGroup.length - 1; i >= 0; i--) {
					console.log(this.form.specGroup[i]);
					if(this.form.specGroup[i].store == "" || this.form.specGroup[i].marketprice == "" ){
						this.$message({
							message: '规格组合的库存、标价不能为空',
							type: 'warning'
						});
						return false;
					}
				}

				// 验证表单
				this.$refs.form.validate((valid) => {
					if (valid) {
						this.$confirm('确认提交吗？', '提示', {}).then(() => {
							// this.listLoading = true;
							let para = {
							    headers: {
							        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							    },
							    user_id:this.user_id,
				    			power_str:this.power_str,
				    			form:this.form,
				    			goodsImgUrl:this.goodsImgUrl
							};
							addGoods(para).then((res) => {
									// console.log(res);return false;
								if(res.data.code == 0){
									this.listLoading = false;
									this.$message({
										message: '提交成功',
										type: 'success'
									});
									// 返回上一级
									this.$router.go(-1);
								}else{
									alert(res.data.msg);
									this.listLoading = false;
								}
							}).catch((error) =>{
								// sessionStorage.removeItem('user');
								// this.$router.push('/login');
							});
						});
					}else{
						alert('请完善表单');
					}
				});
			},
			backLast:function(){
				this.$router.go(-1);
			}
		},mounted() {
			// 初始化编辑框
			this.editor = UE.getEditor(this.uediter_id);
			this.getGoods();
			this.getClAndBr();
		}
	}

</script>
<style>
	.el-upload {
    /*border: 1px dashed #d9d9d9;*/
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
 .avatar-uploader .el-upload {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }
  .avatar-uploader .el-upload:hover {
    border-color: #409EFF;
  }
  .avatar-uploader-icon  {
    font-size: 28px;
    color: #8c939d;
    width: 100px;
    height: 100px;
    line-height: 100px;
    text-align: center;
  }

  .avatar {
    width: 100px;
    height: 100px;
    display: block;
  }
  .el-upload--picture-card {
    width: 100px;
    height: 100px;
    line-height: 100px;
}
.el-col-3{
	margin-right:20px;
}

</style>