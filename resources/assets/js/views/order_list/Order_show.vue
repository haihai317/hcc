<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
				<el-form-item>
					<el-input v-model="filters.order_code" placeholder="订单号"></el-input>
				</el-form-item>
				<el-form-item>
					<el-input v-model="filters.goods_title" placeholder="商品名称"></el-input>
				</el-form-item>
				<el-form-item>
					<el-input v-model="filters.order_express_num" placeholder="快递单号"></el-input>
				</el-form-item>
				<el-form-item>
					<el-select v-model="filters.order_pay_way" placeholder="支付方式">
					    <el-option
					      v-for="item in pay_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
					 </el-select>
				</el-form-item>
				<el-form-item>
					<el-select v-model="filters.order_status" placeholder="订单状态">
					    <el-option
					      v-for="item in status_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
					 </el-select>
				</el-form-item>
				<el-form-item>
					<el-button type="primary" v-on:click="getOrders(1)">查询</el-button>
				</el-form-item>
			</el-form>
		</el-col>

		<!--列表-->
		<el-table :data="orders"  v-loading="listLoading"  style="width: 100%;">
			<el-table-column type="index" width="55">
			</el-table-column>
			<el-table-column prop="order_code" label="订单号">
			</el-table-column>
			<el-table-column prop="user_phone" label="下单用户" >
			</el-table-column>
			<el-table-column prop="order_goods_money" label="订单总额" >
			</el-table-column>
			<el-table-column prop="order_addr" label="收货地址"  :formatter="formatAddr">
			</el-table-column>
			<el-table-column prop="order_pay_way" label="支付方式"  :formatter="formatPayWay">
			</el-table-column>
			<el-table-column prop="order_createtime" label="下单时间" >
			</el-table-column>
			<el-table-column prop="order_status" label="订单状态" :formatter="formatStatus">
			</el-table-column>
			<el-table-column label="操作" fixed="right">
				<template slot-scope="scope">
					<el-button size="small" type="primary" plain @click="handleDetail(scope.row.order_id)">订单详情</el-button>
					<el-button v-if="scope.row.order_status == 2" size="small" type="success" plain @click="writeExpress(scope.row.order_id)">确认发货</el-button>
					<!-- <el-button style="display:block;margin-top:15px;" v-if="scope.row.order_status == 2" size="small" type="info" plain @click="sendMes(scope.row.order_id)">确认退款</el-button> -->
				</template>
			</el-table-column>
		</el-table>

		<!--工具条-->
		<el-col :span="24" class="toolbar">
			<!-- <el-button type="danger" @click="batchRemove" :disabled="this.sels.length===0">批量删除</el-button> -->
			<el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="10" :total="total" style="float:right;">
			</el-pagination>
		</el-col>

		<!--填写物流信息-->
		<el-dialog title="填写物流信息" :visible.sync="addFormVisible" :close-on-click-modal="false">
			<el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">

				<el-form-item label="选择物流公司"  prop="express">
					<el-select v-model="addForm.express" placeholder="请选择">
					    <el-option
					      v-for="item in express_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item">
					    </el-option>
				  	</el-select>
				</el-form-item>
				<el-form-item label="物流单号" prop="express_num">
					<el-input v-model="addForm.express_num"></el-input>
				</el-form-item>

			</el-form>
			<div slot="footer" class="dialog-footer">
				<el-button @click.native="addFormVisible = false">取消</el-button>
				<el-button type="primary" @click.native="saveExpress" :loading="addLoading">提交</el-button>
			</div>
		</el-dialog>

	</section>
</template>

<script>
	import util from '../../common/js/util'
	//import NProgress from 'nprogress'
	import { showOrderList, addExpressMes } from '../../api/api';

	export default {
		data() {
			return {
				addForm:{
					express:{},
					express_num:''
				},
				addFormRules: {
					express: [
						{ required: true, message: '请选择物流公司', trigger: 'blur' }
					],
					express_num: [
						{ required: true, message: '请填写物流单号', trigger: 'blur' }
					]
				},
				addLoading:false,
				express_options:[{label:'德邦物流',value:'debangwuliu'},{label:'ems快递',value:'ems'},{label:'国通快递',value:'guotongkuaidi'},{label:'汇通快运',value:'huitongkuaidi'},{label:'佳吉物流',value:'jjwl'},{label:'联邦快递（国内）',value:'lianb'},{label:'民航快递',value:'minghangkuaidi'},{label:'全际通物流',value:'quanjitong'},{label:'申通',value:'shentong'},{label:'顺丰',value:'shunfeng'},{label:'天地华宇',value:'tiandihuayu'},{label:'天天快递',value:'tiantian'},{label:'万家物流',value:'wanjiawuliu'},{label:'优速物流',value:'youshuwuliu'},{label:'圆通速递',value:'yuantong'},{label:'韵达快运',value:'yunda'},{label:'中通速递',value:'zhongtong'}],
				filters: {
					goods_title: '',
					order_code:'',
					order_express_num:'',
					order_pay_way:'',
					order_status:''
				},
				orders: [],
				total: 0,
				page: 1,
				listLoading: false,
				status_options:[
					{label:"待付款",value:1},{label:"已付款，待发货",value:2},{label:"已发货，待签收",value:3},{label:"已完成",value:5}
				],
				pay_options:[
					{label:"支付宝",value:1},
					{label:"微信",value:2},
					{label:"无需现金",value:3},
				],
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/order_list',
				addFormVisible:false,
				express_id:0

			}
		},
		methods: {
			writeExpress:function(id){
				this.express_id = id;
				this.addFormVisible = true;
			},
			formatAddr:function(row, column){
				return row.addr_province+row.addr_city+row.addr_area+row.addr_address+'  '+row.addr_realname+'  '+row.addr_mobile;
			},
			//库存
			formatPayWay: function (row, column) {
				if(row.order_pay_way  == 1){
					return  "支付宝";
				}else if(row.order_pay_way  == 2){
					return "微信";
				}else if(row.order_pay_way  == 3){
					return "其他";
				}else{
					return "未支付";
				}
			},
			//状态
			formatStatus: function (row, column) {
				if(row.order_status  == 1)	return  '待付款';
				if(row.order_status  == 2)	return  '已付款，待发货';
				if(row.order_status  == 3)	return  '已发货，待签收';
				if(row.order_status  == 5)	return  '已完成';
			},
			handleCurrentChange(val) {
				this.page = val;
				this.getOrders(2);
			},
			//获取用户列表
			getOrders(num) {
				if(num == 1)this.page = 1;
				// console.log()
				let params = {
				    headers: {
				        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				    },
				    user_id:this.user_id,
				    power_str:this.power_str,
					page: this.page,
					goods_title: this.filters.goods_title,
					order_code: this.filters.order_code,
					order_express_num:this.filters.order_express_num,
					order_pay_way:this.filters.order_pay_way,
					order_status:this.filters.order_status
				};

				// console.log(params);return false;
				this.listLoading = true;
				//NProgress.start();
				showOrderList(params).then((res) => {
					// console.log(res);return false;
					this.total = res.data.data.count;
					this.orders = res.data.data.orders;
					// console.log(this.orders);
					this.listLoading = false;
					//NProgress.done();
				}).catch((error) =>{
					sessionStorage.removeItem('user');
					this.$router.push('/login');
				});
			},
			//提交物流信息
			saveExpress: function (index) {
				this.$refs.addForm.validate((valid) => {
					if(valid){
						this.addLoading = true;
						// console.log(this.addForm);
						let para = { 
									headers: {
								        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
								    },
								    user_id:this.user_id,
					    			power_str:this.power_str,
									order_id: this.express_id,
									order_express:this.addForm.express.label,
									order_express_sn:this.addForm.express.value,
									order_express_num:this.addForm.express_num
								};
						addExpressMes(para).then((res) => {
							this.addLoading = false;
							this.addFormVisible = false;
							// console.log(res);
							if(res.data.code == 0){
								//NProgress.done();
								this.$message({
									message: '更新物流信息成功',
									type: 'success'
								});
								this.getOrders();
							}else{
								this.$message({
									message: res.data.msg,
									type: 'warning'
								});
							}
								
						}).catch((error) =>{
							sessionStorage.removeItem('user');
							this.$router.push('/login');
						});
					}	
				});
			},
			//查看订单详情
			handleDetail: function(id){
				// console.log(id);return false;
				this.$router.push({name:'订单详情',params:{id:id}});
			},
		},
		mounted() {
			this.getOrders(1);
		}
	};

</script>

<style scoped>

</style>