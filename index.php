<?php
// Обработка сохранения настроек
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $bg = $_POST['bg_color'] ?? '#ffffff';
    $text = $_POST['text_color'] ?? '#000000';

    // Устанавливаем cookies на 30 дней
    setcookie('user_bg', $bg, time() + 30 * 24 * 3600, '/');
    setcookie('user_text', $text, time() + 30 * 24 * 3600, '/');

    // Перенаправляем, чтобы избежать повторной отправки формы
    header('Location: index.php');
    exit;
}

// Обработка выхода (разлогин)
if (isset($_GET['logout'])) {
    // Удаляем cookies (устанавливаем дату в прошлое)
    setcookie('user_bg', '', time() - 3600, '/');
    setcookie('user_text', '', time() - 3600, '/');

    // Перенаправляем с параметром для вывода сообщения
    header('Location: index.php?logged_out=1');
    exit;
}

// Читаем текущие настройки из cookies (или значения по умолчанию)
$bg = $_COOKIE['user_bg'] ?? '#ffffff';
$text = $_COOKIE['user_text'] ?? '#000000';

// Проверяем, вышли ли мы только что
$showLoggedOutMessage = isset($_GET['logged_out']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная №3 — Cookies</title>
</head>
<body style="background: <?= htmlspecialchars($bg) ?>; color: <?= htmlspecialchars($text) ?>; padding: 20px; font-family: Arial, sans-serif;">

    <h2>🎨 Пользовательские настройки</h2>

    <?php if ($showLoggedOutMessage): ?>
        <p style="color: green; font-weight: bold;">✅ Вы успешно вышли. Настройки сброшены.</p>
        <a href="index.php">Вернуться на главную</a>
    <?php else: ?>
        <p>Измените фон и цвет текста. Закройте вкладку и откройте снова — настройки сохранятся!</p>

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
        <a href="?logout" style="color: red;">🚪 Выйти (сбросить настройки)</a>
    <?php endif; ?>

</body>
</html>
