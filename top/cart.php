<?php
/* 最終課題のカート一覧ページ */
$host = 'localhost';
$username = 'errrt';   // MySQLのユーザ名
$password = '';     // MySQLのパスワード
$dbname = 'dugout';   // MySQLのDB名
$charset = 'utf8';   // データベースの文字コード
// MySQL用のDNS文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
$img_dir = './img/';  // 画像のディレクトリ
$sql_kind = '';   // SQL処理の種類
$err_msg  = [];   // エラーメッセージを格納する配列
$msg      = [];     //エラー以外のメッセージを格納する配列
$item_id  = '';
$total    = 0;

//セッション開始
session_start();

if(isset($_SESSION['user_id']) === TRUE){
  $user_id = $_SESSION['user_id'];
}else{
  header('location: login.php');//ログインしていなければ、ログイン画面へリダイレクト
}
  try {
    // データベースに接続
    $dbh = new PDO($dsn, $username, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
      // var_dump($_POST);
      if(isset($_POST['sql_kind']) === TRUE){
          $sql_kind= $_POST['sql_kind'];
      }
      
      if(isset($_POST['item_id']) === TRUE){
        $item_id = $_POST['item_id'];
      }
      
      if($sql_kind === 'change_amount'){
        if(isset($_POST['change_amount']) === TRUE){
          $amount = $_POST['change_amount'];
        }
        // var_dump($amount);
        $sql = 'UPDATE carts SET amount = ? WHERE item_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $amount,     PDO::PARAM_INT);
        $stmt->bindValue(2, $item_id,    PDO::PARAM_INT);
        $stmt->execute();
        $msg[] = '購入数を変更しました。';
        
      }else if($sql_kind === 'delete_cart_item'){
        //カートからの削除処理
        $sql = 'DELETE FROM carts WHERE item_id = ? AND user_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $item_id,   PDO::PARAM_INT);
        $stmt->bindValue(2, $user_id,    PDO::PARAM_INT);
        $stmt->execute();
        $msg[] = '削除しました。';
      }
    }
    
    //ログインユーザーのカート一覧を取得
    $sql = 'SELECT
                 carts.user_id,
                 carts.item_id,
                 carts.amount,
                 items.name,
                 items.price,
                 items.img
            FROM carts JOIN items
            ON carts.item_id = items.item_id
            WHERE carts.user_id = ?';
    
    // SQL文を実行する準備        
    $stmt = $dbh->prepare($sql);
    // SQL文のプレースホルダに値をバインド
    $stmt->bindValue(1, $user_id,    PDO::PARAM_INT);
    // SQLを実行
    $stmt->execute();
    // レコードの取得
    $cart_list = $stmt->fetchAll();
    //var_dump($cart_list);
 
    $total = 0;
    foreach($cart_list as $cart_item){
      $total += $cart_item['amount'] * $cart_item['price'];
    }
    //var_dump($total);
    
  } catch (PDOException $e) {
    // 例外をスロー
    //throw $e;
    echo 'データベース処理でエラーが発生しました。理由：'.$e->getMessage();
 }
function h($str){
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <title>dugout</title>

</head>
<body>
  <!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>-->
  <!--<script type="text/javascript" src="js/masonry.pkgd.min.js"></script>-->
  <header>
  <div class="container">
    <a href="/top/top.php">
    </a>
    <div class="logout">
    <a href="logout.php">ログアウト</a>
    </div>
  </div>
  </header>
  <main>
  <div class="cart_list">
    <h1>ショッピングカート</h1>
  <table class="cart-table">
  <tr>
    <th>画像</th>
    <th>商品名</th>
    <th>金額</th>
    <th>購入数</th>
    <th>削除</th>
  </tr>
<?php foreach ($cart_list as $cart_item)  { ?>
      <tr class="cart-item">
        <td>
          <span class="item_img_size"><img src="<?php print h($img_dir . $cart_item['img']); ?>"></span>
        </td>
        <td>
          <span><?php print h($cart_item['name']); ?></span>
        </td>
        <td>
          <span class="cart-item-price"><?php print h($cart_item['price']); ?>円</span>
        </td>
        <td>
          <form method="post">
            <input type="text"  class="input_text_width text_align_right" name="change_amount" value="<?php print h($cart_item['amount']); ?>">個&nbsp;&nbsp;<input type="submit" value="変更">
            <input type="hidden" name="item_id" value="<?php print h($cart_item['item_id']); ?>">
            <input type="hidden" name="sql_kind" value="change_amount">
          </form>
        </td>
        <td>
          <form method="post">
            <input type="submit" value="削除">
            <input type="hidden" name="item_id" value="<?php print h($cart_item['item_id']); ?>">
    	    　<input type="hidden" name="sql_kind" value="delete_cart_item">
          </form>
        </td>
      </tr>
<?php } ?>
    </table>
    <div class="buy-sum-box">
      <span class="buy-sum-title">合計:</span>
      <span class="buy-sum-price"><?php print h($total); ?>円</span>
    </div>
    <div class="buy">
      <form action="/top/comp_purchase.php" method="post">
        <input class="buy-btn" type="submit" value="購入する">
      </form>
    </div>
  </div>
    </main>
  <footer>
    <!--<div class="container">-->
      <div class="footer-navi">
      <small>Copyright&copy;dugout All Rights Reserved.</small>
    </div>
    <!--</div>-->
  </footer>
</body>
</html>