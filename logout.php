<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>家計簿ログインページ</title>
</head>
  <style>
body {
  background-color: #e8f8f8;
  font-family: serif;
}
</style>
<body>
<?php
    session_start();
    $_SESSION=array();
    session_destroy();
?>
<hi>ログアウトしました。</hi>
<br>
<a href="login.php">登録ページへ進む</a>
<a href="loguin2.php">ログインページへ進む</a>
</body>
</html>