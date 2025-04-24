<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Не авторизован']);
    exit();
}

$userStmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
$userStmt->bindParam(':username', $_SESSION['username']);
$userStmt->execute();
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'] ?? '';
$chat_type = $data['chat_type'] ?? '';
$sender = $data['sender'] ?? '';

if (!$message || !$chat_type || !$sender || !in_array($chat_type, ['crypto', 'support']) || !in_array($sender, ['user', 'moderator', 'helper'])) {
    echo json_encode(['error' => 'Недостаточно данных или неверный тип']);
    exit();
}

// Проверяем последнюю сессию
$sessionStmt = $conn->prepare("SELECT session_id, session_active FROM chat_messages WHERE user_id = :user_id AND chat_type = :chat_type ORDER BY created_at DESC LIMIT 1");
$sessionStmt->bindParam(':user_id', $user['id']);
$sessionStmt->bindParam(':chat_type', $chat_type);
$sessionStmt->execute();
$session = $sessionStmt->fetch(PDO::FETCH_ASSOC);

$session_id = 1;
if ($session) {
    if (!$session['session_active']) {
        $session_id = $session['session_id'] + 1;
    } else {
        $session_id = $session['session_id'];
    }
}

$stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message, chat_type, sender, session_active, session_id) VALUES (:user_id, :message, :chat_type, :sender, 1, :session_id)");
$stmt->bindParam(':user_id', $user['id']);
$stmt->bindParam(':message', $message);
$stmt->bindParam(':chat_type', $chat_type);
$stmt->bindParam(':sender', $sender);
$stmt->bindParam(':session_id', $session_id);
$stmt->execute();

echo json_encode(['success' => true, 'session_id' => $session_id]);
?>