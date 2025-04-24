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

$chat_type = $_GET['chat_type'] ?? '';
$user_id = $_GET['user_id'] ?? $user['id'];
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if (!$chat_type) {
    echo json_encode(['error' => 'Не указан тип чата']);
    exit();
}

// Получаем текущий session_id для активной сессии
$sessionStmt = $conn->prepare("SELECT session_id FROM chat_messages WHERE user_id = :user_id AND chat_type = :chat_type AND session_active = 1 ORDER BY created_at DESC LIMIT 1");
$sessionStmt->bindParam(':user_id', $user_id);
$sessionStmt->bindParam(':chat_type', $chat_type);
$sessionStmt->execute();
$session = $sessionStmt->fetch(PDO::FETCH_ASSOC);
$current_session_id = $session ? $session['session_id'] : 1;

$stmt = $conn->prepare("SELECT cm.id, cm.message, cm.sender, cm.created_at, u.username 
                       FROM chat_messages cm 
                       JOIN users u ON cm.user_id = u.id 
                       WHERE cm.user_id = :user_id AND cm.chat_type = :chat_type AND cm.session_id = :session_id AND cm.id > :last_id 
                       ORDER BY cm.created_at ASC");
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':chat_type', $chat_type);
$stmt->bindParam(':session_id', $current_session_id);
$stmt->bindParam(':last_id', $last_id, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?>