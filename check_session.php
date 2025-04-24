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

if (!$chat_type) {
    echo json_encode(['error' => 'Не указан тип чата']);
    exit();
}

$sessionStmt = $conn->prepare("SELECT session_active, session_id FROM chat_messages WHERE user_id = :user_id AND chat_type = :chat_type ORDER BY created_at DESC LIMIT 1");
$sessionStmt->bindParam(':user_id', $user_id);
$sessionStmt->bindParam(':chat_type', $chat_type);
$sessionStmt->execute();
$session = $sessionStmt->fetch(PDO::FETCH_ASSOC);

$isSessionActive = $session ? $session['session_active'] : true;

echo json_encode(['session_active' => $isSessionActive, 'session_id' => $session['session_id'] ?? 1]);
?>