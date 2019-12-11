<?php

$pdo = include 'db.php';
include 'WeChat.php';
$files = $_FILES['media'];

$ext = pathinfo($files['name'], PATHINFO_EXTENSION);
$name = time() . '.' . $ext;

$realPath = __DIR__ . '/upload/img/' . $name;
move_uploaded_file($files['tmp_name'], $realPath);

$sql = "insert into material(real_path, create_time, is_forever, media_id) values(?,?,?,?)";
//预处理
$statement = $pdo->prepare($sql);
//上传素材到公众平台
$wx = new WeChat();
$media_id =  $wx->uploadMaterial($realPath, 'image', $_POST['is_forever']);
//执行
$result = $statement->execute([$realPath, time(), $_POST['is_forever'], $media_id]);

echo $media_id;
