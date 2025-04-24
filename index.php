<?php
session_start();
require 'config.php';
require 'languages.php'; // Подключаем переводы

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'user';
    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit();
    } else {
        echo $translations['error'];
    }
}

$unreadNotifications = 0;
if (isset($_SESSION['username'])) {
    $userStmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $userStmt->bindParam(':username', $_SESSION['username']);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $notificationsStmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
        $notificationsStmt->bindParam(':user_id', $user['id']);
        $notificationsStmt->execute();
        $unreadNotifications = $notificationsStmt->fetchColumn();
    }
}

$query = $conn->query("SELECT * FROM cryptocurrencies ORDER BY date");
$data = $query->fetchAll(PDO::FETCH_ASSOC);

$cryptoData = [];
foreach ($data as $row) {
    $cryptoData[$row['name']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['crypto_analytics']; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Подключаем хедер -->
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="auth-buttons">
            <?php if (isset($_SESSION['username'])): ?>
                <span><?php echo $translations['hello']; ?>, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php"><button><?php echo $translations['admin_panel']; ?></button></a>
                <?php elseif ($_SESSION['role'] === 'moderator'): ?>
                    <a href="moderator.php"><button><?php echo $translations['moderator']; ?></button></a>
                <?php elseif ($_SESSION['role'] === 'helper'): ?>
                    <a href="helper.php"><button><?php echo $translations['support']; ?></button></a>
                <?php endif; ?>
                <a href="notifications.php"><button class="notification-bell">🔔 <?php if ($unreadNotifications > 0): ?><span class="notification-count"><?php echo $unreadNotifications; ?></span><?php endif; ?></button></a>
                <a href="index.php?logout=true"><button><?php echo $translations['logout']; ?></button></a>
            <?php else: ?>
                <a href="login.php"><button><?php echo $translations['login']; ?></button></a>
                <a href="register.php"><button><?php echo $translations['register']; ?></button></a>
            <?php endif; ?>
        </div>

        <h1 class="fade-in"><?php echo $translations['crypto_analytics']; ?></h1>

        <?php if (isset($_SESSION['order_message'])): ?>
            <div class="order-message"><?php echo $_SESSION['order_message']; ?></div>
            <?php unset($_SESSION['order_message']); ?>
        <?php endif; ?>

        <?php if (empty($data)): ?>
            <p class="no-data"><?php echo $translations['no_data']; ?></p>
        <?php else: ?>
            <?php foreach ($cryptoData as $name => $values): ?>
                <div class="chart-container fade-in">
                    <h2><?php echo htmlspecialchars($name); ?></h2>
                    <canvas id="chart-<?php echo strtolower(str_replace(' ', '-', $name)); ?>"></canvas>
                    <?php if (isset($_SESSION['username']) && $_SESSION['role'] !== 'admin'): ?>
                        <a href="booking.php?crypto=<?php echo urlencode($name); ?>"><button class="buy-button"><?php echo $translations['buy']; ?></button></a>
                    <?php endif; ?>
                </div>
                <script>
                    const ctx<?php echo str_replace(' ', '', $name); ?> = document.getElementById('chart-<?php echo strtolower(str_replace(' ', '-', $name)); ?>').getContext('2d');
                    new Chart(ctx<?php echo str_replace(' ', '', $name); ?>, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode(array_column($values, 'date')); ?>,
                            datasets: [{
                                label: '<?php echo $translations['price']; ?>',
                                data: <?php echo json_encode(array_column($values, 'price')); ?>,
                                borderColor: '#4dabf7',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            scales: {
                                y: { beginAtZero: false, grid: { color: '#555' }, ticks: { color: '#e0e0e0' } },
                                x: { grid: { color: '#555' }, ticks: { color: '#e0e0e0' } }
                            },
                            plugins: { legend: { labels: { color: '#e0e0e0' } } }
                        }
                    });
                </script>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="chat-toggle-button" id="chat-toggle-button"><span>💬</span></div>
        <div class="chat-container" id="chat-container">
            <div class="chat-header">
                <h3><?php echo $translations['chatbot']; ?></h3>
                <button id="chat-close">✕</button>
            </div>
            <div class="chat-body" id="chat-body">
                <div class="chat-messages" id="chat-messages"></div>
            </div>
            <div class="chat-input">
                <input type="text" id="chat-input" placeholder="<?php echo $translations['type_message']; ?>">
                <button id="chat-send"><?php echo $translations['send']; ?></button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
    const chatToggleButton = document.getElementById('chat-toggle-button');
    const chatContainer = document.getElementById('chat-container');
    const chatCloseButton = document.getElementById('chat-close');
    const chatBody = document.getElementById('chat-body');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');

    if (!chatToggleButton || !chatContainer || !chatCloseButton) {
        console.error('Ошибка: Не найдены элементы чат-бота');
        return;
    }

    chatToggleButton.addEventListener('click', function () {
        const isVisible = chatContainer.style.display === 'block';
        chatContainer.style.display = isVisible ? 'none' : 'block';
        chatContainer.style.visibility = isVisible ? 'hidden' : 'visible';
        chatContainer.style.opacity = isVisible ? '0' : '1';
    });

    chatCloseButton.addEventListener('click', function () {
        chatContainer.style.display = 'none';
        chatContainer.style.visibility = 'hidden';
        chatContainer.style.opacity = '0';
    });

    chatSend.addEventListener('click', function () { sendMessage(); });
    chatInput.addEventListener('keydown', function (event) { if (event.key === 'Enter') sendMessage(); });

    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        addMessage('user', message);
        chatInput.value = '';

        // Локальные команды отправляем на сервер
        if (message.includes('прайс') || message.includes('курс') || message.includes('график') || 
            message.includes('история') || message.includes('список') || message.includes('все крипты')) {
            fetch('chatbot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            })
            .then(response => {
                if (!response.ok) throw new Error('Ошибка сервера');
                return response.json();
            })
            .then(data => {
                addMessage('bot', data.message || data.error);
            })
            .catch(error => {
                console.error('Ошибка:', error);
                addMessage('bot', '<?php echo addslashes($translations['error_chat']); ?>');
            });
        } else {
            // Запрос к OpenAI с клиента
            fetch('https://api.openai.com/v1/chat/completions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer <?php echo $api_key; ?>'
                },
                body: JSON.stringify({
                    model: 'gpt-3.5-turbo',
                    messages: [
                        { role: 'system', content: 'Вы дружелюбный помощник.' },
                        { role: 'user', content: message }
                    ]
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Ошибка API: ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    addMessage('bot', data.error.message);
                } else {
                    addMessage('bot', data.choices[0].message.content);
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                addMessage('bot', '<?php echo addslashes($translations['error_chat']); ?>');
            });
        }
    }

    function addMessage(sender, message) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('chat-message', sender);
        messageElement.textContent = message;
        chatMessages.appendChild(messageElement);
        chatBody.scrollTop = chatBody.scrollHeight;
    }
});
    </script>
</body>
</html>