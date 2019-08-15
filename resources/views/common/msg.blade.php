<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <style>
        * {margin: 0;}
        a {text-decoration:none; color:#333; font-size: 12px;
            margin-top: 10px;
            display: block;}
        .mycontent {text-align: center}
        .mycontent img{
            height: 100px;
            width: 100px;
            margin-top: 65px;
        }
        .mycontent p {margin-top: 35px; color: #666;}
    </style>
</head>
<body>
    <div class="mycontent">
        @if($data['type']=='success')
            <img src="{{asset('image/icon_register_success.png')}}" alt="" />
        @else
            <img src="{{asset('image/icon_register_faild.png')}}" alt="" />
        @endif
        <p>{{$data['msg']}}。{{$data['type']=='success'?'自动跳转中...':''}}</p>
        @if($data['type'] == 'success')
            <a href="{{$data['url']}}">如果没有跳转，请点击这里。</a>
        @endif
    </div>
    @if($data['type'] == 'success')
    <script language="javascript">setTimeout("goUrl('{{ $data['url']}}')", 2000);</script>
    @endif
</body>
<script>
    function goUrl(url) {
        location.href = url;
    }
</script>
</html>