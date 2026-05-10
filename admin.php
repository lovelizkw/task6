<?php
$user = 'u82353';
$pass = '3228865';

try {
    $db = new PDO('mysql:host=localhost;dbname=u82353', $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="Admin Area"');
        header('HTTP/1.0 401 Unauthorized');
        exit('<h1>401 Требуется авторизация</h1>');
    }

    $stmt = $db->prepare("SELECT password_hash FROM admin_auth WHERE login = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
        header('WWW-Authenticate: Basic realm="Admin Area"');
        header('HTTP/1.0 401 Unauthorized');
        exit('<h1>401 Неверный логин или пароль</h1>');
    }
} catch (PDOException $e) {
    exit('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <style>
        body{font-family:sans-serif;background:#fff1f6;padding:20px;}
        table{border-collapse:collapse;width:100%;background:white;margin-bottom:30px;box-shadow:0 2px 10px rgba(0,0,0,0.05);}
        th,td{border:1px solid #ff80ab;padding:12px;text-align:left;}
        th{background:#ff80ab;color:white;}
        tr:nth-child(even){background:#fff9fb;}
        .btn-del{color:#e53e3e;text-decoration:none;font-weight:bold;}
    </style>
</head>
<body>
    <h1>Панель администратора</h1>

    <h2>Статистика по языкам</h2>
    <table>
        <tr><th>Язык</th><th>Количество</th></tr>
        <?php
        $stmt = $db->query("SELECT l.name, COUNT(al.language_id) as count 
                            FROM languages l 
                            LEFT JOIN application_languages al ON l.id = al.language_id 
                            GROUP BY l.id");
        while ($row = $stmt->fetch()) {
            echo "<tr><td>".htmlspecialchars($row['name'])."</td><td>{$row['count']}</td></tr>";
        }
        ?>
    </table>

    <h2>Список анкет</h2>
    <table>
        <tr><th>ID</th><th>ФИО</th><th>Email</th><th>Действия</th></tr>
        <?php
        $stmt = $db->query("SELECT id, fio, email FROM applications");
        while ($row = $stmt->fetch()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . htmlspecialchars($row['fio']) . "</td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>
                        <a href='edit.php?id={$row['id']}'>Редактировать</a> | 
                        <a href='delete.php?id={$row['id']}' class='btn-del' onclick='return confirm(\"Удалить?\")'>Удалить</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>
