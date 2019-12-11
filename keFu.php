<?php
if ($_POST['message']) {
    $openid = $_POST['openid'];
    $message = $_POST['message'];

    include 'WeChat.php';
    $wx = new WeChat();
    $result = $wx->keFuMessage($openid, $message);

    var_dump($result);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>客服消息</title>
</head>
<body>
<form action="" method="post">
    <input type="text" name="openid" value="o1InK1InaiLBLQ0K0S-6zK_BGrRc">
    <input type="text" name="message">
    <input type="submit" value="发送消息">
</form>
</body>
</html>
