<?php
session_start();
require 'config.php';
require 'languages.php'; // Подключаем переводы

if (!isset($_SESSION['username'])) {
    header("Location: index.php?lang=$lang");
    exit();
}

$userStmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
$userStmt->bindParam(':username', $_SESSION['username']);
$userStmt->execute();
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: index.php?lang=$lang");
    exit();
}

if (isset($_GET['mark_as_read'])) {
    $notification_id = $_GET['mark_as_read'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $notification_id);
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->execute();
    header("Location: notifications.php?lang=$lang");
    exit();
}

$notificationsStmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
$notificationsStmt->bindParam(':user_id', $user['id']);
$notificationsStmt->execute();
$notifications = $notificationsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['notifications']; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; // Подключаем хедер ?>

    <div class="container">
        <h1 class="fade-in"><?php echo $translations['notifications']; ?></h1>

        <!-- Кнопки для чатов -->
        <div class="chat-buttons fade-in">
            <a href="kripto_help.php?lang=<?php echo $lang; ?>"><button class="chat-button crypto-button"><?php echo $translations['chat_crypto']; ?></button></a>
            <a href="helper_help.php?lang=<?php echo $lang; ?>"><button class="chat-button support-button"><?php echo $translations['support']; ?></button></a>
        </div>

        <?php if (empty($notifications)): ?>
            <p class="no-data"><?php echo $translations['no_notifications']; ?></p>
        <?php else: ?>
            <div class="notification-body fade-in">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-message <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <small><?php echo htmlspecialchars($notification['created_at']); ?></small>
                        <?php if (!$notification['is_read']): ?>
                            <a href="notifications.php?mark_as_read=<?php echo $notification['id']; ?>&lang=<?php echo $lang; ?>" class="action-link"><?php echo $translations['mark_as_read']; ?></a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="index.php?lang=<?php echo $lang; ?>"><button class="nav-button"><?php echo $translations['to_main']; ?></button></a>
    </div>

    <script>
    // Сохранение языка в localStorage
    document.querySelectorAll('.lang-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const lang = this.getAttribute('href').split('lang=')[1];
            localStorage.setItem('preferredLang', lang);
        });
    });

    // Применение сохранённого языка при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        const savedLang = localStorage.getItem('preferredLang');
        if (savedLang && savedLang !== '<?php echo $lang; ?>') {
            window.location.href = window.location.pathname + '?lang=' + savedLang;
        }
    });
    </script>
</body>
</html>