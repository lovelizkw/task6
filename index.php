<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$host = 'localhost'; $dbname = 'u82353'; $username = 'u82353'; $password = '3228865';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Ошибка БД"); }

$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Go'];
$is_logged = !empty($_SESSION['login']);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = [];
    if (isset($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = 'Данные успешно сохранены!';
    }

    $values = [];
    if ($is_logged) {
        $stmt = $pdo->prepare("SELECT a.* FROM applications a JOIN users u ON a.id = u.application_id WHERE u.login = ?");
        $stmt->execute([$_SESSION['login']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $values = $row;
            $stmt = $pdo->prepare("SELECT l.name FROM application_languages al JOIN languages l ON al.language_id = l.id WHERE al.application_id = ?");
            $stmt->execute([$row['id']]);
            $values['languages'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }
    include('form.php');
} else {
    $fio = $_POST['fio'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '2000-01-01';
    $gender = $_POST['gender'] ?? 'male';
    $languages = $_POST['languages'] ?? [];
    $biography = $_POST['biography'] ?? '';
    $contract = isset($_POST['contract']) ? 1 : 0;

    if ($is_logged) {
        $stmt = $pdo->prepare("SELECT application_id FROM users WHERE login = ?");
        $stmt->execute([$_SESSION['login']]);
        $app_id = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("UPDATE applications SET fio=?, phone=?, email=?, birth_date=?, gender=?, biography=?, contract=? WHERE id=?");
        $stmt->execute([$fio, $phone, $email, $birth_date, $gender, $biography, $contract, $app_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO applications (fio, phone, email, birth_date, gender, biography, contract) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fio, $phone, $email, $birth_date, $gender, $biography, $contract]);
        $app_id = $pdo->lastInsertId();
        
        $login = 'user' . rand(1000, 9999);
        $pass = substr(md5(rand()), 0, 8);
        $pdo->prepare("INSERT INTO users (login, password_hash, password_raw, application_id) VALUES (?, ?, ?, ?)")
            ->execute([$login, md5($pass), $pass, $app_id]);
        
        $_SESSION['login'] = $login;
        setcookie('login', $login, time() + 3600);
        setcookie('pass', $pass, time() + 3600);
    }

    $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")->execute([$app_id]);
    foreach ($languages as $lang) {
        $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, (SELECT id FROM languages WHERE name = ?))")->execute([$app_id, $lang]);
    }

    setcookie('save', '1');
    header('Location: index.php');
}