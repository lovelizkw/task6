<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

$is_logged_in = !empty($_SESSION['login']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

$db_user = 'u82353';
$db_pass = '3228865';

try {
    $db = new PDO('mysql:host=localhost;dbname=u82353', $db_user, $db_pass, [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    print('Error connection: ' . $e->getMessage());
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = 'Спасибо, результаты сохранены.';
    }

    $values = array();
    if ($is_logged_in) {
        $stmt = $db->prepare("SELECT a.* FROM applications a JOIN user_auth u ON u.application_id = a.id WHERE u.id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $values['fio'] = $row['fio'];
        $values['phone'] = $row['phone'];
        $values['email'] = $row['email'];
        $values['birth_date'] = $row['birth_date'];
        $values['gender'] = $row['gender'];
        $values['biography'] = $row['biography'];

        $values['languages'] = [];
        $stmt = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
        $stmt->execute([$row['id']]);
        while($lang = $stmt->fetch()) { $values['languages'][] = $lang['language_id']; }
    } else {
        $values = ['fio'=>'', 'phone'=>'', 'email'=>'', 'birth_date'=>'', 'gender'=>'male', 'biography'=>'', 'languages'=>[]];
    }
    include('form.php');
    exit();
}

try {
    if (!$is_logged_in) {
        $login = 'user' . rand(1, 1000);
        $password = rand(1000, 9999);
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("INSERT INTO applications (fio, phone, email, birth_date, gender, biography) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['biography']]);
        $app_id = $db->lastInsertId();

        $stmt = $db->prepare("INSERT INTO user_auth (login, password_hash, application_id) VALUES (?, ?, ?)");
        $stmt->execute([$login, $hash, $app_id]);

        if (!empty($_POST['languages'])) {
            foreach ($_POST['languages'] as $lang_id) {
                $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
                $stmt->execute([$app_id, $lang_id]);
            }
        }
        setcookie('login', $login, time() + 365 * 24 * 3600);
        setcookie('password', $password, time() + 365 * 24 * 3600);
    } else {
        $stmt = $db->prepare("SELECT application_id FROM user_auth WHERE id = ?");
        $stmt->execute([$user_id]);
        $app_id = $stmt->fetchColumn();

        $stmt = $db->prepare("UPDATE applications SET fio = ?, phone = ?, email = ?, birth_date = ?, gender = ?, biography = ? WHERE id = ?");
        $stmt->execute([$_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['biography'], $app_id]);

        $db->prepare("DELETE FROM application_languages WHERE application_id = ?")->execute([$app_id]);
        if (!empty($_POST['languages'])) {
            foreach ($_POST['languages'] as $lang_id) {
                $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)")->execute([$app_id, $lang_id]);
            }
        }
    }
} catch (PDOException $e) {
    print('Error: ' . $e->getMessage()); exit();
}

setcookie('save', '1');
header('Location: index.php');
