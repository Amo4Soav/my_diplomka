<?php
// Убедимся, что сессия запущена и переводы подключены
if (!isset($_SESSION)) {
    session_start();
}
require_once 'languages.php';
?>

<header class="site-header">
    <div class="header-container">
        <a href="index.php" class="header-logo"><?php echo $translations['crypto_analytics']; ?></a>
        <div class="header-tools">
            <!-- Переключатель языка -->
            <div class="language-switcher">
                <a href="?lang=ru" class="lang-link">Русский</a> |
                <a href="?lang=en" class="lang-link">English</a> |
                <a href="?lang=kz" class="lang-link">Қазақша</a>
            </div>
        </div>
    </div>
</header>