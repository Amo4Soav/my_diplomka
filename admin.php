<?php
session_start();
require 'config.php';
require 'languages.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$adminPassword = "160908";

if (!isset($_SESSION['admin_authenticated'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
        if ($_POST['admin_password'] === $adminPassword) {
            $_SESSION['admin_authenticated'] = true;
        } else {
            die("Неверный пароль админки.");
        }
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="<?php echo $lang; ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $translations['admin_panel']; ?></title>
            <link rel="stylesheet" href="styles.css">
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
        </head>
        <body>
            <div class="container">
                <div class="password-form fade-in">
                    <h2><?php echo $translations['admin_panel']; ?></h2>
                    <form action="admin.php" method="post" class="auth-form">
                        <input type="password" name="admin_password" placeholder="Пароль" required>
                        <button type="submit" class="submit-button"><?php echo $translations['login']; ?></button>
                    </form>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}

// Обработка действий с заказами
if (isset($_GET['approve'])) {
    $order_id = $_GET['approve'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'approved' WHERE id = :id");
    $stmt->bindParam(':id', $order_id);
    $stmt->execute();
    $_SESSION['success_message'] = "Заказ принят!";
    header("Location: admin.php?lang=$lang");
    exit();
}

if (isset($_GET['reject'])) {
    $order_id = $_GET['reject'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'rejected' WHERE id = :id");
    $stmt->bindParam(':id', $order_id);
    $stmt->execute();
    $_SESSION['success_message'] = "Заказ отклонён!";
    header("Location: admin.php?lang=$lang");
    exit();
}

// Обработка добавления, редактирования, удаления криптовалют
if (isset($_POST['add_price'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO cryptocurrencies (name, price, date) VALUES (:name, :price, :date)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    $_SESSION['success_message'] = $translations['add_success'];
    header("Location: admin.php?lang=$lang");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM cryptocurrencies WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $_SESSION['success_message'] = $translations['delete_success'];
    header("Location: admin.php?lang=$lang");
    exit();
}

$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM cryptocurrencies WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST['update_price'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("UPDATE cryptocurrencies SET name = :name, price = :price, date = :date WHERE id = :id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $_SESSION['success_message'] = $translations['edit_success'];
    header("Location: admin.php?lang=$lang");
    exit();
}

$filter_name = isset($_GET['filter_name']) ? $_GET['filter_name'] : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';

$sql = "SELECT * FROM cryptocurrencies";
$conditions = [];
if ($filter_name) $conditions[] = "name LIKE :name";
if ($filter_date) $conditions[] = "date = :date";
if (!empty($conditions)) $sql .= " WHERE " . implode(" AND ", $conditions);
$sql .= " ORDER BY date DESC";

$stmt = $conn->prepare($sql);
if ($filter_name) $stmt->bindValue(':name', "%$filter_name%");
if ($filter_date) $stmt->bindValue(':date', $filter_date);
$stmt->execute();
$cryptoData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Список пользователей
$usersQuery = $conn->query("SELECT * FROM users");
$usersData = $usersQuery->fetchAll(PDO::FETCH_ASSOC);

// Список заказов
$ordersQuery = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$ordersData = $ordersQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['admin_panel']; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="fade-in"><?php echo $translations['admin_panel']; ?></h1>
        <p class="welcome-text fade-in"><?php echo $translations['welcome']; ?>, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>

        <!-- Переключение языка -->
        <div class="language-switcher fade-in">
            <a href="admin.php?lang=ru" class="lang-link">Русский</a> |
            <a href="admin.php?lang=en" class="lang-link">English</a> |
            <a href="admin.php?lang=kz" class="lang-link">Қазақша</a>
        </div>

        <!-- Уведомления -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message fade-in"><?php echo $_SESSION['success_message']; ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Форма редактирования -->
        <?php if ($editData): ?>
            <div class="admin-section fade-in">
                <h2><?php echo $translations['edit']; ?></h2>
                <form action="admin.php?lang=<?php echo $lang; ?>" method="post" class="edit-form">
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                    <label for="name"><?php echo $translations['crypto']; ?>:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($editData['name']); ?>" required>
                    <label for="price"><?php echo $translations['price']; ?>:</label>
                    <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($editData['price']); ?>" required>
                    <label for="date"><?php echo $translations['date']; ?>:</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($editData['date']); ?>" required>
                    <button type="submit" name="update_price" class="submit-button"><?php echo $translations['edit']; ?></button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Форма добавления -->
        <div class="admin-section fade-in">
            <h2><?php echo $translations['add_price']; ?></h2>
            <form action="admin.php?lang=<?php echo $lang; ?>" method="post" class="add-form">
                <label for="name"><?php echo $translations['crypto']; ?>:</label>
                <input type="text" id="name" name="name" required>
                <label for="price"><?php echo $translations['price']; ?>:</label>
                <input type="number" step="0.01" id="price" name="price" required>
                <label for="date"><?php echo $translations['date']; ?>:</label>
                <input type="date" id="date" name="date" required>
                <button type="submit" name="add_price" class="submit-button"><?php echo $translations['add']; ?></button>
            </form>
        </div>

        <!-- Список криптовалют -->
        <div class="admin-section fade-in">
            <h2><?php echo $translations['crypto_data']; ?></h2>
            <?php if (empty($cryptoData)): ?>
                <p class="no-data"><?php echo $translations['no_data']; ?></p>
            <?php else: ?>
                <table class="crypto-table">
                    <thead>
                        <tr>
                            <th><?php echo $translations['crypto']; ?></th>
                            <th><?php echo $translations['price']; ?></th>
                            <th><?php echo $translations['date']; ?></th>
                            <th><?php echo $translations['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cryptoData as $row): ?>
                            <tr class="fade-in">
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['price']); ?></td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td>
                                    <a href="admin.php?edit=<?php echo $row['id']; ?>&lang=<?php echo $lang; ?>" class="action-link edit-link"><?php echo $translations['edit']; ?></a>
                                    <a href="admin.php?delete=<?php echo $row['id']; ?>&lang=<?php echo $lang; ?>" class="action-link delete-link" onclick="return confirm('<?php echo $translations['confirm_delete']; ?>');"><?php echo $translations['delete']; ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Список заказов -->
        <div class="admin-section fade-in">
            <h2><?php echo $translations['orders']; ?></h2>
            <?php if (empty($ordersData)): ?>
                <p class="no-data"><?php echo $translations['no_orders']; ?></p>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo $translations['user']; ?></th>
                            <th><?php echo $translations['crypto']; ?></th>
                            <th><?php echo $translations['amount']; ?></th>
                            <th><?php echo $translations['payment_method']; ?></th>
                            <th><?php echo $translations['status']; ?></th>
                            <th><?php echo $translations['date']; ?></th>
                            <th><?php echo $translations['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordersData as $order): ?>
                            <tr class="fade-in">
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td><?php echo htmlspecialchars($order['crypto_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['amount']); ?></td>
                                <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                <td class="status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></td>
                                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                                <td>
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <a href="admin.php?approve=<?php echo $order['id']; ?>&lang=<?php echo $lang; ?>" class="action-link approve-link"><?php echo $translations['approve']; ?></a>
                                        <a href="admin.php?reject=<?php echo $order['id']; ?>&lang=<?php echo $lang; ?>" class="action-link reject-link"><?php echo $translations['reject']; ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Список пользователей -->
        <div class="admin-section fade-in">
            <h2><?php echo $translations['users']; ?></h2>
            <?php if (empty($usersData)): ?>
                <p class="no-data"><?php echo $translations['no_users']; ?></p>
            <?php else: ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th><?php echo $translations['username']; ?></th>
                            <th><?php echo $translations['role']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usersData as $user): ?>
                            <tr class="fade-in">
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="auth-buttons">
            <a href="index.php?lang=<?php echo $lang; ?>"><button class="nav-button"><?php echo $translations['to_main']; ?></button></a>
        </div>
    </div>
</body>
</html>