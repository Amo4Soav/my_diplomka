<?php
session_start();
require 'config.php';

// Проверяем авторизацию
if (!isset($_SESSION['username'])) {
    exit(json_encode(['error' => 'Пожалуйста, авторизуйтесь для использования чата.']));
}

// Получаем входные данные
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['message'])) {
    exit(json_encode(['error' => 'Ошибка: Сообщение не получено.']));
}
$message = strtolower(trim($input['message']));

// Синонимы криптовалют
$cryptoSynonyms = [
    'bitlion' => ['bitlion', 'битлайон', 'биткоин', 'бит лайон', 'битлайон', 'bit lion', 'биткойн', 'btl'],
    'etherowl' => ['etherowl', 'этероул', 'эфир', 'ether owl', 'этер оул', 'эфириум', 'eth', 'ethereum'],
    'litefox' => ['litefox', 'лайтфокс', 'лайткоин', 'lite fox', 'лайт фокс', 'ltf', 'litecoin', 'лайткойн'],
    'ravencoin' => ['ravencoin', 'рейвенкоин', 'воронкоин', 'raven coin', 'рейвен коин', 'ворон коин', 'rvn', 'raven'],
];

// Определяем криптовалюту
$cryptoName = '';
foreach ($cryptoSynonyms as $key => $synonyms) {
    foreach ($synonyms as $synonym) {
        if (strpos($message, $synonym) !== false) {
            $cryptoName = $key;
            break 2;
        }
    }
}

// Обработка команд
try {
    if (strpos($message, 'прайс') !== false || strpos($message, 'курс') !== false) {
        if ($cryptoName) {
            $query = $conn->prepare("SELECT name, price, date FROM cryptocurrencies WHERE name = :name ORDER BY date DESC LIMIT 1");
            $query->bindParam(':name', $cryptoName);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);

            $bot_message = $row ? "Курс {$row['name']}: {$row['price']} (на {$row['date']})" : "Данные о курсе {$cryptoName} отсутствуют.";
        } else {
            $query = $conn->query("SELECT name, price, date FROM cryptocurrencies WHERE date = (SELECT MAX(date) FROM cryptocurrencies)");
            $cryptoData = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($cryptoData)) {
                $bot_message = "Данные о курсах криптовалют отсутствуют.";
            } else {
                $bot_message = "Последние курсы криптовалют:\n" . implode("\n", array_map(fn($row) => "{$row['name']}: {$row['price']} (на {$row['date']})", $cryptoData));
            }
        }
        exit(json_encode(['message' => $bot_message]));
    }

    if (strpos($message, 'график') !== false && $cryptoName) {
        exit(json_encode(['message' => "График для {$cryptoName} доступен на главной странице!"]));
    }

    if (strpos($message, 'история') !== false && $cryptoName) {
        $period = 'week';
        if (strpos($message, 'месяц') !== false) $period = 'month';
        elseif (strpos($message, 'день') !== false) $period = 'day';

        $interval = $period === 'week' ? '7 DAY' : ($period === 'month' ? '1 MONTH' : '1 DAY');
        $query = $conn->prepare("SELECT name, price, date FROM cryptocurrencies WHERE name = :name AND date >= DATE_SUB(CURDATE(), INTERVAL $interval) ORDER BY date ASC");
        $query->bindParam(':name', $cryptoName);
        $query->execute();
        $history = $query->fetchAll(PDO::FETCH_ASSOC);

        $bot_message = empty($history) ? "История цен для {$cryptoName} за $period отсутствует." : "История цен {$cryptoName} за последний $period:\n" . implode("\n", array_map(fn($row) => "{$row['date']}: {$row['price']}", $history));
        exit(json_encode(['message' => $bot_message]));
    }

    if (strpos($message, 'список') !== false || strpos($message, 'все крипты') !== false) {
        $query = $conn->query("SELECT DISTINCT name FROM cryptocurrencies");
        $cryptoList = $query->fetchAll(PDO::FETCH_COLUMN);

        $bot_message = empty($cryptoList) ? "Список криптовалют пуст." : "Доступные криптовалюты:\n" . implode("\n", $cryptoList);
        exit(json_encode(['message' => $bot_message]));
    }

    if (strpos($message, 'привет') !== false || strpos($message, 'здравствуй') !== false) {
        exit(json_encode(['message' => "Привет! Чем могу помочь?"]));
    }

    if (strpos($message, 'пока') !== false || strpos($message, 'до свидания') !== false) {
        exit(json_encode(['message' => "До встречи! Если что, я тут."]));
    }

    // Если команда не распознана, возвращаем сообщение
    $bot_message = "Я передал твой запрос в OpenAI с твоего устройства!";
    echo json_encode(['message' => $bot_message]);

} catch (Exception $e) {
    error_log("Ошибка: " . $e->getMessage());
    echo json_encode(['error' => 'Ошибка сервера. Попробуйте снова позже.']);
}
?>