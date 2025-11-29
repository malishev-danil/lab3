<?php
//Подключение к БД
try {
    $pdo = new PDO("mysql:host=MySQL-8.4;dbname=lab3;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . htmlspecialchars($e->getMessage()));
}

//Генерация или получение ID пользователя
$user_id = $_COOKIE['user_id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    //Создаём новую запись
    $stmt = $pdo->prepare("INSERT INTO user_settings (bg_color, text_color) VALUES ('#ffffff', '#000000')");
    $stmt->execute();
    $user_id = $pdo->lastInsertId();
    setcookie('user_id', $user_id, time() + 30*24*3600, '/');
}

//Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $bg = $_POST['bg_color'] ?? '#ffffff';
    $text = $_POST['text_color'] ?? '#000000';

    //Обновляем в БД
    $stmt = $pdo->prepare("UPDATE user_settings SET bg_color = ?, text_color = ? WHERE id = ?");
    $stmt->execute([$bg, $text, $user_id]);

    //Обновляем cookies
    setcookie('user_bg', $bg, time() + 30*24*3600, '/');
    setcookie('user_text', $text, time() + 30*24*3600, '/');

    header('Location: index.php');
    exit;
}

//Выход
if (isset($_GET['logout'])) {
    setcookie('user_id', '', time() - 3600, '/');
    setcookie('user_bg', '', time() - 3600, '/');
    setcookie('user_text', '', time() - 3600, '/');
    header('Location: index.php?logged_out=1');
    exit;
}

//Загрузка настроек из БД
$stmt = $pdo->prepare("SELECT bg_color, text_color FROM user_settings WHERE id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch();

$bg = $row ? $row['bg_color'] : '#ffffff';
$text = $row ? $row['text_color'] : '#000000';

//Синхронизируем cookies при первом заходе
if (!isset($_COOKIE['user_bg'])) {
    setcookie('user_bg', $bg, time() + 30*24*3600, '/');
    setcookie('user_text', $text, time() + 30*24*3600, '/');
}

$showLoggedOutMessage = isset($_GET['logged_out']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная №3 — Cookies</title>
</head>
<body style="background: <?= htmlspecialchars($bg) ?>; color: <?= htmlspecialchars($text) ?>; padding: 20px; font-family: Arial, sans-serif;">

    <h2>Пользовательские настройки</h2>

    <?php if ($showLoggedOutMessage): ?>
        <p style="color: green; font-weight: bold;">Вы успешно вышли. Настройки сброшены.</p>
        <a href="index.php">Вернуться на главную</a>
    <?php else: ?>

        <!-- Форма сохранения настроек -->
        <form method="POST">
            <label>
                Фон:
                <input type="color" name="bg_color" value="<?= htmlspecialchars($bg) ?>">
            </label><br><br>

            <label>
                Цвет текста:
                <input type="color" name="text_color" value="<?= htmlspecialchars($text) ?>">
            </label><br><br>

            <button type="submit" name="save" value="1">Сохранить</button>
        </form>

        <br>
        <a href="?logout" style="color: red;">Выйти</a>
    <?php endif; ?>

</body>
</html>
