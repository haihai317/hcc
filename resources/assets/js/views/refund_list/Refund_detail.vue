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
        <h1>订单退款/退货/换货记录</h1>
        <div class="single">
            <h2>退换申请类型:</h2>
            <p v-if="refunds.refund_type == 1">仅退款</p>
            <p v-if="refunds.refund_type == 2">退货退款</p>
            <p v-if="refunds.refund_type == 3">换货</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>退换状态:</h2>
            <p v-if="refunds.refund_status == 1">买家申请，待处理</p>
            <p v-if="refunds.refund_status == 2">商家已退款</p>
            <p v-if="refunds.refund_status == 3">商家驳回请求</p>
            <p v-if="refunds.refund_status == 4">已同意退换，待买家寄回</p>
            <p v-if="refunds.refund_status == 5">买家寄回中，待确认</p>
            <p v-if="refunds.refund_status == 6">商家收到退货并完成退款</p>
            <p v-if="refunds.refund_status == 7">换货寄送中，待买家确认</p>
            <p v-if="refunds.refund_status == 8">买家收到换货并确认</p>
            <div class="clear"></div>
        </div>
        <div class="single">
            <h2>买家退换原因:</h2>
            <p>{{ refunds.refund_reason }}</p>
            <div class="clear"></div>
        </div>
        <!-- <div class="single">
            <h2>买家退换拍摄:</h2>
            <img :src="refunds.refund_img" alt="" style="display:block;float:left;width:200px;">
            <div class="clear"></div>
        </div> -->
        <div class="single" v-if="refunds.refund_status > 4">
            <h2>买家退换物流公司:</h2>
            <p>{{ refunds.refund_back_express }}</p>
            <div class="clear"></div>
        </div>
        <div class="single" v-if="refunds.refund_status > 4">
            <h2>买家退换物流单号:</h2>
            <p>{{ refunds.refund_back_expresssn }}</p>
            <div class="clear"></div>
        </div>
        <div class="single" v-if="refunds.refund_status == 7">
            <h2>卖家寄回物流公司:</h2>
            <p>{{ refunds.refund_send_express }}</p>
            <div class="clear"></div>
        </div>
        <div class="single" v-if="refunds.refund_status == 7">
            <h2>卖家寄回物流单号:</h2>
            <p>{{ refunds.refund_send_expresssn }}</p>
            <div class="clear"></div>
        </div>
        <!-- 处理功能按钮 -->
        <div class="single" style="width:75%">
            <button v-if="refunds.refund_status == 1 && refunds.refund_type == 1" class="dealButtons" @click="refuseBackMoney" style="background-color:#e6a23c">拒绝退款</button>
            <button v-if="refunds.refund_status == 1 && refunds.refund_type == 1" class="dealButtons" @click="agreeBackMoney">同意退款</button>
            <button v-if="refunds.refund_status == 1 && refunds.refund_type > 1" class="dealButtons" @click="refuseBackGoods" style="background-color:#e6a23c">拒绝退换</button>
            <button v-if="refunds.refund_status == 1 && refunds.refund_type > 1" class="dealButtons" @click="agreeBackGoods">同意退换</button>
            <button v-if="refunds.refund_status == 5 && refunds.refund_type == 2" class="dealButtons" @click="doBackMoney">收到退货并确认退款</button>
            <button v-if="refunds.refund_status == 5 && refunds.refund_type == 3" class="dealButtons" @click="writeExpress(refunds.refund_id)">收到退货并填写寄回物流信息</button>
            <div class="clear"></div>
        </div>
            




        <div style="width:100%;height:60px;"></div>


        <!--商家选择寄回地址-->
        <el-dialog title="选择寄回地址" :visible.sync="outerVisible" :close-on-click-modal="false">

            <el-form :model="backAddr" label-width="80px" >
                <el-form-item label="" >
                    <el-select v-model="backAddr.addr_id" placeholder="请选择寄回地址">
                        <el-option
                          v-for="item in addr_options"
                          :key="item.value"
                          :label="item.label"
                          :value="item.value"
                           size="large">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="text" @click.native="innerVisible = true">增加寄回地址</el-button>
                <el-button @click.native="outerVisible = false">取消</el-button>
                <el-button type="primary" @click.native="saveBackAddrMes" :loading="addLoading">提交</el-button>
            </div>

            <!-- 内嵌的dialog -->
            <el-dialog
              width="50%"
              title="添加寄回地址"
              :visible.sync="innerVisible"
              append-to-body>
                <el-form :model="addrForm" label-width="80px" :rules="addrFormRules" ref="addrForm">
                    <el-form-item label="选择省市区" prop="sanlian">
                        <el-cascader
                          size="large"
                          :options="options"
                          v-model="selectedOptions"
                          @change="handleChange">
                        </el-cascader>
                    </el-form-item>
                        
                    <el-form-item label="寄回详细地址" prop="addr_address">
                        <el-input v-model="addrForm.addr_address"></el-input>
                    </el-form-item>
                    <el-form-item label="收货人姓名" prop="addr_realname">
                        <el-input v-model="addrForm.addr_realname"></el-input>
                    </el-form-item>
                    <el-form-item label="联系电话" prop="addr_mobile">
                        <el-input v-model="addrForm.addr_mobile"></el-input>
                    </el-form-item>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button @click.native="addFormVisible = false">取消</el-button>
                    <el-button type="primary" @click.native="saveAddr" :loading="addLoading">提交</el-button>
                </div>
            </el-dialog>

        </el-dialog>



        <!--填写物流信息-->
        <el-dialog title="填写物流信息" :visible.sync="editFormVisible" :close-on-click-modal="false">
            <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">

                <el-form-item label="选择物流公司"  prop="express">
                    <el-select v-model="editForm.express" placeholder="请选择">
                        <el-option
                          v-for="item in express_options"
                          :key="item.value"
                          :label="item.label"
                          :value="item">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="物流单号" prop="express_num">
                    <el-input v-model="editForm.express_num"></el-input>
                </el-form-item>

            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="editFormVisible = false">取消</el-button>
                <el-button type="primary" @click.native="saveExpress" :loading="editLoading">提交</el-button>
            </div>
        </el-dialog>


    </section>
</template>

<script>
    import util from '../../common/js/util'
    import { showRefundDetail,refuseBackMoney,agreeBackMoney,backAddrList,saveBackAddr,saveBackAddrMes,doBackMoney,addRefundExpressMes  } from '../../api/api';
    // 省市区三级联动
    import { regionData,CodeToText } from 'element-china-area-data';

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
                listLoading:true,
                refund_id:0,
                // 填写物流信息弹窗
                outerVisible:false,
                addLoading:false,
                addr_options:[],
                backAddr:{
                    addr_id:""
                },
                innerVisible:false,
                // 省市区三级联动
                options:regionData,
                selectedOptions: [],
                addrForm:{
                    addr_province:'',
                    addr_city:'',
                    addr_area:'',
                    addr_address:'',
                    addr_realname:'',
                    addr_mobile:''
                },
                addrFormRules:{
                    addr_address:[
                        { required: true, message: '请填写寄回详细地址', trigger: 'blur' }
                    ],
                    addr_mobile: [
                        { required: true, message: '请填写收货人电话', trigger: 'blur' }
                    ],
                    addr_realname: [
                        { required: true, message: '请填写收货人姓名', trigger: 'blur' }
                    ]
                },
                editForm:{
                    express:{},
                    express_num:''
                },
                editFormRules: {
                    express: [
                        { required: true, message: '请选择物流公司', trigger: 'blur' }
                    ],
                    express_num: [
                        { required: true, message: '请填写物流单号', trigger: 'blur' }
                    ]
                },
                editLoading:false,
                express_options:[{label:'德邦物流',value:'debangwuliu'},{label:'ems快递',value:'ems'},{label:'国通快递',value:'guotongkuaidi'},{label:'汇通快运',value:'huitongkuaidi'},{label:'佳吉物流',value:'jjwl'},{label:'联邦快递（国内）',value:'lianb'},{label:'民航快递',value:'minghangkuaidi'},{label:'全际通物流',value:'quanjitong'},{label:'申通',value:'shentong'},{label:'顺丰',value:'shunfeng'},{label:'天地华宇',value:'tiandihuayu'},{label:'天天快递',value:'tiantian'},{label:'万家物流',value:'wanjiawuliu'},{label:'优速物流',value:'youshuwuliu'},{label:'圆通速递',value:'yuantong'},{label:'韵达快运',value:'yunda'},{label:'中通速递',value:'zhongtong'}],
                editFormVisible:false
            }
        },

        methods: {
            orderDetail:function(){
                this.refund_id = this.$route.params.id;
                // console.log(this.order_id);return false;
                let para = { 
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        user_id:this.user_id,
                        power_str:this.power_str,
                        refund_id: this.refund_id
                    };
                showRefundDetail(para).then((res) => {
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
            },
            // 商家同意退款
            agreeBackMoney:function(){
                this.listLoading = true;
                let para = { 
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        user_id:this.user_id,
                        power_str:this.power_str,
                        refund_id: this.refunds.refund_id
                    };
                agreeBackMoney(para).then((res) => {
                    this.listLoading = false;
                    console.log(res);
                    if(res.data.code == 0){
                        this.orderDetail();
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
            },
            // 商家收到退货并同意退款
            doBackMoney:function(){
                this.listLoading = true;
                let para = { 
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        user_id:this.user_id,
                        power_str:this.power_str,
                        refund_id: this.refunds.refund_id
                    };
                doBackMoney(para).then((res) => {
                    this.listLoading = false;
                    console.log(res);
                    if(res.data.code == 0){
                        this.orderDetail();
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
            },
            // 商家拒绝退款
            refuseBackMoney:function(){
                this.listLoading = true;
                let para = { 
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        user_id:this.user_id,
                        power_str:this.power_str,
                        refund_id: this.refunds.refund_id
                    };
                refuseBackMoney(para).then((res) => {
                    this.listLoading = false;
                    console.log(res);
                    if(res.data.code == 0){
                        this.orderDetail();
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
            },
            // 查找商家的收货地址列表
            agreeBackGoods:function(){
                this.outerVisible = true;
                let para = { 
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        user_id:this.user_id,
                        power_str:this.power_str
                    };
                backAddrList(para).then((res) => {
                    // console.log(res);
                    if(res.data.code == 0){
                        this.addr_options = res.data.data;
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
            },
            // 商家拒绝退换货
            refuseBackGoods:function(){

            },
            // 实时保存选择的省市区的数据
            handleChange:function(value){
                this.addrForm.addr_province = CodeToText[value[0]];
                this.addrForm.addr_city = CodeToText[value[1]];
                this.addrForm.addr_area = CodeToText[value[2]];
            },
            // 保存新添加的数据
            saveAddr:function(){
                let para = { 
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        user_id:this.user_id,
                        power_str:this.power_str,
                        addrForm:this.addrForm
                    };
                saveBackAddr(para).then((res) => {
                    // console.log(res);
                    if(res.data.code == 0){
                        this.addr_options.push({"value":res.data.data.addr_id,"label":res.data.data.addr_province+" "+res.data.data.addr_city+" "+res.data.data.addr_area+" "+res.data.data.addr_address+" "+res.data.data.addr_realname+" "+res.data.data.addr_mobile});
                        // 清空addrForm
                        this.addrForm = [];
                        // 关闭内部弹窗
                        this.innerVisible = false;
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
            },
            // 商家提交选择好的寄回地址，并将退换单状态改为，等待买家退还
            saveBackAddrMes:function(){
                let para = { 
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        user_id:this.user_id,
                        power_str:this.power_str,
                        addr_id:this.backAddr.addr_id,
                        refund_id:this.refunds.refund_id
                    };
                saveBackAddrMes(para).then((res) => {
                    // console.log(res);
                    if(res.data.code == 0){
                        this.outerVisible = false;
                        this.orderDetail();
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
            },
            writeExpress:function(id){
                this.express_id = id;
                this.editFormVisible = true;
            },
            //提交物流信息
            saveExpress: function (index) {
                this.$refs.editForm.validate((valid) => {
                    if(valid){
                        this.editLoading = true;
                        // console.log(this.editForm);return false;
                        let para = { 
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    user_id:this.user_id,
                                    power_str:this.power_str,
                                    refund_id: this.refunds.refund_id,
                                    refund_send_express:this.editForm.express.label,
                                    refund_send_expresssn:this.editForm.express_num
                                };
                        addRefundExpressMes(para).then((res) => {
                            this.editLoading = false;
                            this.editFormVisible = false;
                            // console.log(res);
                            if(res.data.code == 0){
                                //NProgress.done();
                                this.$message({
                                    message: '更新物流信息成功',
                                    type: 'success'
                                });
                                this.orderDetail();
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
    .dealButtons{
        display:block;
        height:40px;
        border:0px;
        font-size:15px;
        background-color:#67c23a;
        border-radius:5px;
        margin:30px 30px 10px 0;
        padding:0 15px;
        color:#fff;
        float:right;
    }
    .el-form{
        width:100%;
    }
    .el-select {
        width: 80%;
    }
    .el-button+.el-button {
        margin-left: 10px;
        padding: 10px 20px;
    }
</style>
