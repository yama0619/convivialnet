<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>投稿</title>
</head>
<body>
    <h1>投稿ページ</h1>
    <form action="submit_post.php" method="post">
        <label>内容：</label><textarea name="content" required></textarea><br>
        <input type="submit" value="投稿">
    </form>
    <a href="index.php">ホームに戻る</a>
</body>
</html>
