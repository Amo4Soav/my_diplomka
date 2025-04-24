<?php
session_start();
require 'config.php';

// Регистрация
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'user'; // По умолчанию роль "user"
    header("Location: index.php");
    exit();
}

// Вход
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role']; // Сохраняем роль пользователя
        header("Location: index.php");
        exit();
    } else {
        echo "Неверное имя пользователя или пароль.";
    }
}

// Выход
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>