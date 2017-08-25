<?php
session_start();

$db = mysqli_connect('localhost', 'root', '', 'dugout') or die(mysqli_connect_erro());
mysqli_set_charset($db, 'utf8');

session_start();

if(!empty($_POST)){
    //login
    if($_POST['email'] !== "" && $_POST['password'] !==""){
        $sql = sprintf('SELECT * FROM users WHERE email="?" AND password="?"',
            mysqli_real_escape_string($db, $_POST['email']),
            mysqli_real_escape_string($db, $_POST['password'])
        );
        $record = mysqli_query($db, $sql) or die($mysqli_error($db));
        if($table = mysqli_fetch_assoc($record)){
            
            //success
            header('Location: /signup/view/dogout_signup.php');
            exit();
        }else{
            $error['login'] ='failed';
        }
    }else{
        $error['login'] = 'blank';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset = "UTF-8">
        <title>dugout</title>
        <link rel="stylesheet" href="prd.css">
        <link rel="stylesheet" href="entry.css">
    </head>
    <body>
    <div id="entry">
        <h1 id="entry_logo">dugout</h1>
        <h3 id="entry_sub">accelarate your favourits</h3>
        <div id="entry_option">
            <div id="signin">
                <input type="email" name="email" placeholder="email">
                <input type="text" name="password" placeholder="password">
                <input type="submit" name="login" value="Sign&nbsp;in">
            </div>
        <div id="signup"><a href="/signup/view/dogout_signup.php">sign&nbsp;up</a></div>
        </div>
    </div>
    </body>
</html>