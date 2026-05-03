<?php
require_once('db.php');

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
} else {
    $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE login = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        exit;
    }
}

if (isset($_POST['del_id'])) {
    $id = (int)$_POST['del_id'];
    $pdo->prepare("DELETE FROM user_languages WHERE user_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM user_auth WHERE user_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    header('Location: admin.php'); exit;
}

$users = $pdo->query("SELECT u.*, a.login FROM users u LEFT JOIN user_auth a ON u.id = a.user_id")->fetchAll();
$stats = $pdo->query("SELECT l.name, COUNT(ul.user_id) as count FROM languages l LEFT JOIN user_languages ul ON l.id = ul.language_id GROUP BY l.id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Админка</title></head>
<body style="font-family:sans-serif; padding:20px;">
    <h1>Админка</h1>
    <h3>Статистика:</h3>
    <?php foreach($stats as $s): ?> <div><?=$s['name']?>: <b><?=$s['count']?></b></div> <?php endforeach; ?>
    <h3>Пользователи:</h3>
    <?php foreach($users as $u): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:5px;">
            ID: <?=$u['id']?> | Логин: <?=$u['login']?> | <?=$u['fio']?>
            <form method="POST" style="display:inline;"><input type="hidden" name="del_id" value="<?=$u['id']?>"><button type="submit">Удалить</button></form>
        </div>
    <?php endforeach; ?>
</body>
</html>