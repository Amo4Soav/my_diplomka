<?php
session_start();
require 'config.php';
require 'languages.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'moderator') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['user_id'])) {
    header("Location: moderator.php");
    exit();
}

$user_id = $_GET['user_id'];

// Получаем информацию о клиенте
$clientStmt = $conn->prepare("SELECT username FROM users WHERE id = :user_id");
$clientStmt->bindParam(':user_id', $user_id);
$clientStmt->execute();
$client = $clientStmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    header("Location: moderator.php");
    exit();
}

// Получаем текущий session_id для активной сессии
$sessionStmt = $conn->prepare("SELECT session_id FROM chat_messages WHERE user_id = :user_id AND chat_type = 'crypto' AND session_active = 1 ORDER BY created_at DESC LIMIT 1");
$sessionStmt->bindParam(':user_id', $user_id);
$sessionStmt->execute();
$session = $sessionStmt->fetch(PDO::FETCH_ASSOC);
$current_session_id = $session ? $session['session_id'] : null;

// Загружаем сообщения только для текущей активной сессии
$session_id_to_use = $current_session_id ?? 1; // По умолчанию первая сессия
$messagesStmt = $conn->prepare("SELECT cm.id, cm.message, cm.sender, cm.created_at, u.username 
                               FROM chat_messages cm 
                               JOIN users u ON cm.user_id = u.id 
                               WHERE cm.chat_type = 'crypto' AND cm.user_id = :user_id AND cm.session_id = :session_id 
                               ORDER BY cm.created_at ASC");
$messagesStmt->bindParam(':user_id', $user_id);
$messagesStmt->bindParam(':session_id', $session_id_to_use);
$messagesStmt->execute();
$messages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);

$isSessionActive = $current_session_id !== null;

if (isset($_POST['send_message'])) {
    $message = $_POST['message'];
    
    // Проверяем последнюю сессию
    $lastSessionStmt = $conn->prepare("SELECT session_id, session_active FROM chat_messages WHERE user_id = :user_id AND chat_type = 'crypto' ORDER BY created_at DESC LIMIT 1");
    $lastSessionStmt->bindParam(':user_id', $user_id);
    $lastSessionStmt->execute();
    $lastSession = $lastSessionStmt->fetch(PDO::FETCH_ASSOC);

    $session_id = 1; // По умолчанию первая сессия
    if ($lastSession) {
        if (!$lastSession['session_active']) {
            // Если предыдущая сессия завершена, начинаем новую
            $session_id = $lastSession['session_id'] + 1;
        } else {
            // Продолжаем текущую сессию
            $session_id = $lastSession['session_id'];
        }
    }

    $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message, chat_type, sender, session_active, session_id) VALUES (:user_id, :message, 'crypto', 'moderator', 1, :session_id)");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':session_id', $session_id);
    $stmt->execute();

    header("Location: mader_chat.php?user_id=$user_id");
    exit();
}

if (isset($_POST['end_session']) && $current_session_id) {
    // Удаляем все сообщения текущей сессии
    $stmt = $conn->prepare("DELETE FROM chat_messages WHERE user_id = :user_id AND chat_type = 'crypto' AND session_id = :session_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':session_id', $current_session_id);
    $stmt->execute();

    $message = "Модератор завершил сессию чата по криптовалюте.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':message', $message);
    $stmt->execute();

    header("Location: moderator.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат с клиентом</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="auth-buttons">
            <span>Привет, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="moderator.php"><button>Назад</button></a>
            <a href="index.php?logout=true"><button>Выход</button></a>
        </div>

        <h1>Чат с клиентом: <?php echo htmlspecialchars($client['username']); ?></h1>
        <div class="chat-body" id="chat-body">
            <div class="chat-messages" id="chat-messages">
                <?php foreach ($messages as $msg): ?>
                    <div class="chat-message <?php echo $msg['sender'] === 'user' ? 'user' : 'moderator'; ?>" data-message-id="<?php echo $msg['id']; ?>">
                        <strong><?php echo htmlspecialchars($msg['username']); ?>:</strong>
                        <?php echo htmlspecialchars($msg['message']); ?>
                        <small>(<?php echo $msg['created_at']; ?>)</small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Форма ввода всегда видна для модератора -->
        <form method="post" class="chat-input">
            <input type="text" name="message" placeholder="Введите сообщение..." required>
            <button type="submit" name="send_message">Отправить</button>
        </form>
        <?php if ($isSessionActive): ?>
            <form method="post" style="margin-top: 10px;">
                <button type="submit" name="end_session" class="reject-button">Закрыть чат</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatBody = document.getElementById('chat-body');
        const chatMessages = document.getElementById('chat-messages');

        let lastMessageId = chatMessages.lastElementChild ? parseInt(chatMessages.lastElementChild.getAttribute('data-message-id')) : 0;

        function addMessage(message) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('chat-message', message.sender);
            messageElement.setAttribute('data-message-id', message.id);
            messageElement.innerHTML = `<strong>${message.username}:</strong> ${message.message} <small>(${message.created_at})</small>`;
            chatMessages.appendChild(messageElement);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        setInterval(function () {
            fetch('load_chat.php?chat_type=crypto&user_id=<?php echo $user_id; ?>&last_id=' + lastMessageId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }

                    data.forEach(msg => {
                        addMessage(msg);
                        lastMessageId = Math.max(lastMessageId, parseInt(msg.id));
                    });

                    fetch('check_session.php?chat_type=crypto&user_id=<?php echo $user_id; ?>')
                        .then(response => response.json())
                        .then(data => {
                            if (!data.session_active) {
                                document.querySelector('form[method="post"]:last-child')?.remove();
                            } else if (!document.querySelector('form[method="post"]:last-child')) {
                                const endForm = document.createElement('form');
                                endForm.method = 'post';
                                endForm.style.marginTop = '10px';
                                endForm.innerHTML = '<button type="submit" name="end_session" class="reject-button">Закрыть чат</button>';
                                document.querySelector('.container').appendChild(endForm);
                            }
                        });
                })
                .catch(error => {
                    console.error('Ошибка загрузки сообщений:', error);
                });
        }, 5000);
    });
    </script>
</body>
</html>