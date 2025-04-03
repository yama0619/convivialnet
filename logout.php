<?php
session_start( );
session_unset();  // セッション変数をすべて削除
session_destroy();  // セッション自体を破棄
$_SESSION = array( );
header( 'Location: http://'.$_SERVER[ 'HTTP_HOST' ].'/convivialnet/index.php');
exit;
?>