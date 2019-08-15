<template>
    <section class="chart-container"  v-loading="listLoading" >
        <h1>订单信息</h1>
        <div class="single">
            <h2>会员信息:</h2>
            <p>[昵称]{{ order.user_name }}  [联系方式]{{ order.user_phone }}</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>订单编号:</h2>
            <p>{{ order.order_code }}</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>订单状态:</h2>
            <p v-if="order.order_status == 1">未支付</p>
            <p v-if="order.order_status == 2">待发货</p>
            <p v-if="order.order_status == 3">待确认收货</p>
            <p v-if="order.order_status == 5">已完成</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>订单金额:</h2>
            <p>{{ order.order_goods_money + order.order_trans_money }}元</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>收件信息:</h2>
            <p>{{ order.addr_province}} {{ order.addr_city }} {{ order.addr_area }} {{ order.addr_address }} {{ order.addr_realname}} {{ order.addr_mobile }}</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>物流信息:</h2>
            <p>[物流公司]{{ order.order_express }}  [快递单号]{{ order.order_express_num }}</p>
            <div class="clear"></div>
            <button v-if="order.order_express_sn" class="buttons" @click="showExpressMes">查询物流进度</button>
        </div>
        <div class="single">
            <h2>支付方式:</h2>
            <p v-if="order.order_pay_way == 1">支付宝</p>
            <p v-if="order.order_pay_way == 2">微信</p>
            <p v-if="order.order_pay_way == 3">其他</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>支付单号:</h2>
            <p>{{ order.order_pay_code }}</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>订单备注:</h2>
            <p>{{ order.order_remark }}</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>订单生成时间:</h2>
            <p>{{ order.order_createtime }}</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>订单支付时间:</h2>
            <p>{{ order.order_paytime }}</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>订单发货时间:</h2>
            <p>{{ order.order_sendtime }}</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>订单完成时间:</h2>
            <p>{{ order.order_finishtime }}</p>
            <div class="clear"></div>
        </div>
        <h1>订单商品信息</h1>
        <div v-for="(item,index) in goods">
            <div class="single">
                <h2>商品{{ index+1 }}:</h2>
                <div class="clear"></div>
            </div>
            <div class="single">
                <h2>商品名称:</h2>
                <p>{{ item.og_goods_title }}</p>
                <div class="clear"></div>
            </div>
            <div class="single">
                <h2>商品规格:</h2>
                <p>{{ item.og_goods_specname }}</p>
                <div class="clear"></div>
            </div>
            <div class="single">
                <h2>购买数量:</h2>
                <p>{{ item.og_goods_num }}</p>
                <div class="clear"></div>
            </div>
        </div>
    </section>
</template>

<script>
    import util from '../../common/js/util'
    import { showOrderDetail  } from '../../api/api';

    export default {
        data() {
            return {
                order_id:0,
                // 调用接口时，验证用户权限的两个参数
                user_id:sessionStorage.getItem('user_id'),
                power_str:'/order_list',
                order:[],
                goods:[],
                refunds:[],
                listLoading:true
            }
        },

        methods: {
            orderDetail:function(){
                this.order_id = this.$route.params.id;
                // console.log(this.order_id);return false;
                let para = { 
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        user_id:this.user_id,
                        power_str:this.power_str,
                        order_id: this.order_id
                    };
                showOrderDetail(para).then((res) => {
                    this.listLoading = false;
                    console.log(res);
                    if(res.data.code == 0){
                        this.order = res.data.data.order;
                        this.goods = res.data.data.goods;
                        this.refunds = res.data.data.refunds;
                    }else{
                        this.$message({
                            message: res.data.msg,
                            type: 'warning'
                        });
                        this.$router.go(-1);
                    }
                        
                }).catch((error) =>{
                    sessionStorage.removeItem('user');
                    this.$router.push('/login');
                });
            },
            showExpressMes:function(){
                // window.location.href="https://m.kuaidi100.com/app/query/?coname="+this.order.order_express_sn+"&nu="+this.order.order_express_num;
                window.open("https://www.kuaidi100.com/chaxun?com="+this.order.order_express_sn+"&nu="+this.order.order_express_num);
            }
        },

        mounted: function () {
            this.orderDetail();
        }
    }
</script>

<style scoped>
    *{
        margin:0;
        padding:0;
        font-size: 14px;
        font-weight:normal;
    }
    .clear{
        clear:both;
    }
    h1{
        color:#333;
        font-size:16px;
        width:90%;
        margin-left:5%;
        border-bottom: 1px solid #aaa;
        height: 50px;
        line-height: 70px;
    }
    .single{
        width:100%;
    }
    h2{
        float:left;
        color:#555;
        font-size:14px;
        width:20%;
        height: 30px;
        line-height: 30px;
        text-align:right;
    }
    p{
        float:left;
        color:#555;
        font-size:14px;
        width:80%;
        line-height: 30px;
        text-indent:15px;
    }
    .buttons{
        display:block;
        height:30px;
        border:0px;
        background-color:#409EFF;
        border-radius:3px;
        margin:10px 0 10px 20%;
        padding:0 15px;
        color:#fff;
    }
</style>
