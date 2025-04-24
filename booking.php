<?php
session_start();
require 'config.php';
require 'languages.php'; // Подключаем переводы

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['buy_crypto'])) {
    $userStmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $userStmt->bindParam(':username', $_SESSION['username']);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    $crypto_name = $_POST['crypto_name'];
    $amount = floatval($_POST['amount']);
    $payment_method = $_POST['payment_method'];

    $stmt = $conn->prepare("INSERT INTO orders (user_id, crypto_name, amount, payment_method) VALUES (:user_id, :crypto_name, :amount, :payment_method)");
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->bindParam(':crypto_name', $crypto_name);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':payment_method', $payment_method);
    $stmt->execute();

    $_SESSION['order_message'] = sprintf($translations['order_success'], $amount, $crypto_name);
    header("Location: index.php");
    exit();
}

$crypto_name = isset($_GET['crypto']) ? $_GET['crypto'] : '';
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['buy_crypto']; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="booking-styles.css">
</head>
<body>
    <div class="booking-container">
        <div class="booking-content">
            <h2><?php echo $translations['buy_crypto'] . ' ' . htmlspecialchars($crypto_name); ?></h2>
            <form method="post" action="booking.php">
                <input type="hidden" name="crypto_name" value="<?php echo htmlspecialchars($crypto_name); ?>">
                <label for="amount"><?php echo $translations['amount']; ?>:</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
                <label for="payment_method"><?php echo $translations['select_payment']; ?>:</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="Каспи Банк"><?php echo $translations['kaspi_bank']; ?></option>
                    <option value="Жусан Банк"><?php echo $translations['jusan_bank']; ?></option>
                    <option value="Халык Банк"><?php echo $translations['halyk_bank']; ?></option>
                    <option value="Kaspi Gold"><?php echo $translations['kaspi_gold']; ?></option>
                </select>
                <button type="submit" name="buy_crypto"><?php echo $translations['submit_order']; ?></button>
                <a href="index.php"><button type="button" class="back-button"><?php echo $translations['to_main']; ?></button></a>
            </form>
        </div>
    </div>
</body>
</html>