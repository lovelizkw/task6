<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Анкета</title>
  <style>
    body { font-family: sans-serif; background: #fff5f7; display: flex; justify-content: center; padding: 20px; }
    .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(216, 27, 96, 0.1); width: 440px; border: 1px solid #fce4ec; }
    h2 { color: #d81b60; text-align: center; }
    input, select, textarea { width: 100%; margin-bottom: 15px; padding: 10px; border: 1px solid #f8bbd0; border-radius: 6px; box-sizing: border-box; }
    input:focus, select:focus, textarea:focus { outline: none; border-color: #d81b60; }
    .btn-main { background: #d81b60; color: white; border: none; padding: 12px; width: 100%; cursor: pointer; border-radius: 6px; font-weight: bold; font-size: 16px; transition: 0.3s; }
    .btn-main:hover { background: #c2185b; }
    .msg-ok { background: #fce4ec; color: #880e4f; padding: 10px; margin-bottom: 15px; border: 1px solid #f8bbd0; border-radius: 6px; text-align: center; }
    .nav { margin-top: 25px; border-top: 1px solid #fce4ec; padding-top: 15px; display: flex; flex-direction: column; gap: 10px; text-align: center; }
    .link { color: #d81b60; text-decoration: none; font-size: 14px; font-weight: bold; }
    .link:hover { text-decoration: underline; }
  </style>
</head>
<body>
<div class="card">
  <h2>Анкета</h2>

  <?php if (!empty($messages)) foreach($messages as $m) echo "<div class='msg-ok'>$m</div>"; ?>

  <?php if (!$is_logged && !empty($_COOKIE['login'])): ?>
    <div class="msg-ok">
      Ваш логин: <b><?= htmlspecialchars($_COOKIE['login']) ?></b><br>Пароль: <b><?= htmlspecialchars($_COOKIE['pass']) ?></b>
    </div>
  <?php endif; ?>

  <form method="POST">
    <input type="text" name="fio" placeholder="ФИО" value="<?= htmlspecialchars($values['fio'] ?? '') ?>" required>
    <input type="tel" name="phone" placeholder="Телефон" value="<?= htmlspecialchars($values['phone'] ?? '') ?>">
    <input type="email" name="email" placeholder="E-mail" value="<?= htmlspecialchars($values['email'] ?? '') ?>">
    <input type="date" name="birth_date" value="<?= htmlspecialchars($values['birth_date'] ?? '') ?>" required>
    
    <select name="gender">
      <option value="male" <?= ($values['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Мужской</option>
      <option value="female" <?= ($values['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Женский</option>
    </select>

    <select name="languages[]" multiple size="4">
      <?php foreach ($allowed_languages as $l): ?>
        <option value="<?= $l ?>" <?= (isset($values['languages']) && in_array($l, $values['languages'])) ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>

    <textarea name="biography" placeholder="О себе..."><?= htmlspecialchars($values['biography'] ?? '') ?></textarea>
    
    <label style="font-size: 13px; color: #880e4f;"><input type="checkbox" name="contract" style="width:auto" required checked> Я принимаю условия соглашения</label>

    <button type="submit" class="btn-main"><?= $is_logged ? 'Сохранить изменения' : 'Отправить' ?></button>
  </form>

  <div class="nav">
    <?php if ($is_logged): ?>
      <span style="color:#880e4f;">Вы вошли как: <b><?= $_SESSION['login'] ?></b></span>
      <a href="logout.php" class="link">Выйти</a>
    <?php else: ?>
      <a href="login.php" class="link">Вход</a>
    <?php endif; ?>
    <a href="admin.php" class="link" style="color:#a0aec0; margin-top: 10px; font-size: 12px;">Админ-панель</a>
  </div>
</div>
</body>
</html>