<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Анкета</title>
    <style>
        body { font-family: sans-serif; background: #fff5f7; display: flex; justify-content: center; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); width: 400px; }
        h2 { color: #d53f8c; text-align: center; }
        input, select, textarea { width: 100%; margin-bottom: 10px; padding: 10px; border: 1px solid #fbb6ce; border-radius: 5px; box-sizing: border-box; }
        .btn { background: #d53f8c; color: white; border: none; padding: 12px; width: 100%; cursor: pointer; border-radius: 5px; font-weight: bold; }
        .info { background: #edf2f7; padding: 10px; margin-bottom: 15px; border-radius: 5px; font-size: 13px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Анкета</h2>
    <?php if (!$is_logged && !empty($_COOKIE['login'])): ?>
        <div class="info">Логин: <b><?=htmlspecialchars($_COOKIE['login'])?></b><br>Пароль: <b><?=htmlspecialchars($_COOKIE['pass'])?></b></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="fio" placeholder="ФИО" value="<?=htmlspecialchars($values['fio'] ?? '')?>" required>
        <input type="tel" name="phone" placeholder="Телефон" value="<?=htmlspecialchars($values['phone'] ?? '')?>">
        <input type="email" name="email" placeholder="E-mail" value="<?=htmlspecialchars($values['email'] ?? '')?>">
        <input type="date" name="birth_date" value="<?=htmlspecialchars($values['birth_date'] ?? '')?>">
        <select name="gender">
            <option value="male" <?=($values['gender']??'')=='male'?'selected':''?>>Мужской</option>
            <option value="female" <?=($values['gender']??'')=='female'?'selected':''?>>Женский</option>
        </select>
        <select name="languages[]" multiple size="4">
            <?php foreach($allowed_languages as $l): ?>
                <option value="<?=$l?>" <?=(isset($values['languages']) && in_array($l, $values['languages']))?'selected':''?>><?=$l?></option>
            <?php endforeach; ?>
        </select>
        <textarea name="biography"><?=htmlspecialchars($values['biography'] ?? '')?></textarea>
        <button type="submit" class="btn"><?=$is_logged?'Обновить':'Отправить'?></button>
    </form>
    <div style="text-align:center; margin-top:15px;">
        <?php if($is_logged): ?> <a href="logout.php">Выйти</a> <?php else: ?> <a href="login.php">Вход</a> <?php endif; ?>
    </div>
</div>
</body>
</html>