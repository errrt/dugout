<?php
/* 最終課題のログアウトページ */

// セッション開始
session_start();
// セッション名取得 ※デフォルトはPHPSESSID
$session_name = session_name();
// セッション変数を全て削除
$_SESSION = array();
// ユーザのCookieに保存されているセッションIDを削除
if (isset($_COOKIE[$session_name])) {
  setcookie($session_name, '', time() - 42000);
}
// セッションIDを無効化
session_destroy();
// ログアウトの処理が完了したらログインページへリダイレクト
header('Location: /entry/entry.php');
exit;
?>