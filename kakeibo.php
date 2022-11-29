<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>家計簿本体ページ</title>
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
     $week = array("日", "月", "火", "水", "木", "金", "土");
    if(!empty($_GET['log'])){
        $logname = $_GET['log'];
    }
    if(!empty($_GET['namelog'])){
        $logname = $_GET['namelog'];
    }
?>
<h1 class='midashi_1'> <?php echo "$logname"?>さんの家計簿</h1>
<form action="" method="post">
        <input type="date" name="dat" >
        <input type="text" name="comment" placeholder="コメント">
        <input type="text" name="money" placeholder="支出">
        <input type="text" name="moneyin" placeholder="収入">
        <button type="submit">送信</button>
</form>
<?php
    $moneysum = 0;
    $moneyinsum = 0;
    $count = 0;
    $countin = 0;
    //データベース情報
    $dsn='';
    $user='';
    $password='';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    //ユーザー用テーブル作成
    $sql2 = "CREATE TABLE IF NOT EXISTS book"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "dat TEXT,"
    . "comment TEXT,"
    . "money INT,"
    . "moneyin INT"
    .");";
    $stmt = $pdo->query($sql2);
    //日付が入力された時以下が実行される
    if(!empty($_POST["dat"])){
        if(is_numeric($_POST["money"]) or is_numeric($_POST["moneyin"])){
            $sql2 = $pdo -> prepare("INSERT INTO book (name, dat, comment, money, moneyin) VALUES (:name,:dat,:comment,:money, :moneyin)");
            $sql2 -> bindParam(':name', $logname, PDO::PARAM_STR);
            $sql2 -> bindParam(':dat', $dat, PDO::PARAM_STR);
            $sql2 -> bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql2 -> bindParam(':money', $money, PDO::PARAM_INT);
            $sql2 -> bindParam(':moneyin', $moneyin, PDO::PARAM_INT);
            $comment = $_POST["comment"];
            $dat = $_POST["dat"];
            $money =$_POST["money"];
            $moneyin =$_POST["moneyin"];
            $sql2 -> execute();
            //echo "$dat "."$comment "."$money"."円"."<br>";
        }
    }
    $sql2 = 'SELECT * FROM book WHERE name = :name';
    $stmt = $pdo->prepare($sql2);
    $stmt -> bindParam(':name', $logname, PDO::PARAM_STR);
    $stmt -> execute();
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        if(!empty($row['money'])){
            $a = $row['dat'];
            $b = $row['money'];
            $k = explode("-",$a);
            $c["$count"] = $a;
            $value_count = array_count_values($c); // 各値の出現回数を数える
            $max = max($value_count); // 最大の出現回数を取得する
            if ($max == 1) {
                $forward[$c["$count"]] = $b;//連想配列
            //var_dump($forward);
            //var_dump($c);
            }else{//同じ日にちの場合
                $e = array_search("$a", $c);//一番最初に$cで$aが出てくる時のキー取得
                $forward[$c["$e"]] += $b;
                unset($c["$count"]);//要素消す
                array_values($c);//要素を詰める
                $count -= 1;
            //var_dump($forward);
            //var_dump($c);
            }
        //echo "$a"."の時"."$b"."<br>";;
            $moneysum += $b;
            $count += 1;
            //echo $count;
        }elseif(!empty($row['moneyin'])){
            $a = $row['dat'];
            $b = $row['moneyin'];
            $k = explode("-",$a);
            $in["$countin"] = $a;
            $value_count = array_count_values($in); // 各値の出現回数を数える
            $max = max($value_count); // 最大の出現回数を取得する
            if ($max == 1) {
                $income[$in["$countin"]] = $b;//連想配列
            //var_dump($income);
            //var_dump($in);
            //echo $in["$countin"];
            }else{//同じ日にちの場合
                $e = array_search("$a", $in);//一番最初に$cで$aが出てくる時のキー取得
                $income[$in["$e"]] += $b;
            //echo "kこれは"."$e";
                unset($in["$countin"]);//要素消す
                array_values($in);//要素を詰める
                $countin -= 1;
            //var_dump($income);
            //var_dump($in);
            }
        //echo "$a"."の時"."$b"."<br>";;
            $moneysum -= $b;
            $countin += 1;
            //echo $countin;
        }
    }
    if(!$count==0){
        array_multisort(array_map("strtotime",$c),SORT_ASC,$c);
        foreach($c as $q){
            $qq=explode("-",$q);
            $qqq[] = join("",$qq);
            $cc[]=$qq[1];//月
            $ccc[]=$qq[2];//日付
            $datetime = new DateTime($q);
            $w = (int)$datetime->format('w');
            $cccc[] = $w;//曜日を数値化
            //echo $w;
        }
        ksort($forward);
        $array = array_values($forward);
        $jx = json_encode($qqq, JSON_UNESCAPED_UNICODE);
        $jy = json_encode($array, JSON_UNESCAPED_UNICODE);
        //var_dump($jx);
        //var_dump($income);
    }
    if(!$countin == 0){
        array_multisort(array_map("strtotime",$in),SORT_ASC,$in);
        foreach($in as $q){
            $qq=explode("-",$q);
            $inq[] = join("",$qq);
            $inm[] = $qq[1];
            $ind[] = $qq[2];
            $datetime = new DateTime($q);
            $w = (int)$datetime->format('w');
            $inw[] = $w;
            //echo $w;
        }
        ksort($income);
        $arrayin = array_values($income);
        $jxin = json_encode($inq, JSON_UNESCAPED_UNICODE);
        $jyin = json_encode($arrayin, JSON_UNESCAPED_UNICODE);
    }
    if(!$countin==0 or !$count==0){
        echo "<br>"."全体の合計金額 "."$moneysum"."円"."<br>";
        $average=$moneysum/$count+$countin;
        echo "全体の1日あたりの金額"."$average"."円"."<br>"."<br>";
    }
?>
 <form action="" method="post">
  <label>月ごとの金額</label>
  <br>
  <input type="month" name="month" required >
  <button type="submit">送信</button>
</form>
<br>
<form action="" method="post">
<label>曜日ごとの金額</label>
<br>
<select name="dow" >
	<option value="0">日曜日</option>
	<option value="1">月曜日</option>
	<option value="2">火曜日</option>
	<option value="3">水曜日</option>
	<option value="4">木曜日</option>
	<option value="5">金曜日</option>
	<option value="6">土曜日</option>
</select>
<input type="submit" value="送信">
</form>
<br>
 <form action="" method="post">
  <label>1日の金額</label>
  <br>
  <input type="date" name="oneday" required >
  <button type="submit">送信</button>
</form>
<?php
    //日付ごとに支出入出力
    if(!empty($_POST["oneday"])){
        $oneday = $_POST["oneday"];
        $outday = 0;
        $inday = 0;
        if(!$count == 0){
            if(in_array($_POST["oneday"],$c)){
                $outday = $forward[$ondday];
            echo "支出は".$outday."円です";
        }
        }
        if(!$countin == 0){
        if(in_array($_POST["oneday"],$in)){
            $inday = $income["$oneday"];
            echo "収入は".$inday."円です";
        }
        }
        $dayresult = $outday+$inday;
        if($dayresult == 0){
            echo "この日付の支出入はありません。";
        }
    }
    
?>
 <?php
    if(!empty($_POST["month"])){
        $month = explode("-",$_POST["month"]);
        $moneymonth = 0;
        $f=0;
        $fin=0;
        if(!$count==0){
            if(in_array($month[1],$cc)){//$ccの中に$monthがあった場合
                $key = array_keys($cc,$month[1]);//$ccの中の$monthを全部抽出
                $f = count($key);//$keyの数を数える
                for($j=0;$j<$f;$j++){
                    $day[] = $c[$key[$j]];//日付取得
                    $arraymonth[] = $array[$key[$j]];
                    $moneymonth += $array[$key[$j]];
                }
                $jxmonth = json_encode($day, JSON_UNESCAPED_UNICODE);
                $jymonth = json_encode($arraymonth, JSON_UNESCAPED_UNICODE);
            }
        }
        if(!$countin==0){
            if(in_array($month[1],$inm)){//$ccの中に$monthがあった場合
                $key = array_keys($inm,$month[1]);//$ccの中の$monthを全部抽出
                $fin = count($key);//$keyの数を数える
                for($j=0;$j<$fin;$j++){
                    $dayin[] = $in[$key[$j]];//日付取得
                    $arraymonthin[] = $arrayin[$key[$j]];
                    $moneymonth -= $arrayin[$key[$j]];
                }
                $jxmonthin = json_encode($dayin, JSON_UNESCAPED_UNICODE);
                $jymonthin = json_encode($arraymonthin, JSON_UNESCAPED_UNICODE);
            }
        }
        if(!empty($moneymonth)){
            $monthaverage = $moneymonth / ($f + $fin);
            $maxd = max(max($day),max($dayin));
            $maxday = explode("-",$maxd);
            echo $maxday[2];
            if($month == 1 or $month == 3 or $month == 5 or $month == 7 or $month == 8 or $month == 10 or $month == 12){
                $monthsim = $monthaverage*31;
                $monthsim2 = (31-$maxday[2])*$monthaverage;
                echo "<br>"."$month[1]"."月の現在の合計金額"."$moneymonth"."円";
                echo "<br>"."$month[1]"."月の合計金額予想"."$monthsim"."円";
                echo "<br>"."$month[1]"."月の今後の使用金額予想"."$monthsim2"."円";
            }
            elseif($month == 2){
                $monthsim = $monthaverage*28;
                $monthsim2 = (28-$maxday[2])*$monthaverage;
                echo "<br>"."$month[1]"."月の現在の合計金額"."$moneymonth"."円";
                echo "<br>"."$month[1]"."月の合計金額予想"."$monthsim"."円";
                echo "<br>"."$month[1]"."月の今後の使用金額予想"."$monthsim2"."円";
            }else{
                $monthsim = $monthaverage*30;
                $monthsim2 = (30-$maxday[2])*$monthaverage;
                echo "<br>"."$month[1]"."月の現在の合計金額"."$moneymonth"."円";
                echo "<br>"."$month[1]"."月の合計金額予想"."$monthsim"."円";
                echo "<br>"."$month[1]"."月の今後の使用金額予想"."$monthsim2"."円";
            }
        }
        if($count==0 and $countin==0){
            echo "<br>".$month[1]."月の支出入はありません。";
        }
    }
?>

<?php
    //入力された曜日の支出入を出力
    if(!empty($_POST["dow"])){
        $moneyweek = 0;
        $dow = $_POST["dow"];
        $f2=0;
        $fin2=0;
        if(!$count == 0){
            if(in_array($dow,$cccc)){
                $key2 = array_keys($cccc,$dow);
                $f2 = count($key2);
                for($j=0;$j<$f2;$j++){
                    $day2[] = $c[$key2[$j]];
                    $arrayweek[] = $array[$key2[$j]];
                    $moneyweek += $array[$key2[$j]];
                }
                $jxweek = json_encode($day2, JSON_UNESCAPED_UNICODE);
                $jyweek = json_encode($arrayweek, JSON_UNESCAPED_UNICODE);
            }
        }
        if(!$countin == 0){
            if(in_array($dow,$inw)){
                $key2 = array_keys($inw,$dow);
                $fin2 = count($key2);
                for($j = 0;$j<$fin2;$j++){
                    $day2in[] = $in[$key2[$j]];
                    $arrayweekin[] = $arrayin[$key2[$j]];
                    $moneyweek -= $arrayin[$key2[$j]];
                }
            }
        }
        if(!$moneyweek == 0){
            $weekaverage = $moneyweek / ($f2+$fin2);
            echo "<br>"."$week[$dow]"."曜日の合計金額"."$moneyweek"."円";
            echo "<br>"."$week[$dow]"."曜日の1日あたりの金額"."$weekaverage"."円";
        }else{
            echo "<br>"."$week[$dow]"."曜日の支出入はありません。";
        }
    }
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<canvas id="myLineChart" style="width: 100%; height:300px;"></canvas>
 <script>
//phpから値を受け取る
let x = JSON.parse('<?php echo $jxmonth; ?>');
let y = JSON.parse('<?php echo $jymonth; ?>');
//以下，グラフを表示
var ctx = document.getElementById("myLineChart");
  var myLineChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: x,
      datasets: [
        {
          label: '支出',
          data: y,
        }
      ],
    },
    options: {
      title: {
        display: true,
        text: '<?php echo $month[1]?>月の支出'
      },
      scales: {
        yAxes: [{
          ticks: {
            suggestedMax: <?php echo max($arraymonth); ?>,
            suggestedMin: 0,
            stepSize: <?php echo max($arraymonth)/10; ?>,
            callback: function(value, index, values){
              return  value +  '円'
            }
          }
        }]
      },
    }
  });
Chart.plugins.register({
    afterDatasetsDraw: function (chart, easing) {
        var ctx = chart.ctx;
        chart.data.datasets.forEach(function (dataset, i) {
            var meta = chart.getDatasetMeta(i);
            if (!meta.hidden) {
                meta.data.forEach(function (element, index) {
                    ctx.fillStyle = 'rgb(0, 0, 0)';
                    var fontSize = 14;
                    var fontStyle = 'normal';
                    var fontFamily = 'Helvetica Neue';
                    ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);
                    var dataString = dataset.data[index].toString()+"円";
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    var padding = 5;
                    var position = element.tooltipPosition();
                    ctx.fillText(dataString, position.x, position.y - (fontSize / 2) - padding);
                });
            }
        });
    }
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<canvas id="myLineChart" style="width: 100%; height:300px;"></canvas>
 <script>
//phpから値を受け取る
let x = JSON.parse('<?php echo $jxweek; ?>');
let y = JSON.parse('<?php echo $jyweek; ?>');
//以下，グラフを表示
var ctx = document.getElementById("myLineChart");
  var myLineChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: x,
      datasets: [
        {
          label: '支出',
          data: y,
        }
      ],
    },
    options: {
      title: {
        display: true,
        text: '<?php echo $week[$dow]?>曜日の支出'
      },
      scales: {
        yAxes: [{
          ticks: {
            suggestedMax: <?php echo max($arrayweek); ?>,
            suggestedMin: 0,
            stepSize: <?php echo max($arrayweek)/10; ?>,
            callback: function(value, index, values){
              return  value +  '円'
            }
          }
        }]
      },
    }
  });
Chart.plugins.register({
    afterDatasetsDraw: function (chart, easing) {
        var ctx = chart.ctx;
        chart.data.datasets.forEach(function (dataset, i) {
            var meta = chart.getDatasetMeta(i);
            if (!meta.hidden) {
                meta.data.forEach(function (element, index) {
                    ctx.fillStyle = 'rgb(0, 0, 0)';
                    var fontSize = 14;
                    var fontStyle = 'normal';
                    var fontFamily = 'Helvetica Neue';
                    ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);
                    var dataString = dataset.data[index].toString()+"円";
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    var padding = 5;
                    var position = element.tooltipPosition();
                    ctx.fillText(dataString, position.x, position.y - (fontSize / 2) - padding);
                });
            }
        });
    }
});
//ログアウトボタンを押されたらログアウトのページに遷移
</script>
<a href="logout.php">ログアウト</a>
</body>
</html>
