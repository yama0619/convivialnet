<?php
$host = "localhost";
$dbname = "convivialnet"; // データベース名を設定
$username = "root";  
$password = "yama333"; //ConvPass00

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("接続失敗: " . $conn->connect_error);
}
?>
