<?php
session_start();
require 'config.php';
require 'languages.php'; // Подключаем переводы

if (!isset($_SESSION['username'])) {
    header("Location: login.php?lang=$lang");
    exit();
}

$userStmt = $conn->prepare("SELECT id, role FROM users WHERE username = :username");
$userStmt->bindParam(':username', $_SESSION['username']);
$userStmt->execute();
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'user') {
    header("Location: index.php?lang=$lang");
    exit();
}

// Получаем текущий session_id для активной сессии
$sessionStmt = $conn->prepare("SELECT session_id FROM chat_messages WHERE user_id = :user_id AND chat_type = 'support' AND session_active = 1 ORDER BY created_at DESC LIMIT 1");
$sessionStmt->bindParam(':user_id', $user['id']);
$sessionStmt->execute();
$session = $sessionStmt->fetch(PDO::FETCH_ASSOC);
$current_session_id = $session ? $session['session_id'] : null;

// Загружаем сообщения только для текущей активной сессии
$session_id_to_use = $current_session_id ?? 1;
$messagesStmt = $conn->prepare("SELECT cm.id, cm.message, cm.sender, cm.created_at, u.username 
                               FROM chat_messages cm 
                               JOIN users u ON cm.user_id = u.id 
                               WHERE cm.chat_type = 'support' AND cm.user_id = :user_id AND cm.session_id = :session_id 
                               ORDER BY cm.created_at ASC");
$messagesStmt->bindParam(':user_id', $user['id']);
$messagesStmt->bindParam(':session_id', $session_id_to_use);
$messagesStmt->execute();
$messages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);

$isSessionActive = $current_session_id !== null;

if (isset($_POST['end_session']) && $current_session_id) {
    $stmt = $conn->prepare("DELETE FROM chat_messages WHERE user_id = :user_id AND chat_type = 'support' AND session_id = :session_id");
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->bindParam(':session_id', $current_session_id);
    $stmt->execute();

    $message = $translations['support_chat'] . ": " . $translations['chat_closed_by_moderator'];
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)");
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->bindParam(':message', $message);
    $stmt->execute();

    header("Location: notifications.php?lang=$lang");
    exit();
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['support_chat']; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="auth-buttons">
            <span><?php echo $translations['hello']; ?>, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="notifications.php?lang=<?php echo $lang; ?>"><button><?php echo $translations['to_main']; ?></button></a>
            <a href="index.php?logout=true&lang=<?php echo $lang; ?>"><button><?php echo $translations['logout']; ?></button></a>
        </div>

        <h1><?php echo $translations['support_chat']; ?></h1>
        <div class="chat-body" id="chat-body">
            <div class="chat-messages" id="chat-messages">
                <?php foreach ($messages as $msg): ?>
                    <div class="chat-message <?php echo $msg['sender'] === 'user' ? 'user' : 'helper'; ?>" data-message-id="<?php echo $msg['id']; ?>">
                        <strong><?php echo htmlspecialchars($msg['username']); ?>:</strong>
                        <?php echo htmlspecialchars($msg['message']); ?>
                        <small>(<?php echo $msg['created_at']; ?>)</small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="chat-input">
            <input type="text" id="chat-input" placeholder="<?php echo $translations['type_message']; ?>">
            <button id="chat-send"><?php echo $translations['send']; ?></button>
        </div>
        <?php if ($isSessionActive): ?>
            <form method="post" style="margin-top: 10px;">
                <button type="submit" name="end_session" class="reject-button"><?php echo $translations['reject']; ?></button>
            </form>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatBody = document.getElementById('chat-body');
        const chatMessages = document.getElementById('chat-messages');
        const chatInput = document.getElementById('chat-input');
        const chatSend = document.getElementById('chat-send');

        let lastMessageId = chatMessages.lastElementChild ? parseInt(chatMessages.lastElementChild.getAttribute('data-message-id')) : 0;

        function addMessage(message) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('chat-message', message.sender);
            messageElement.setAttribute('data-message-id', message.id);
            messageElement.innerHTML = `<strong>${message.username}:</strong> ${message.message} <small>(${message.created_at})</small>`;
            chatMessages.appendChild(messageElement);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        chatSend.addEventListener('click', function () { sendMessage(); });
        chatInput.addEventListener('keydown', function (event) { if (event.key === 'Enter') sendMessage(); });

        function sendMessage() {
            const message = chatInput.value.trim();
            if (message) {
                fetch('save_chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: message, chat_type: 'support', sender: 'user' }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        addMessage({ sender: 'helper', message: data.error, username: 'Система', created_at: new Date().toISOString() });
                    } else {
                        addMessage({ sender: 'user', message: message, username: '<?php echo htmlspecialchars($_SESSION['username']); ?>', created_at: new Date().toISOString() });
                        chatInput.value = '';
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    addMessage({ sender: 'helper', message: '<?php echo $translations['error_chat']; ?>', username: 'Система', created_at: new Date().toISOString() });
                });
            }
        }

        setInterval(function () {
            fetch('load_chat.php?chat_type=support&last_id=' + lastMessageId)
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

                    fetch('check_session.php?chat_type=support')
                        .then(response => response.json())
                        .then(data => {
                            if (!data.session_active) {
                                document.querySelector('form[method="post"]')?.remove();
                            } else if (!document.querySelector('form[method="post"]')) {
                                const endForm = document.createElement('form');
                                endForm.method = 'post';
                                endForm.style.marginTop = '10px';
                                endForm.innerHTML = '<button type="submit" name="end_session" class="reject-button"><?php echo $translations['reject']; ?></button>';
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