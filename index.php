<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
$is_logged_in = !empty($_SESSION['login']);

$user = 'u82353';
$pass = '3228865';
$db = new PDO('mysql:host=localhost;dbname=u82353', $user, $pass, [
    PDO::ATTR_PERSISTENT => true,
    PDO::ERRMODE => PDO::ERRMODE_EXCEPTION
]);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = 'Спасибо, результаты сохранены.';
        if (!empty($_COOKIE['password'])) {
            $messages[] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong> для изменения данных.',
                strip_tags($_COOKIE['login']),
                strip_tags($_COOKIE['password']));
        }
    }

    $values = array();
    $values['fio'] = empty($_COOKIE['fio_value']) ? '' : $_COOKIE['fio_value'];
    $values['phone'] = empty($_COOKIE['phone_value']) ? '' : $_COOKIE['phone_value'];
    $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
    $values['birth_date'] = empty($_COOKIE['birth_date_value']) ? '' : $_COOKIE['birth_date_value'];
    $values['gender'] = empty($_COOKIE['gender_value']) ? 'male' : $_COOKIE['gender_value'];
    $values['biography'] = empty($_COOKIE['biography_value']) ? '' : $_COOKIE['biography_value'];
    $values['languages'] = [];
        $stmt = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
        $stmt->execute([$row['id']]);
        while($lang = $stmt->fetch()) { $values['languages'][] = $lang['language_id']; }
    } else {
        $values['fio'] = empty($_COOKIE['fio_value']) ? '' : $_COOKIE['fio_value'];
        $values['phone'] = empty($_COOKIE['phone_value']) ? '' : $_COOKIE['phone_value'];
        $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
        $values['birth_date'] = empty($_COOKIE['birth_date_value']) ? '' : $_COOKIE['birth_date_value'];
        $values['gender'] = empty($_COOKIE['gender_value']) ? 'male' : $_COOKIE['gender_value'];
        $values['biography'] = empty($_COOKIE['biography_value']) ? '' : $_COOKIE['biography_value'];
        $values['languages'] = [];
    }

    include('form.php');
    exit();
}

$errors = FALSE;
if (empty($_POST['fio'])) { $errors = TRUE; setcookie('fio_error', '1', time() + 24 * 3600); }
if (empty($_POST['birth_date'])) { $errors = TRUE; setcookie('birth_date_error', '1', time() + 24 * 3600); }

if ($errors) {
    header('Location: index.php');
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

        $stmt = $db->prepare("INSERT INTO users (login, password_hash, application_id) VALUES (?, ?, ?)");
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
        $stmt = $db->prepare("SELECT application_id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $app_id = $stmt->fetchColumn();

        $stmt = $db->prepare("UPDATE applications SET fio = ?, phone = ?, email = ?, birth_date = ?, gender = ?, biography = ? WHERE id = ?");
        $stmt->execute([$_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['biography'], $app_id]);

        $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = ?");
        $stmt->execute([$app_id]);

        if (!empty($_POST['languages'])) {
            foreach ($_POST['languages'] as $lang_id) {
                $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
                $stmt->execute([$app_id, $lang_id]);
            }
        }
    }
} catch (PDOException $e) {
    print('Error : ' . $e->getMessage());
    exit();
}

setcookie('save', '1');
header('Location: index.php');
