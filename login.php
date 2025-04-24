<?php
session_start();
require 'config.php';
require 'languages.php'; // Подключаем переводы

if (isset($_SESSION['username'])) {
    header("Location: index.php?lang=$lang");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];
        header("Location: index.php?lang=$lang");
        exit();
    } else {
        $error = $translations['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['login']; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">

        <h1 class="fade-in"><?php echo $translations['login']; ?></h1>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" class="auth-form">
            <input type="text" name="username" placeholder="<?php echo $translations['username']; ?>" required>
            <input type="password" name="password" placeholder="<?php echo $translations['password']; ?>" required>
            <button type="submit" class="submit-button"><?php echo $translations['login']; ?></button>
        </form>
        <a href="index.php?lang=<?php echo $lang; ?>"><button class="nav-button"><?php echo $translations['to_main']; ?></button></a>
    </div>
</body>
</html>