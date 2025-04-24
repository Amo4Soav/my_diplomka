<?php
session_start();
require 'config.php';
require 'languages.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'helper') {
    header("Location: index.php?lang=$lang");
    exit();
}

if (isset($_POST['end_session'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("UPDATE chat_messages SET session_active = 0 WHERE user_id = :user_id AND chat_type = 'support'");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    $message = $translations['helper_ended_session'];
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':message', $message);
    $stmt->execute();

    header("Location: helper.php?lang=$lang");
    exit();
}

if (isset($_POST['send_message'])) {
    $user_id = $_POST['user_id'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message, chat_type, sender, session_active) VALUES (:user_id, :message, 'support', 'helper', 1)");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':message', $message);
    $stmt->execute();

    header("Location: helper.php?lang=$lang");
    exit();
}

// Получаем активные чаты
$chatsStmt = $conn->prepare("SELECT DISTINCT cm.user_id, u.username 
                             FROM chat_messages cm 
                             JOIN users u ON cm.user_id = u.id 
                             WHERE cm.chat_type = 'support' AND cm.session_active = 1");
$chatsStmt->execute();
$activeChats = $chatsStmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем сообщения для каждого чата
$chatMessages = [];
foreach ($activeChats as $chat) {
    $messagesStmt = $conn->prepare("SELECT cm.id, cm.message, cm.sender, cm.created_at, u.username 
                                   FROM chat_messages cm 
                                   JOIN users u ON cm.user_id = u.id 
                                   WHERE cm.chat_type = 'support' AND cm.user_id = :user_id 
                                   ORDER BY cm.created_at ASC");
    $messagesStmt->bindParam(':user_id', $chat['user_id']);
    $messagesStmt->execute();
    $chatMessages[$chat['user_id']] = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['helper_panel']; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="auth-buttons">
            <span><?php echo $translations['hello']; ?>, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="index.php?lang=<?php echo $lang; ?>"><button><?php echo $translations['to_main']; ?></button></a>
            <a href="index.php?logout=true&lang=<?php echo $lang; ?>"><button><?php echo $translations['logout']; ?></button></a>
        </div>

        <h1><?php echo $translations['helper_panel']; ?></h1>

        <h2><?php echo $translations['active_support_chats']; ?></h2>
        <?php if (empty($activeChats)): ?>
            <p><?php echo $translations['no_active_chats']; ?></p>
        <?php else: ?>
            <?php foreach ($activeChats as $chat): ?>
                <div class="chat-section" data-user-id="<?php echo $chat['user_id']; ?>">
                    <h3><?php echo $translations['chat_with']; ?> <?php echo htmlspecialchars($chat['username']); ?></h3>
                    <div class="chat-body">
                        <div class="chat-messages" id="chat-messages-<?php echo $chat['user_id']; ?>">
                            <?php foreach ($chatMessages[$chat['user_id']] as $msg): ?>
                                <div class="chat-message <?php echo $msg['sender'] === 'user' ? 'user' : 'helper'; ?>" data-message-id="<?php echo $msg['id']; ?>">
                                    <strong><?php echo htmlspecialchars($msg['username']); ?>:</strong>
                                    <?php echo htmlspecialchars($msg['message']); ?>
                                    <small>(<?php echo $msg['created_at']; ?>)</small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <form method="post" class="chat-input">
                        <input type="hidden" name="user_id" value="<?php echo $chat['user_id']; ?>">
                        <input type="text" name="message" placeholder="<?php echo $translations['type_message']; ?>" required>
                        <button type="submit" name="send_message"><?php echo $translations['send']; ?></button>
                    </form>
                    <form method="post" style="margin-top: 10px;">
                        <input type="hidden" name="user_id" value="<?php echo $chat['user_id']; ?>">
                        <button type="submit" name="end_session" class="reject-button"><?php echo $translations['end_session']; ?></button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatSections = document.querySelectorAll('.chat-section');
        
        chatSections.forEach(section => {
            const userId = section.getAttribute('data-user-id');
            const chatMessages = document.getElementById('chat-messages-' + userId);
            let lastMessageId = chatMessages.lastElementChild ? parseInt(chatMessages.lastElementChild.getAttribute('data-message-id')) : 0;

            function addMessage(message) {
                const messageElement = document.createElement('div');
                messageElement.classList.add('chat-message', message.sender);
                messageElement.setAttribute('data-message-id', message.id);
                messageElement.innerHTML = `<strong>${message.username}:</strong> ${message.message} <small>(${message.created_at})</small>`;
                chatMessages.appendChild(messageElement);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            setInterval(function () {
                fetch('load_chat.php?chat_type=support&last_id=' + lastMessageId + '&user_id=' + userId)
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
                    })
                    .catch(error => {
                        console.error('Ошибка загрузки сообщений:', error);
                    });
            }, 5000); // Обновление каждые 5 секунд
        });
    });
    </script>
</body>
</html>