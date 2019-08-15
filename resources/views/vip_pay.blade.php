<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="format-detection" content="telephone=no,email=no,date=no,address=no">
    <title>苍耳会VIP购买</title>
    <style>
        * {padding: 0; margin: 0;}
        input[type="text"]:focus,
        input[type="password"]:focus {
            border: 1px solid #eb7350;
            background: #fff;
            outline: none;
        }
        input::-webkit-input-placeholder {color: #aaaaaa;}
        body {padding: 0 15px;}
        .title {margin-top: 40px; text-align: center;}
        .vip_con {display: flex; flex-flow: row; margin: 30px 0;}
        .vip_item {flex: 1; margin: 0 10px; border: 1px solid #cccccc; height: 70px;
            line-height: 70px; text-align: center; color: #aaaaaa; border-radius: 5px;font-weight: bold;}
        .phone-div {padding: 10px;}
        .vip_item.active {border: 1px solid #1d8ce0; color: #1d8ce0;}
        #phone {width: 100%; margin: 20px 0; height: 40px; border: 1px solid #aaaaaa !important;
            border-radius: 5px; text-align: center;}
        .pay-fee {text-align: center; color: #f00; font-size: 20px; font-weight: bold;}
        #submit-btn {height: 45px; width: 60%; margin: 30px auto; background: #1d8ce0; color: #ffffff;
            text-align: center; line-height: 45px; border-radius: 5px;}
    </style>
</head>
<body style="background: #fff">
<div class="vip-content">
    <h3 class="title">苍耳会礼包购买</h3>
    <form action="" method="post" id="my_form">
        {{csrf_field()}}
        <input type="hidden" name="vip_type" id="vip_type" value="{{$vips[0]->id}}"/>
        <div class="vip_con">
            @foreach($vips as $k => $v)
            <div class="vip_item {{$k==0?'active':''}}" _data="{{$v}}">{{$v->name}}</div>
            @endforeach
        </div>
        <div class="pay-fee">￥<span>{{$vips[0]->price}}</span></div>
        <div class="phone-div"><input type="text" name="phone" id="phone" placeholder="请输入苍耳会注册手机号"/></div>
        <div id="submit-btn">购买</div>
    </form>
</div>
</body>

<script type="text/javascript" src="{{asset('js/jquery.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/layer/layer.js')}}"></script>
<script type="text/javascript">
$(function() {
    var O_type = $('#vip_type');
    var O_phone = $('#phone');
    var myForm = $('#my_form');
    $('.vip_item').click(function() {
        $('.vip_item').removeClass('active');
        $(this).addClass('active');
        var data = $(this).attr('_data');
        data = JSON.parse(data);
        $('#vip_type').val(data.id);
        $('.pay-fee>span').text(data.price);
    });

    $('#submit-btn').click(function() {
        if (!O_phone.val()) {
            layer.open({content: '请输入手机号',skin: 'msg',time: 2});
            return false;
        }
        let p1 = /^[1][2,3,4,5,6,7,8,9][0-9]{9}$/;
        if (!p1.test(O_phone.val())) {
            layer.open({content: '手机号格式有误',skin: 'msg',time: 2});
            return false;
        }
        layer.open({type: 2,shadeClose: false});
        myForm.submit();
    });


});
</script>
</html>