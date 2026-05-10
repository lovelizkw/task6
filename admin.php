<?php
session_start();
$user = 'u82353'; 
$pass = '3228865';

try {
    $db = new PDO('mysql:host=localhost;dbname=u82353', $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    if (isset($_GET['do']) && $_GET['do'] == 'logout') {
        unset($_SESSION['admin_ok']);
        header('Location: admin.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
        $stmt = $db->prepare("SELECT password_hash FROM admin_auth WHERE login = ?");
        $stmt->execute([$_POST['admin_login']]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($_POST['admin_pass'], $admin['password_hash'])) {
            $_SESSION['admin_ok'] = true;
        } else {
            $error = "Неверный логин или пароль";
        }
    }
} catch (PDOException $e) {
    exit('Error: ' . $e->getMessage());
}

if (empty($_SESSION['admin_ok'])) {
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админку</title>
    <style>
        body{font-family:sans-serif;background:#fff1f6;display:flex;justify-content:center;padding-top:100px;}
        .login-box{background:white;padding:30px;border-radius:15px;box-shadow:0 4px 15px rgba(0,0,0,0.1);width:350px;text-align:center;}
        h2{color:#d81b60;}
        input{width:100%;padding:12px;margin:10px 0;border:1px solid #ff80ab;border-radius:8px;box-sizing:border-box;}
        button{width:100%;background:#d81b60;color:white;border:none;padding:12px;border-radius:8px;cursor:pointer;font-weight:bold;}
        .err{color:#e53e3e;background:#fed7d7;padding:10px;border-radius:6px;margin-bottom:15px;}
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Вход в админ-панель</h2>
        <?php if(isset($error)) echo "<div class='err'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="admin_login" placeholder="Логин" required>
            <input type="password" name="admin_pass" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <style>
        body{font-family:sans-serif;background:#fff1f6;padding:20px;}
        .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
        table{border-collapse:collapse;width:100%;background:white;box-shadow:0 2px 10px rgba(0,0,0,0.05);}
        th,td{border:1px solid #ff80ab;padding:12px;text-align:left;}
        th{background:#ff80ab;color:white;}
        tr:nth-child(even){background:#fff9fb;}
        .logout{background:#d81b60;color:white;padding:8px 15px;text-decoration:none;border-radius:5px;}
    </style>
</head>
<body>
    <div class="header">
        <h1>Панель администратора</h1>
        <a href="?do=logout" class="logout">Выйти</a>
    </div>

    <h2>Список анкет</h2>
    <table>
        <tr><th>ID</th><th>ФИО</th><th>Email</th></tr>
        <?php
        $stmt = $db->query("SELECT id, fio, email FROM applications");
        while ($row = $stmt->fetch()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . htmlspecialchars($row['fio']) . "</td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>
