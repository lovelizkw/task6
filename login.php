<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit;
}

$host = 'localhost'; $dbname = 'u82353'; $username = 'u82353'; $password = '3228865';
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $pass = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($pass, $user['password_hash'])) {
        $_SESSION['login'] = $user['login'];
        $_SESSION['user_id'] = $user['id']; 
        header('Location: index.php');
        exit;
    } else {
        $login_error = "Неверный логин или пароль";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <style>
        body{font-family:sans-serif;background:#fff5f7;display:flex;justify-content:center;padding:40px 0;}
        .container{width:400px;background:white;border-radius:15px;box-shadow:0 4px 15px rgba(216,27,96,0.1);overflow:hidden; border: 1px solid #fce4ec;}
        header{background:white;color:#d81b60;padding:30px;text-align:center; border-bottom: 1px solid #fce4ec;}
        h1 {margin:0; font-size: 24px;}
        .form-body{padding:40px;}
        label{display:block;margin:15px 0 5px;font-weight:bold; color: #880e4f;}
        input{width:100%;padding:12px;border:1px solid #f8bbd0;border-radius:8px;font-size:16px;box-sizing:border-box;margin-bottom:15px;}
        input:focus{border-color:#d81b60;outline:none;}
        button{background:#d81b60;color:white;padding:15px;font-size:16px;font-weight:bold;border:none;border-radius:8px;cursor:pointer;width:100%; transition:0.3s;}
        button:hover{background:#c2185b;}
        .error{color:#e53e3e;margin-bottom:15px;text-align:center; background: #fed7d7; padding: 10px; border-radius: 6px;}
        .nav {text-align:center; margin-top:20px;}
        .nav a {color:#d81b60; text-decoration:none; font-weight:bold;}
    </style>
</head>
<body>
<div class="container">
    <header><h1>Вход</h1></header>
    <div class="form-body">
        <?php if (isset($login_error)) echo '<div class="error">'.$login_error.'</div>'; ?>
        <form method="post">
            <label>Логин</label>
            <input type="text" name="login" required>
            <label>Пароль</label>
            <input type="password" name="password" required>
            <button type="submit">Войти</button>
        </form>
        <div class="nav"><a href="index.php">На главную</a></div>
    </div>
</div>
</body>
</html>
