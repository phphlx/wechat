<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>素材管理</title>
</head>
<body>
<form action="upSave.php" method="post" enctype="multipart/form-data">
    <p>
        素材类型:
        <select name="is_forever">
            <option value="0">临时</option>
            <option value="1">永久</option>
        </select>
    </p>
    <p>
        <input type="file" name="media" id="">
    </p>
    <p>
        <input type="submit" value="提交素材">
    </p>
</form>
</body>
</html>

<!--https://api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE-->
<!--https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=ACCESS_TOKEN-->
