<?php
//1. Подключение к БД
$host = 'MySQL-8.4';
$dbname = 'php_malishev';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . htmlspecialchars($e->getMessage()));
}

//2. Получение или создание пользователя
$user_id = null;

if (isset($_COOKIE['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM user_settings WHERE id = ?");
    $stmt->execute([$_COOKIE['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $user_id = $user['id'];
    }
}

if (!$user_id) {
    // Создаём нового пользователя (новую запись в БД)
    $stmt = $pdo->prepare("INSERT INTO user_settings (bg_color, text_color) VALUES ('#ffffff', '#000000')");
    $stmt->execute();
    $user_id = $pdo->lastInsertId();

    // Сохраняем ID в cookie на 30 дней
    setcookie('user_id', $user_id, time() + 30*24*3600, '/');
}

//3. Обработка сохранения настроек
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $bg = $_POST['bg_color'] ?? '#ffffff';
    $text = $_POST['text_color'] ?? '#000000';

    // Обновляем настройки в БД
    $stmt = $pdo->prepare("UPDATE user_settings SET bg_color = ?, text_color = ? WHERE id = ?");
    $stmt->execute([$bg, $text, $user_id]);

    // Также обновляем cookies
    setcookie('user_bg', $bg, time() + 30*24*3600, '/');
    setcookie('user_text', $text, time() + 30*24*3600, '/');

    header('Location: index.php');
    exit;
}

//4. Обработка выхода
if (isset($_GET['logout'])) {
    // Удаляем все cookies
    setcookie('user_id', '', time() - 3600, '/');
    setcookie('user_bg', '', time() - 3600, '/');
    setcookie('user_text', '', time() - 3600, '/');

    header('Location: index.php?logged_out=1');
    exit;
}

//5. Загрузка текущих настроек из БД
$stmt = $pdo->prepare("SELECT bg_color, text_color FROM user_settings WHERE id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->fetch();

$bg = $settings['bg_color'] ?? '#ffffff';
$text = $settings['text_color'] ?? '#000000';

// Также устанавливаем cookies при первом заходе
if (!isset($_COOKIE['user_bg'])) {
    setcookie('user_bg', $bg, time() + 30*24*3600, '/');
    setcookie('user_text', $text, time() + 30*24*3600, '/');
}

$showLoggedOut = isset($_GET['logged_out']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная №3 — Настройки в БД</title>
</head>
<body style="background: <?= htmlspecialchars($bg) ?>; color: <?= htmlspecialchars($text) ?>; padding: 20px; font-family: Arial, sans-serif;">

    <h2> Ваши настройки</h2>

    <?php if ($showLoggedOut): ?>
        <p style="color: green; font-weight: bold;">Вы вышли. Все данные в БД сохранены, но cookies удалены.</p>
        <p>При следующем заходе будет создан новый пользователь.</p>
        <a href="index.php">Создать нового пользователя</a>
    <?php else: ?>
        <p>Ваш ID: <strong><?= htmlspecialchars($user_id) ?></strong> (хранится в cookie).</p>
        <p>Измените настройки — они сохранятся в базе данных!</p>

        <form method="POST">
            <label>
                Фон:
                <input type="color" name="bg_color" value="<?= htmlspecialchars($bg) ?>">
            </label><br><br>

            <label>
                Цвет текста:
                <input type="color" name="text_color" value="<?= htmlspecialchars($text) ?>">
            </label><br><br>

            <button type="submit" name="save" value="1">Сохранить в БД</button>
        </form>

        <br>
        <a href="?logout" style="color: red;">Выйти</a>
    <?php endif; ?>

</body>
</html>
