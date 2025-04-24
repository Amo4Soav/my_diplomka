<?php
session_start();
require 'config.php';
require 'languages.php'; // Подключаем переводы

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['register']; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Подключаем хедер -->
    <?php include 'header.php'; ?>

    <div class="container">
        <h1 class="fade-in"><?php echo $translations['register']; ?></h1>
        <form method="POST" class="auth-form">
            <input type="text" name="username" placeholder="<?php echo $translations['username']; ?>" required>
            <input type="password" name="password" placeholder="<?php echo $translations['password']; ?>" required>
            <button type="submit" class="submit-button"><?php echo $translations['register']; ?></button>
        </form>
        <a href="index.php"><button class="nav-button"><?php echo $translations['to_main']; ?></button></a>
    </div>
</body>
</html>