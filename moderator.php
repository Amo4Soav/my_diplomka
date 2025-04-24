<?php
session_start();
require 'config.php';
require 'languages.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'moderator') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['approve'])) {
    $order_id = $_POST['order_id'];
    $user_id = $_POST['user_id'];
    $crypto_name = $_POST['crypto_name'];
    $amount = $_POST['amount'];

    $stmt = $conn->prepare("UPDATE orders SET status = 'approved' WHERE id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();

    $message = "Ваш заказ на $amount $crypto_name одобрен модератором!";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':message', $message);
    $stmt->execute();

    header("Location: moderator.php");
    exit();
}

if (isset($_POST['reject'])) {
    $order_id = $_POST['order_id'];
    $user_id = $_POST['user_id'];
    $crypto_name = $_POST['crypto_name'];
    $amount = $_POST['amount'];

    $stmt = $conn->prepare("UPDATE orders SET status = 'rejected' WHERE id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();

    $message = "Ваш заказ на $amount $crypto_name отклонён модератором.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':message', $message);
    $stmt->execute();

    header("Location: moderator.php");
    exit();
}

$ordersStmt = $conn->prepare("SELECT o.id, o.user_id, o.crypto_name, o.amount, o.payment_method, o.status, u.username 
                             FROM orders o 
                             JOIN users u ON o.user_id = u.id 
                             WHERE o.status = 'pending'");
$ordersStmt->execute();
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель модератора</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="auth-buttons">
            <span>Привет, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="index.php?logout=true"><button>Выход</button></a>
        </div>

        <h1>Панель модератора</h1>

        <h2>Ожидающие заказы</h2>
        <?php if (empty($orders)): ?>
            <p>Нет заказов на проверку.</p>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Пользователь</th>
                        <th>Криптовалюта</th>
                        <th>Количество</th>
                        <th>Способ оплаты</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['crypto_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['amount']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $order['user_id']; ?>">
                                    <input type="hidden" name="crypto_name" value="<?php echo $order['crypto_name']; ?>">
                                    <input type="hidden" name="amount" value="<?php echo $order['amount']; ?>">
                                    <button type="submit" name="approve" class="approve-button">Одобрить</button>
                                    <button type="submit" name="reject" class="reject-button">Отказать</button>
                                </form>
                                <a href="mader_chat.php?user_id=<?php echo $order['user_id']; ?>"><button class="chat-button">Общаться с клиентом</button></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>