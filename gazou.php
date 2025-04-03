<?php require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // マークダウンをHTMLに変換
    $content_html = markdownToHtml($content);

    // データベースに保存
    $stmt = $conn->prepare("INSERT INTO posts (title, content, content_html) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $content_html);

    if ($stmt->execute()) {
        echo "投稿が成功しました。";
    } else {
        echo "エラー: " . $stmt->error;
    }
}

// マークダウンをHTMLに変換する関数
function markdownToHtml($markdown) {
    // マークダウンをHTMLに変換するライブラリ（例: Parsedown）を使用
    require 'Parsedown.php';
    $Parsedown = new Parsedown();
    return $Parsedown->text($markdown);
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
        <textarea name="content" placeholder="マークダウン形式で内容を入力" required></textarea><br>
        <button type="submit">投稿</button>
    </form>
</body>
</html>
