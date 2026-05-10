<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit;
}

$db_user = 'u82353';
$db_pass = '3228865';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=u82353;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $login = $_POST['login'];
        $pass = $_POST['password'];
        
        // Ищем в правильной таблице user_auth
        $stmt = $pdo->prepare("SELECT * FROM user_auth WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['login'] = $user['login'];
            $_SESSION['user_id'] = $user['id']; // Это ID из таблицы user_auth
            header('Location: index.php');
            exit;
        } else {
            $login_error = "Неверный логин или пароль";
        }
    }
} catch (PDOException $e) {
    exit('Ошибка БД: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <style>
        body{font-family:sans-serif;background:#fff5f7;display:flex;justify-content:center;padding:40px 0;}
        .container{width:400px;background:white;border-radius:15px;box-shadow:0 4px 15px rgba(216,27,96,0.1);padding:40px;border:1px solid #fce4ec;}
        h1{color:#d81b60;text-align:center;margin-top:0;}
        label{display:block;margin:15px 0 5px;font-weight:bold;color:#880e4f;}
        input{width:100%;padding:12px;border:1px solid #f8bbd0;border-radius:8px;box-sizing:border-box;margin-bottom:15px;}
        button{background:#d81b60;color:white;padding:15px;border:none;border-radius:8px;cursor:pointer;width:100%;font-weight:bold;}
        .error{color:#e53e3e;background:#fed7d7;padding:10px;border-radius:6px;text-align:center;margin-bottom:15px;}
        .nav{text-align:center;margin-top:20px;}
        .nav a{color:#d81b60;text-decoration:none;}
    </style>
</head>
<body>
<div class="container">
    <h1>Вход</h1>
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
</body>
</html>
