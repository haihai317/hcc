<template>
	<section>
		<!--工具条-->
		<el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
			<el-form :inline="true" :model="filters">
				<el-form-item>
					<el-input v-model="filters.order_code" placeholder="订单号"></el-input>
				</el-form-item>
				<el-form-item>
					<el-select v-model="filters.refund_status" placeholder="退换订单状态">
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
			<el-table-column prop="order_pay_way" label="支付方式"  :formatter="formatPayWay">
			</el-table-column>
			<el-table-column prop="order_paytime" label="支付时间" >
			</el-table-column>
			<el-table-column prop="order_status" label="退换订单状态" :formatter="formatStatus">
			</el-table-column>
			<el-table-column label="操作" fixed="right">
				<template slot-scope="scope">
					<el-button size="small" type="primary" plain @click="handleDetail(scope.row.refund_id)">处理退换订单</el-button>
				</template>
			</el-table-column>
		</el-table>

		<!--工具条-->
		<el-col :span="24" class="toolbar">
			<!-- <el-button type="danger" @click="batchRemove" :disabled="this.sels.length===0">批量删除</el-button> -->
			<el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="10" :total="total" style="float:right;">
			</el-pagination>
		</el-col>

	</section>
</template>

<script>
	import util from '../../common/js/util'
	//import NProgress from 'nprogress'
	import { showRefundList } from '../../api/api';

	export default {
		data() {
			return {
				filters: {
					order_code:'',
					refund_status:''
				},
				orders: [],
				total: 0,
				page: 1,
				listLoading: false,
				status_options:[
					{label:"买家申请，待处理",value:1},{label:"已同意退换，待买家寄回",value:4},{label:"买家寄回中，待确认",value:5},{label:"换货寄送中，待买家确认",value:7}
				],
				// 调用接口时，验证用户权限的两个参数
				user_id:sessionStorage.getItem('user_id'),
				power_str:'/order_list',
			}
		},
		methods: {
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
				if(row.refund_status  == 1)	return  '买家申请，待处理';
				if(row.refund_status  == 4)	return  '已同意退换，待买家寄回';
				if(row.refund_status  == 5)	return  '买家寄回中，待确认';
				if(row.refund_status  == 7)	return  '换货寄送中，待买家确认';
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
					order_code: this.filters.order_code,
					refund_status:this.filters.refund_status
				};

				// console.log(params);return false;
				this.listLoading = true;
				//NProgress.start();
				showRefundList(params).then((res) => {
					// console.log(res);return false;
					this.total = res.data.data.count;
					this.orders = res.data.data.orders;
					// console.log(this.orders);
					this.listLoading = false;
					//NProgress.done();
				}).catch((error) =>{
					// sessionStorage.removeItem('user');
					// this.$router.push('/login');
				});
			},
			//查看处理详情
			handleDetail: function(id){
				// console.log(id);return false;
				this.$router.push({name:'退换详情',params:{id:id}});
			},
		},
		mounted() {
			this.getOrders(1);
		}
	};

</script>

<style scoped>

</style>