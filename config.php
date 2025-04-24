<?php
$host = 'localhost'; // Хост
$dbname = 'cn91210_amoproje'; // Имя базы данных
$username = 'cn91210_amoproje'; // Имя пользователя (по умолчанию в OpenServer это 'root')
$password = '11998822Alisher'; // Пароль (по умолчанию в OpenServer пароль пустой)

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

$api_key = 'YOUR_API_KAY';
?>