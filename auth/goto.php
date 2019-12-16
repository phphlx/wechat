<?php

$appid = 'wx7813d1c7bff1a7f7';
$secret = '7ee50ab06cd6b382fa8b0707cf31944b';
$redirect_uri = urlencode('http://665de7d6.ngrok.io/auth/shop.php'); // urlencode 处理
$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=100#wechat_redirect';
$url = sprintf($url, $appid, $redirect_uri);

//echo $url;
//die;
header('Location:' . $url);
