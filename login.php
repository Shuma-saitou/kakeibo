<!DOCTYPE html>
<html lang="ja">
    <link rel="stylesheet" href="kakeibo.css">
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
<h1 class='midashi_1'>会員登録 </h1>
<?php
    session_start();
    $a=0;
    $dsn = '';
    $user = '';
    $password = '';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    //ユーザー用テーブル作成
    $sql = "CREATE TABLE IF NOT EXISTS account"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "pass TEXT"
    .");";
    $stmt = $pdo->query($sql);
    //アカウント新規登録
    if(!empty($_POST["str"])){
        if($_POST["passsub"] === ""){
        }else{
            $sql = $pdo -> prepare("INSERT INTO account (name, pass) VALUES (:name, :pass)");
            $sql -> bindParam(':name', $name, PDO::PARAM_STR);
            $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
            $name = $_POST["str"];
            $pass = $_POST["passsub"];
            $sql -> execute();
            $_SESSION["logname"] = $_POST["str"];
            $a = $_SESSION['logname'];
        }
    }
    //ログイン状態の場合ログイン後のページにリダイレクト
    if (!empty($a)) {
        session_regenerate_id(TRUE);
        header("Location: kakeibo.php?log=".$_SESSION["logname"]);
        exit();
    }
?>
<form action="" method="post">
        <input type="text" name="str" placeholder="名前入力欄" >
        <input type="text" name="passsub" placeholder="パスワード">
        <br>
        <input type="submit" value="登録" />
</form>
</body>
</html>