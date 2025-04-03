<?php
// Parsedownライブラリを読み込む
require_once 'Parsedown.php';

// Parsedownインスタンスを作成
$parsedown = new Parsedown();

// POSTリクエストからcontentテキストを取得
$content = isset($_POST['content']) ? $_POST['content'] : '';



// content_htmlをHTMLに変換
$content_html = $parsedown->text($content);

// HTMLを出力
echo $content_html;

