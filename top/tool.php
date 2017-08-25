<?php
/* 最終課題の商品管理ページ */
$host     = 'localhost';
$username = 'errrt';   // MySQLのユーザ名
$password = '';       // MySQLのパスワード
$dbname   = 'dugout';   // MySQLのDB名
$charset  = 'utf8';   // データベースの文字コード
   
// MySQL用のDNS文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

$img_dir    = './img/';  // 画像のディレクトリ

$sql_kind   = '';     // SQL処理の種類
$result_msg = '';     // 実行結果のメッセージ
$data       = [];     // DBから取得した値を格納する配列
$err_msg    = [];     // エラーメッセージを格納する配列

// SQL処理を取得
if (isset($_POST['sql_kind']) === TRUE) {
  $sql_kind = $_POST['sql_kind'];
}

// 商品追加の場合は最初に入力項目をチェックする
if ($sql_kind === 'insert') {

  $new_name   = '';
  $new_genre  = '';
  $new_price  = '';
  $released_year = '';
  $new_cond = '';
  $new_stock  = '';
  $new_status = '';
  $new_img    = '';

  if (isset($_POST['new_name']) !== TRUE || mb_strlen($_POST['new_name']) ===0) {
    $err_msg[] = "商品名を入力してください。";
  }else{
    // 半角・全角空白のトリム
    $new_name = preg_replace('/\A[　\s]*|[　\s]*\z/u', '', $_POST['new_name']);
  }
  
  if (isset($_POST['new_price']) !== TRUE || mb_strlen($_POST['new_price']) ===0) {
    $err_msg[] = "値段を入力してください。";
  }else if(preg_match('/^([1-9][0-9]*|0)$/', $_POST['new_price']) !== 1){
    //ただし、このままでは0もエラーになってしまうので/^([1-9][0-9]*|0)$/として、0も含むようにしましょう。
    $err_msg[] = '正しい形式で入力してください。';
  }else{
    // 半角・全角空白のトリム
    $new_price = preg_replace('/\A[　\s]*|[　\s]*\z/u', '', $_POST['new_price']);
  }
  
  if (isset($_POST['new_stock']) !== TRUE || mb_strlen($_POST['new_stock']) ===0) {
    $err_msg[] = "個数を入力してください。";
  }else if(preg_match('/^([1-9][0-9]*|0)$/', $_POST['new_stock']) !== 1){
    $err_msg[] = '正しい形式で入力してください。';
  }else{
    // 半角・全角空白のトリム
    $new_stock = preg_replace('/\A[　\s]*|[　\s]*\z/u', '', $_POST['new_stock']);
  }
  if (isset($_POST['new_released_year']) !== TRUE || mb_strlen($_POST['new_released_year']) ===0) {
    $err_msg[] = "発売年を入力してください。";
  }else{
    // 半角・全角空白のトリム
    $new_released_year = preg_replace('/\A[　\s]*|[　\s]*\z/u', '', $_POST['new_released_year']);
  }
  if (isset($_POST['new_genre']) !== TRUE || mb_strlen($_POST['new_genre']) ===0) {
    $err_msg[] = "ジャンルを入力してください。";
  }else{
    // 半角・全角空白のトリム
    $new_genre = preg_replace('/\A[　\s]*|[　\s]*\z/u', '', $_POST['new_genre']);
  }
  if (isset($_POST['new_cond']) !== TRUE || mb_strlen($_POST['new_cond']) ===0) {
    $err_msg[] = "コンディションを入力してください。";
  }else{
    // 半角・全角空白のトリム
    $new_cond = preg_replace('/\A[　\s]*|[　\s]*\z/u', '', $_POST['new_cond']);
  }
  
  //  HTTP POST でファイルがアップロードされたか確認
  if (is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE) {

    $new_img = $_FILES['new_img']['name'];

    // 画像の拡張子取得
    $extension = pathinfo($new_img, PATHINFO_EXTENSION);
    
    // 拡張子を小文字にする
    $extension = strtolower($extension);

    // 拡張子チェック
    if ($extension === 'jpg' || $extension == 'jpeg' || $extension == 'png') {

      // ユニークIDを生成し、保存ファイルの名前を変更する
      $new_img = md5(uniqid(mt_rand(), true)) . '.' . $extension;

      // 同名ファイルが存在するか確認
      if (is_file($img_dir . $new_img) !== TRUE) {

      if (move_uploaded_file($_FILES['new_img']['tmp_name'], $img_dir . $new_img) !== TRUE) {
             $err_msg[] = 'ファイルアップロードに失敗しました';
            } 

      } else {

        // 生成したIDがかぶることは通常ないため、IDの再生成ではなく再アップロードを促すようにする
        $err_msg[] = 'ファイルアップロードに失敗しました。再度お試しください。';
      }

    } else {
      $err_msg[] = 'ファイル形式が異なります。画像ファイルはJPEG又はPNGのみ利用可能です。';
    }

  } else {
    $err_msg[] = 'ファイルを選択してください。';
  }
    // 在庫変更の場合の入力項目チェック
  } else if ($sql_kind === 'update') {
    
      $update_stock = '';
      $item_id     = '';
  
  if (isset($_POST['update_stock']) !== TRUE) {
    $err_msg[] = "個数を入力してください。";
  }else if(preg_match('/^([1-9][0-9]*|0)$/', $_POST['update_stock']) !== 1){
    $err_msg[] = '正しい形式で入力してください。';
  }else{
    // 半角・全角空白のトリム
    $update_stock = preg_replace('/\A[　\s]*|[　\s]*\z/u', '', $_POST['update_stock']);
  } 
  
  if (isset($_POST['item_id']) === TRUE) {
    $item_id = $_POST['item_id'];
  }

    // ステータスの入力項目チェック
  } else if ($sql_kind === 'change') {
    
    $change_status = '';
    $item_id       = '';
  
  if (isset($_POST['change_status']) === TRUE) {
    $change_status = $_POST['change_status'];
  }else{
    $err_msg[] = 'ステータスを選択してください。';
  }
  
  if (isset($_POST['item_id']) === TRUE) {
    $item_id = $_POST['item_id'];
  }
  
      // 商品を削除する
  } else if ($sql_kind === 'delete') {
    
    $delete_item = '';
    $item_id       = '';
  
  if (isset($_POST['delete_item']) === TRUE) {
    $delete_item = $_POST['delete_item'];
  }
  
  if (isset($_POST['item_id']) === TRUE) {
    $item_id = $_POST['item_id'];
  }
}

try {
  // データベースに接続
  $dbh = new PDO($dsn, $username, $password);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  if (count($err_msg) === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
      
    // 現在日時を取得
    $now_date = date('Y-m-d H:i:s');
    
    // 商品追加の場合
    if ($sql_kind === 'insert') {
      
    try {
      // SQL文を作成
      $sql = 'INSERT INTO items (name, genre, price, released_year, cond, status, stock, img) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
      // SQL文を実行する準備
      $stmt = $dbh->prepare($sql);
      // SQL文のプレースホルダに値をバインド
      $stmt->bindValue(1, $new_name,    PDO::PARAM_STR);
      $stmt->bindValue(2, $new_genre,   PDO::PARAM_INT);
      $stmt->bindValue(3, $new_price,   PDO::PARAM_INT);
      $stmt->bindValue(4, $new_released_year,   PDO::PARAM_INT);
      $stmt->bindValue(5, $new_cond,     PDO::PARAM_STR);
      $stmt->bindValue(6, $new_status,  PDO::PARAM_INT);
      $stmt->bindValue(7, $new_stock,   PDO::PARAM_INT);
      $stmt->bindValue(8, $new_img,    PDO::PARAM_STR);
      // SQLを実行
      $stmt->execute();
      
      // 表示メッセージの設定
      $result_msg =  '追加成功';

    } catch (PDOException $e) {
      // 例外をスロー
      throw $e;
      }
      
    // 在庫変更
    } else if ($sql_kind === 'update') {
    try {
     // SQL文を作成
     $sql = 'UPDATE items SET stock = ? WHERE item_id = ?';
     // SQL文を実行する準備
     $stmt = $dbh->prepare($sql);
     // SQL文のプレースホルダに値をバインド
     $stmt->bindValue(1, $update_stock, PDO::PARAM_INT);
     $stmt->bindValue(2, $item_id,      PDO::PARAM_INT);
     // SQLを実行
     $stmt->execute();
     
     // 表示メッセージの設定
     $result_msg = '在庫変更成功';
     
    } catch (PDOException $e) {
     // 例外をスロー
     throw $e;
    }
    
    // ステータス変更
    }else if ($sql_kind === 'change') {
    try {
    // SQL文を作成
    $sql = 'UPDATE items SET status = ? WHERE item_id = ?';
    // SQL文を実行する準備
    $stmt = $dbh->prepare($sql);
     // SQL文のプレースホルダに値をバインド
     $stmt->bindValue(1, $change_status, PDO::PARAM_INT);
     $stmt->bindValue(2, $item_id,       PDO::PARAM_INT);
     // SQLを実行
     $stmt->execute();
     
     // 表示メッセージの設定
      $result_msg = 'ステータスを更新しました';
      
    } catch (PDOException $e) {
      // 例外をスロー
      throw $e;
    }
    
    // 商品削除
    }else if ($sql_kind === 'delete') {
    try {
    // SQL文を作成
    $sql = 'DELETE FROM items WHERE item_id = ?';
    // SQL文を実行する準備
    $stmt = $dbh->prepare($sql);
     // SQL文のプレースホルダに値をバインド
     $stmt->bindValue(1, $item_id,       PDO::PARAM_INT);
     // SQLを実行
     $stmt->execute();
     
     // 表示メッセージの設定
      $result_msg = '削除しました';
      
    } catch (PDOException $e) {
      // 例外をスロー
      throw $e;
    } 
  } 
  } 
  
  try {
    // SQL文を作成
    $sql = 'SELECT 
              items.item_id,
              items.name,
              items.genre,
              items.price,
              items.released_year,
              items.cond,
              items.status,
              items.stock,
              items.img
            FROM items';
    // SQL文を実行する準備
    $stmt = $dbh->prepare($sql);
    // SQLを実行
    $stmt->execute();
    // レコードの取得
    $rows = $stmt->fetchAll();

    // 1行ずつ結果を配列で取得します
    $i = 0;
    foreach ($rows as $row) {
      $data[$i]['item_id']   = htmlspecialchars($row['item_id'],   ENT_QUOTES, 'UTF-8');
      $data[$i]['name']      = htmlspecialchars($row['name'],      ENT_QUOTES, 'UTF-8');
      $data[$i]['genre']      = htmlspecialchars($row['genre'],      ENT_QUOTES, 'UTF-8');
      $data[$i]['price']     = htmlspecialchars($row['price'],     ENT_QUOTES, 'UTF-8');
      $data[$i]['released_year']      = htmlspecialchars($row['released_year'],      ENT_QUOTES, 'UTF-8');
      $data[$i]['cond']     = htmlspecialchars($row['cond'],     ENT_QUOTES, 'UTF-8');
      $data[$i]['status']    = htmlspecialchars($row['status'],    ENT_QUOTES, 'UTF-8');
      $data[$i]['stock']     = htmlspecialchars($row['stock'],     ENT_QUOTES, 'UTF-8');
      $data[$i]['img']       = htmlspecialchars($row['img'],       ENT_QUOTES, 'UTF-8');
      $i++;
    }

  } catch (PDOException $e) {
    // 例外をスロー
    throw $e;
  }
} catch (PDOException $e) {
  $err_msg[] = '予期せぬエラーが発生しました。管理者へお問い合わせください。'.$e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
 <meta charset="UTF-8">
 <title>dugout</title>
  <style>
    section {
      margin-bottom: 20px;
      border-top: solid 1px;
    }

    table {
      width: 660px;
      border-collapse: collapse;
    }

    table, tr, th, td {
      border: solid 1px;
      padding: 10px;
      text-align: center;
    }

    caption {
      text-align: left;
    }

    .text_align_right {
      text-align: right;
    }

    .name_width_width {
      width: 100px;
    }

    .input_text_width {
      width: 60px;
    }
    
    .status_false {
      background-color: #A9A9A9;
    }
  </style>
</head>
<body>
<?php if (empty($result_msg) !== TRUE) { ?>
  <p><?php print $result_msg; ?></p>
<?php } ?>
<?php foreach ($err_msg as $value) { ?>
  <p><?php print $value; ?></p>
<?php } ?>
  <h1>Post Products</h1>
  <a href="/top/top.php">back 2 top</a>
  <section>
    <h2>商品の登録</h2>
    <form method="post" enctype="multipart/form-data">
      <div><label>商品名： <input type="text" name="new_name" value=""></label></div>
      <div><label>ジャンル： <input type="text" name="new_genre" value=""></label></div>
    　<div><label>値段： <input type="text" name="new_price" value=""></label></div>
    　<div><label>発売年： <input type="text" name="new_released_year" value=""></label></div>
    　<div><label>状態：<select name="new_cond">
    　<option value="A">A</option>
    　<option value="B">B</option>
    　<option value="C">C</option>
    　<option value="D">D</option>
    　</select></label></div>
      <div><label>個数： <input type="text" name="new_stock" value=""></label></div>
      <div><label>商品画像： <input type="file" name="new_img"></label></div>
      <div><label>ステータス： <select name="new_status">
        <option value="0">非公開</option>
        <option value="1">公開</option></select></label>
      </div>
      <input type="hidden" name="sql_kind" value="insert">
      <div><input type="submit" value="商品を登録する"></div>
    </form>
  </section>
  <section>
    <h2>商品情報の一覧・変更</h2>
    <table>
     <tr>
       <th>商品画像</th>
       <th>商品名</th>
       <th>ジャンル</th>
       <th>発売年</th>
       <th>価格</th>
       <th>在庫数</th>
       <th>コンディション</th>
       <th>ステータス</th>
       <th>操作</th>
     </tr>
<?php foreach ($data as $value)  { ?>
<?php if ($value['status'] === '1') { ?>
     <tr>
<?php } else { ?>
     <tr class="status_false">
<?php } ?>
	<form method="post">
		<td><img src="<?php print $img_dir . $value['img']; ?>"></td>
		<td class="name_width"><?php print $value['name']; ?></td>
		<td class="text_align_right"><?php print $value['genre']; ?></td>
		<td class="text_align_right"><?php print $value['released_year']; ?></td>
		<td class="text_align_right"><?php print $value['price']; ?>円</td>
		<td><input type="text"  class="input_text_width text_align_right" name="update_stock" value="<?php print $value['stock']; ?>">個&nbsp;&nbsp;<input type="submit" value="変更"></td>
		<td class="text_align_right"><?php print $value['cond']; ?></td>
		
		<input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
		<input type="hidden" name="sql_kind" value="update">
	</form>
	<form method="post">
<?php if ($value['status'] === '1') { ?>
		<td><input type="submit" value="公開 → 非公開"></td>
		<input type="hidden" name="change_status" value="0">
<?php } else { ?>
    <td><input type="submit" value="公開 → 非公開"></td>
		<input type="hidden" name="change_status" value="1">
<?php } ?>
    <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
		<input type="hidden" name="sql_kind" value="change">
	</form>
	<form method="post">
	    <td><input type="submit" value="削除">
	    <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
	    <input type="hidden" name="sql_kind" value="delete">
	</form>
	    </td>
　　 </tr>
<?php } ?>
    </table>
  </section>
</body>
</html>