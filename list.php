<?php
require_once("functions.php");
/* ■　1.POSTメソッドに加え、GETメソッドで遷移してくる場合を追加
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST["name"])){
        if(!empty($_POST["name"])) {
            $name = htmlspecialchars($_POST["name"], ENT_QUOTES, 'UTF-8');
        }
    }
}
*/
define('MAXITEM',5);    // 最大表示件数
	
if($_SERVER['REQUEST_METHOD'] === 'POST'){     // 最初の条件検索時
    if(isset($_POST["name"])){
        $name = htmlspecialchars($_POST["name"], ENT_QUOTES, 'UTF-8');
    }
    $page = 1;   // 初期表示は1ページ
} elseif($_SERVER['REQUEST_METHOD'] === 'GET'){  // ページネーション時
    if (isset($_GET['page'])) {
        $page = $_GET['page'];    //[ページ番号を設定処理1] 
        $name = htmlspecialchars($_GET["name"], ENT_QUOTES, 'UTF-8');
    } else {
        $page = 1;    //[ページ番号を設定処理2] 
        $name = htmlspecialchars($_GET["name"], ENT_QUOTES, 'UTF-8');
    }

    // スタートのポジションを計算する
    // 取得するレコードの先頭位置を求める
    if ($page > 1) {
           // 例：２ページ目の場合は、『(2ページ目 × 最大表示件数) - 最大表示件数 = 5』
        $start = $page * MAXITEM - MAXITEM; //[レコードの先頭位置を計算]   // $start変数に設定
    } else {
        $start = 0;  //[レコードの先頭位置を設定]   // 1ページ目の場合は先頭 0
    }
}
//■　1.ここまで




$dbh = db_conn();
$data = [];

/* ●　2.データ取得のSQL文を変更
try{
    $sql = "SELECT * FROM user WHERE name like :name";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':name', '%'.$name.'%', PDO::PARAM_STR);
    $stmt->execute();
    $count = 0;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $data[] = $row;
        $count++;
    }
    */

try{
    $sql = "SELECT * FROM user WHERE name like :name LIMIT :start , :page /*[SQL文に追加]*/";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':name', '%'.$name.'%', PDO::PARAM_STR);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':page', MAXITEM, PDO::PARAM_INT);
    $stmt->execute();
    $count = 0;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $data[] = $row;
        $count++;
}
// ●　2.ここまで



}catch (PDOException $e){
    echo($e->getMessage());
    die();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>検索結果画面</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
       <div>
            <h1>ユーザー一覧</h1>
       </div>
    </header>
</div>
<hr>
<p><?php echo $count;?>件見つかりました。</p>
<table border=1>
    <tr><th>id</th><th>名前</th><th>メールアドレス</th><th>性別</th></tr>
    <?php foreach($data as $row): ?>
    <tr>
    <td><?php echo $row['id'];?></td>
    <td><?php echo $row['name'];?></td>
    <td><?php echo $row['email'];?></td>
    <td>
        <?php
           if ($row['gender'] === 1) {
              echo "男性";
           } elseif ($row['gender'] === 2) {
              echo "女性";
           } else {
              echo "その他";
           }
        ?>
    </td>
    </tr>
    <?php endforeach; ?>
</table>
<p style="margin:8px;">

<!--// ▲　3.ページの下にページ送りのリンクを表示させる
<form action="" method="POST">
<div class="button-wrapper">
    <button type="button" onclick="history.back()">戻る</button>
</div>
</form>  -->

<form action="" method="GET">
	
	<div>
	    <p>現在 <?php echo $page; ?> ページ目です。</p>
	<?php
	   $stmt = $dbh->prepare("SELECT COUNT(*) id FROM user WHERE name like :name");
	   $stmt->bindValue(':name', '%'.$name.'%', PDO::PARAM_STR);
	   $stmt->execute();
	   $page_num = $stmt->fetchColumn();
	   // ページネーションの数を取得する
	   $pagination = ceil($page_num / MAXITEM);  //[総ページ数の計算処理]
	?>
	<?php 
	   for ($x=1; $x <= $pagination ; $x++) {
	      if( $page == $x  /*[表示ページの場合]*/){
		      echo $x;
	      } else {
	          echo ' ';
	          echo '<a href=?page='. $x. '&name='. $name.'>'. $x. '</a>';
		      echo ' ';
		  }
	   }
	?>
	</div>
	
	<div class="button-wrapper">
	    <button type="button" onclick="history.back()">戻る</button>
	</div>
</form>
<!--// ▲　3.ここまで-->

<hr>
<div class="container">
    <footer>
        <p>CCC.</p>
    </footer>
</div>

</body>
</html>
