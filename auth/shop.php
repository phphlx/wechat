<?php
$appid = 'wx7813d1c7bff1a7f7';
$secret = '7ee50ab06cd6b382fa8b0707cf31944b';
$code = $_GET['code'];
$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';
$url = sprintf($url, $appid, $secret, $code);
$json = httpRequest($url);
$arr = json_decode($json, true);
$access_token = $arr['access_token'];
$open_id = $arr['openid'];
$url = 'https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN';
$url = sprintf($url, $access_token, $open_id);
$userinfo = json_decode(httpRequest($url), true);

function httpRequest($url, $postArr = '', $file = '')
{
    if (!empty($file)) {
        $postArr['media'] = new CURLFile($file);
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MSIE001');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    if ($postArr) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postArr);
    }

    $data = curl_exec($ch);

    if (curl_errno($ch) > 0) {
        echo curl_error($ch);
        $data = '';
    }

    curl_close($ch);
    return $data;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>首页</title>
</head>
<body>
<div>
    <p>openid: <?php echo $open_id;?></p>
    <p>nick: <?php echo $userinfo['nickname'];?></p>
    <p>sex: <?php echo $userinfo['sex'] == 1 ? '帅哥' : '美女' ;;?></p>
    <p><img src="<?php echo $userinfo['headimgurl']?>" alt=""></p>
</div>
</body>
</html>