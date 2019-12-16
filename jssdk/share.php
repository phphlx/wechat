<?php

include '../WeChat.php';
$wx = new WeChat();
$config = $wx->signature();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>砍一刀share</title>
    <script src="js/jweixin-1.4.0.js"></script>
    <script>
        // 权限配置
        wx.config({
            debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: '<?php echo $config["appid"]; ?>', // 必填，公众号的唯一标识
            timestamp:<?php echo $config['timestamp']; ?>, // 必填，生成签名的时间戳
            nonceStr: '<?php echo $config["noncestr"]; ?>', // 必填，生成签名的随机串
            signature: '<?php echo $config["signature"]; ?>',// 必填，签名
            jsApiList: [
                'updateAppMessageShareData',
                'updateTimelineShareData',
                'chooseImage',
            ] // 必填，需要使用的JS接口列表
        });
        //验证成功后的动作
        wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
            wx.updateAppMessageShareData({
                title: 'person', // 分享标题
                desc: '分享给个人', // 分享描述
                link: 'http://665de7d6.ngrok.io/qrcode.jpg', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: 'http://665de7d6.ngrok.io/qrcode.jpg', // 分享图标
                success: function () {
                    // 设置成功
                    alert('分享给个人接口ok');
                }
            });

            wx.ready(function () {      //需在用户可能点击分享按钮前就先调用
                wx.updateTimelineShareData({
                    title: '成功了', // 分享标题
                    link: 'http://665de7d6.ngrok.io/qrcode.jpg', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                    imgUrl: 'http://665de7d6.ngrok.io/qrcode.jpg', // 分享图标
                    success: function () {
                        // 设置成功ee
                        alert('分享到朋友圈接口ok')
                    }
                })
            });

            wx.chooseImage({
                count: 1, // 默认9
                sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                success: function (res) {
                    var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
                }
            });

        });
    </script>
</head>
<body>

</body>
</html>