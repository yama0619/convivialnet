<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // 画像処理
    $image_data = null;
    $image_type = null;

    if (!empty($_FILES['image']['tmp_name'])) {
        $image_data = file_get_contents($_FILES['image']['tmp_name']); // 画像ファイルをバイナリ形式に変換
        $image_type = $_FILES['image']['type']; // 画像のMIMEタイプを取得
    }

    // データベースに保存
    $stmt = $conn->prepare("INSERT INTO posts (title, content, image_data, image_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $content, $image_data, $image_type);

    if ($stmt->execute()) {
        echo "投稿が成功しました。";
    } else {
        echo "エラー: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>新規投稿</title>
</head>
<body>
    <h1>新規投稿</h1>
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="タイトル" required><br>
        <textarea name="content" placeholder="内容" required></textarea><br>
        <input type="file" name="image" accept="image/*"><br>
        <button type="submit">投稿</button>
    </form>
</body>
</html>
