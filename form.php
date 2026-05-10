<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        body { background-color: #fff1f6; font-family: sans-serif; display: flex; justify-content: center; padding: 20px; }
        .form-container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 400px; }
        h2 { color: #d81b60; text-align: center; }
        input, select, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ff80ab; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; background: #d81b60; color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; }
        .messages { color: green; font-size: 0.9em; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Анкета</h2>
        <?php if (!empty($messages)) { foreach ($messages as $m) { echo "<div class='messages'>$m</div>"; } } ?>
        
        <form action="index.php" method="POST">
            <input type="text" name="fio" placeholder="ФИО" value="<?php echo $values['fio']; ?>">
            <input type="tel" name="phone" placeholder="Телефон" value="<?php echo $values['phone']; ?>">
            <input type="email" name="email" placeholder="E-mail" value="<?php echo $values['email']; ?>">
            <input type="date" name="birth_date" value="<?php echo $values['birth_date']; ?>">
            
            <select name="gender">
                <option value="male" <?php if($values['gender'] == 'male') echo 'selected'; ?>>Мужской</option>
                <option value="female" <?php if($values['gender'] == 'female') echo 'selected'; ?>>Женский</option>
            </select>

            <select name="languages[]" multiple size="5">
                <option value="1">Pascal</option>
                <option value="2">C</option>
                <option value="3">C++</option>
                <option value="4">JavaScript</option>
                <option value="5">PHP</option>
                <option value="6">Python</option>
                <option value="7">Java</option>
                <option value="8">Go</option>
            </select>

            <textarea name="biography" placeholder="О себе"><?php echo $values['biography']; ?></textarea>
            <button type="submit">Отправить</button>
        </form>
        <div style="text-align: center; margin-top: 15px;"><a href="login.php" style="color: #d81b60;">Вход для пользователей</a></div>
    </div>
</body>
</html>
