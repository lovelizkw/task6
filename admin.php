<?php
$user = 'u82353';
$pass = '3228865';
$db = new PDO('mysql:host=localhost;dbname=u82353', $user, $pass);

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

$stmt = $db->prepare("SELECT password_hash FROM admin_auth WHERE login = ?");
$stmt->execute([$_SERVER['PHP_AUTH_USER']]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    print('<h1>401 Неверный логин или пароль</h1>');
    exit();
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админка</title>
    <style>
        body { font-family: sans-serif; background: #fff1f6; padding: 20px; }
        table { border-collapse: collapse; width: 100%; background: white; margin-bottom: 30px; }
        th, td { border: 1px solid #ff80ab; padding: 10px; text-align: left; }
        th { background: #ff80ab; color: white; }
    </style>
</head>
<body>
    <h1>Панель администратора</h1>

    <h2>Статистика языков программирования</h2>
    <table>
        <tr><th>Язык</th><th>Количество любителей</th></tr>
        <?php
        $stmt = $db->query("SELECT l.name, COUNT(al.language_id) as count 
                            FROM languages l 
                            LEFT JOIN application_languages al ON l.id = al.language_id 
                            GROUP BY l.id");
        while ($row = $stmt->fetch()) {
            echo "<tr><td>{$row['name']}</td><td>{$row['count']}</td></tr>";
        }
        ?>
    </table>

    <h2>Все пользователи</h2>
    <table>
        <tr><th>ФИО</th><th>Дата рождения</th><th>Email</th><th>Действия</th></tr>
        <?php
        $stmt = $db->query("SELECT * FROM applications");
        while ($row = $stmt->fetch()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['fio']) . "</td>
                    <td>{$row['birth_date']}</td>
                    <td>{$row['email']}</td>
                    <td>
                        <a href='edit.php?id={$row['id']}'>Редактировать</a> | 
                        <a href='delete.php?id={$row['id']}' onclick='return confirm(\"Удалить?\")'>Удалить</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>
