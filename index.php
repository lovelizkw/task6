<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once('db.php');

$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Go'];
$is_logged = !empty($_SESSION['login']);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $values = [];
    $messages = [];
    if (isset($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = 'Данные сохранены!';
    }
    if ($is_logged) {
        $stmt = $pdo->prepare("SELECT u.* FROM users u JOIN user_auth a ON u.id = a.user_id WHERE a.login = ?");
        $stmt->execute([$_SESSION['login']]);
        $values = $stmt->fetch();
        if ($values) {
            $stmt = $pdo->prepare("SELECT l.name FROM user_languages ul JOIN languages l ON ul.language_id = l.id WHERE ul.user_id = ?");
            $stmt->execute([$values['id']]);
            $values['languages'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }
    include('form.php');
} else {
    $app_id = null;
    if ($is_logged) {
        $stmt = $pdo->prepare("SELECT user_id FROM user_auth WHERE login = ?");
        $stmt->execute([$_SESSION['login']]);
        $app_id = $stmt->fetchColumn();
        $stmt = $pdo->prepare("UPDATE users SET fio=?, phone=?, email=?, birth_date=?, gender=?, biography=? WHERE id=?");
        $stmt->execute([$_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['biography'], $app_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (fio, phone, email, birth_date, gender, biography) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['biography']]);
        $app_id = $pdo->lastInsertId();
        $login = 'user' . rand(1000, 9999);
        $pass = substr(md5(rand()), 0, 8);
        $pdo->prepare("INSERT INTO user_auth (login, password_hash, user_id) VALUES (?, ?, ?)")
            ->execute([$login, password_hash($pass, PASSWORD_DEFAULT), $app_id]);
        $_SESSION['login'] = $login;
        setcookie('login', $login, time() + 3600);
        setcookie('pass', $pass, time() + 3600);
    }
    $pdo->prepare("DELETE FROM user_languages WHERE user_id = ?")->execute([$app_id]);
    foreach ($_POST['languages'] as $lang_name) {
        $pdo->prepare("INSERT INTO user_languages (user_id, language_id) VALUES (?, (SELECT id FROM languages WHERE name = ?))")->execute([$app_id, $lang_name]);
    }
    setcookie('save', '1');
    header('Location: index.php');
}