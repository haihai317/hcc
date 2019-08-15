<template>
	<section>
		<el-card class="box-card" style="margin-top:30px">
		    <div slot="header" class="clearfix">
			    <span>为 “{{ role_name }}” 职位授权</span>
		    </div>
		  	<div>
		   		<el-tree
					v-loading="listLoading"
					:data="powers"
					show-checkbox
					node-key="id"
					ref="tree"
					:default-checked-keys="haveId"
					default-expand-all
				  >
				</el-tree>
		  	</div>
		</el-card>
		<el-button style="margin:20px 0 0 10px" type="success" @click="submitNode">提交</el-button>
		<el-button style="margin:20px 0 0 10px" type="primary" @click="backNode">返回</el-button>
	</section>
</template>

<script>
	import util from '../../common/js/util'
	//import NProgress from 'nprogress'
	import { getRolePower,submitTree } from '../../api/api';

	export default {
		data() {
			return {
				listLoading:false,
				powers:[],
				treeNode:[],
				role_id:0,
				role_name:'',
				haveId:[],
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/role'
			}
		},
		methods: {
			
			//获取用户列表
			getPowers() {
				this.role_id = this.$route.params.id;
				this.role_name = this.$route.params.role_name;
				// console.log(this.role_id);
				if(this.role_id == undefined){
					this.$router.push({path:'/role'});
				}

				let params = {
				    headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
				    power_str:this.power_str,
				    role_id:this.role_id
				};

				// // console.log(params);return false;
				this.listLoading = true;
				//NProgress.start();
				this.powers = [];
				getRolePower(params).then((res) => {
					if(res.data.code == 0){
						this.powers = res.data.data.powers;
						this.haveId = res.data.data.hasId;
						// console.log(this.powers);
					}else{
						alert(res.data.msg);
						this.$router.push({path:'/role'});
					}

					this.listLoading = false;
						
					//NProgress.done();
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});

			},
			submitNode() {
        		this.treeNode = this.$refs.tree.getCheckedKeys();
        		// console.log(this.treeNode);
        		let params = {
				    headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
				    power_str:this.power_str,
				    treeNode:this.treeNode,
				    role_id:this.role_id
				};

				this.listLoading = true;

				submitTree(params).then((res) => {
					if(res.data.code == 0){
						alert('操作成功！');
					}else{
						alert(res.data.msg);
					}
					this.listLoading = false;
					//NProgress.done();
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});

      		},
      		backNode(){
      			this.$router.go(-1);
      		}
		},
		mounted() {
			this.getPowers();
		}
	};

</script>

<style scoped>
.el-tree-node__content {
    height: 33px!important;
}
.el-tree-node__label {
    font-size: 16px!important;
}
</style>