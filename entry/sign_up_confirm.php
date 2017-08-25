<?php
session_start();

$db = mysqli_connect('localhost', 'root', '', 'dugout') or die(mysqli_connect_erro());
mysqli_set_charset($db, 'utf8');

if(!isset($_SESSION['signup'])){
	header('Location: /entry/sign_up.php');
	exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	//登録処理をする
	$sql = sprintf('INSERT INTO users SET username="?", email="?", password="?"',
		mysqli_real_escape_string($db, $_SESSION['signup']['username']),
		mysqli_real_escape_string($db, $_SESSION['signup']['email']),
		mysqli_real_escape_string($db, sha1($_SESSION['signup']['password']))
	);
	mysqli_query($db, $sql) or die(mysqli_error($db));
	unset($_SESSION['signup']);

	header('Location: /signup/view/sign_up_completion.php');
	exit();
}

?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>confirm</title>
    </head>
    <body>
        <form action="" method="post">
            <dl>
                <dt>username</dt>
                <dd>
                    <?php echo htmlspecialchars($_SESSION['signup']['username'], ENT_QUOTES,'UTF-8'); ?>
                </dd>
                <dt>mail</dt>
                <dd>
                    <?php echo htmlspecialchars($_SESSION['signup']['email']); ?>
                </dd>
                <dt>password</dt>
                <dd>
                 hidden for the security    
                </dd>
            </dl>
            <div><a href="/entry/sign_up.php?action=rewrite">修正する</a>
            <input type="submit" value="register"></div>
        </form>
    </body>
</html>