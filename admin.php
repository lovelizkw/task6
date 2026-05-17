<?php
$host = 'localhost';
$dbname = 'u82353';
$username = 'u82353';
$password = '3228865'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// === HTTP-АВТОРИЗАЦИЯ ===
$is_admin = false;
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
    $stmt = $pdo->prepare("SELECT password_hash FROM admin_auth WHERE login = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $hash = $stmt->fetchColumn();
    if ($hash && password_verify($_SERVER['PHP_AUTH_PW'], $hash)) {
        $is_admin = true;
    }
}

if (!$is_admin) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    die('Авторизуйтесь для доступа к панели администратора');
}

// === ОБРАБОТКА ДЕЙСТВИЙ ===
$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Go'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_admin'])) {
        $id = (int)$_POST['user_id'];
        $fio = trim($_POST['fio'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $birth = $_POST['birth_date'] ?? null;
        $gender = $_POST['gender'] ?? 'male';
        $bio = trim($_POST['biography'] ?? '');

        $pdo->prepare("UPDATE applications SET fio=?, phone=?, email=?, birth_date=?, gender=?, biography=? WHERE id=?")
             ->execute([$fio, $phone, $email, $birth, $gender, $bio, $id]);

        $langs = $_POST['languages'] ?? [];
        $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")->execute([$id]);
        foreach ($langs as $lang) {
            $pdo->prepare("INSERT IGNORE INTO application_languages (application_id, language_id) 
                           SELECT ?, id FROM languages WHERE name = ?")
                 ->execute([$id, $lang]);
        }
        header("Location: admin.php?success=1");
        exit;
    }

    if (isset($_POST['del_id'])) {
        $id = (int)$_POST['del_id'];
        $pdo->prepare("DELETE FROM applications WHERE id = ?")->execute([$id]);
        header("Location: admin.php?deleted=1");
        exit;
    }
}

// === ЗАГРУЗКА ДАННЫХ ===
$apps = $pdo->query("SELECT a.*, u.login, u.password_raw 
                     FROM applications a 
                     LEFT JOIN users u ON a.id = u.application_id 
                     ORDER BY a.id DESC")->fetchAll(PDO::FETCH_ASSOC);

$stats = $pdo->query("SELECT l.name, COUNT(al.application_id) as count 
                      FROM languages l 
                      LEFT JOIN application_languages al ON l.id = al.language_id 
                      GROUP BY l.id, l.name ORDER BY count DESC")
              ->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <style>
        body { font-family: sans-serif; background: #fdf2f8; margin: 0; padding: 20px; }
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .user-card { border: 2px solid #fce4ec; border-radius: 10px; padding: 18px; margin-bottom: 20px; }
        .user-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #fce4ec; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-edit { background: #d81b60; color: white; }
        .btn-del { background: #e53e3e; color: white; }
        .btn-save { background: #38a169; color: white; width: 100%; padding: 12px; margin-top: 10px; }
        .edit-form { display: none; margin-top: 15px; background: #fdf2f8; padding: 20px; border-radius: 8px; }
        input, select, textarea { width: 100%; padding: 10px; margin: 5px 0 12px; border: 1px solid #f8bbd0; border-radius: 6px; box-sizing: border-box; }
    </style>
    <script>
        function toggleEdit(id) {
            var form = document.getElementById('form-' + id);
            form.style.display = (form.style.display === 'block') ? 'none' : 'block';
        }
    </script>
</head>
<body>
<div class="container">
    <h2 style="color: #d81b60; text-align: center;">Панель администратора</h2>

    <?php if (isset($_GET['success']) || isset($_GET['deleted'])): ?>
        <p style="background:#d4edda; color:#155724; padding:12px; border-radius:6px; text-align:center;">
            <?= isset($_GET['deleted']) ? '✅ Анкета успешно удалена!' : '✅ Изменения успешно сохранены!' ?>
        </p>
    <?php endif; ?>

    <div style="background:#fce4ec; padding:15px; border-radius:8px; margin-bottom:25px;">
        <strong>Статистика по языкам:</strong><br>
        <?php foreach($stats as $s): ?>
            <strong><?=htmlspecialchars($s['name'])?></strong>: <?=$s['count']?> чел. &nbsp;&nbsp;&nbsp;
        <?php endforeach; ?>
    </div>

    <?php foreach($apps as $a): 
        $stmt = $pdo->prepare("SELECT l.name FROM application_languages al 
                               JOIN languages l ON al.language_id = l.id 
                               WHERE al.application_id = ?");
        $stmt->execute([$a['id']]);
        $user_langs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    ?>
    <div class="user-card">
        <div class="user-header">
            <div>ID: <b><?=$a['id']?></b> | Логин: <b><?=htmlspecialchars($a['login'] ?? '-')?></b> | 
                 Пароль: <b><?=htmlspecialchars($a['password_raw'] ?? '-')?></b></div>
            <div>
                <button class="btn btn-edit" onclick="toggleEdit(<?=$a['id']?>)">Изменить</button>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить анкету №<?=$a['id']?>?')">
                    <input type="hidden" name="del_id" value="<?=$a['id']?>">
                    <button type="submit" class="btn btn-del">Удалить</button>
                </form>
            </div>
        </div>

        <p><strong>ФИО:</strong> <?=htmlspecialchars($a['fio'] ?? '—')?></p>
        <p><strong>Языки:</strong> <?=htmlspecialchars(implode(', ', $user_langs) ?: '—')?></p>

        <div class="edit-form" id="form-<?=$a['id']?>">
            <form method="POST">
                <input type="hidden" name="user_id" value="<?=$a['id']?>">
                <input type="text" name="fio" value="<?=htmlspecialchars($a['fio'] ?? '')?>" required>
                <input type="tel" name="phone" value="<?=htmlspecialchars($a['phone'] ?? '')?>">
                <input type="email" name="email" value="<?=htmlspecialchars($a['email'] ?? '')?>">
                <input type="date" name="birth_date" value="<?=$a['birth_date'] ?? ''?>">
                
                <select name="gender">
                    <option value="male" <?=($a['gender']??'') == 'male' ? 'selected' : ''?>>Мужской</option>
                    <option value="female" <?=($a['gender']??'') == 'female' ? 'selected' : ''?>>Женский</option>
                </select>

                <select name="languages[]" multiple size="6">
                    <?php foreach($allowed_languages as $l): ?>
                        <option value="<?=$l?>" <?=in_array($l, $user_langs) ? 'selected' : ''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>

                <textarea name="biography" rows="4"><?=htmlspecialchars($a['biography'] ?? '')?></textarea>

                <button type="submit" name="save_admin" class="btn btn-save">Сохранить изменения</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</body>
</html>
