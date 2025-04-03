<?php
require 'db.php';

$id = $_GET['id'];

// 投稿から画像データを取得
$stmt = $conn->prepare("SELECT image_data, image_type FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($image_data, $image_type);
$stmt->fetch();

if ($image_data) {
    header("Content-Type: $image_type");
    echo $image_data;
} else {
    echo "画像が見つかりません。";
}
?>
