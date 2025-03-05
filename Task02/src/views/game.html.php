<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Игра "Простое ли число"</title>
</head>
<body>
    <h1>Игра "Простое ли число"</h1>
    <p>Определите, является ли число <b><?= $_SESSION['number'] ?></b> простым:</p>

    <form method="post">
        <label for="user_answer">Ваш ответ (Простое/Не простое):</label>
        <input type="text" name="user_answer" required>
        <button type="submit">Проверить</button>
    </form>

    <?php if (isset($message)): ?>
        <p><?= $message ?></p>
    <?php endif; ?>

    <br>

    <form method="post">
      <input type="submit" name="new_game" value="Новая игра" />
    </form>

    <br>
    
    <a href="index.php?page=home">Закончить игру</a>
</body>
</html>
