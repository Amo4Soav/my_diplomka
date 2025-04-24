<?php
session_start();

// Проверяем, есть ли язык в GET или в сессии
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ru', 'en', 'kz'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Сохраняем язык в сессии
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang']; // Используем язык из сессии
} else {
    $lang = 'ru'; // Язык по умолчанию
    $_SESSION['lang'] = $lang;
}

$translations = [];

if ($lang === 'ru') {
    $translations = [
        'hello' => 'Привет',
        'to_main' => 'На главную',
        'logout' => 'Выход',
        'type_message' => 'Введите сообщение...',
        'send' => 'Отправить',
        'helper_panel' => 'Панель хелпера',
        'active_support_chats' => 'Активные чаты поддержки',
        'no_active_chats' => 'Нет активных чатов',
        'chat_with' => 'Чат с',
        'end_session' => 'Завершить сессию',
        'helper_ended_session' => 'Хелпер завершил сессию чата поддержки',

        // Общие фразы
        'welcome' => 'Добро пожаловать',
        'login' => 'Войти',
        'logout' => 'Выход',
        'register' => 'Регистрация',
        'to_main' => 'На главную',
        'username' => 'Имя пользователя',
        'password' => 'Пароль',
        'role' => 'Роль',
        'no_data' => 'Данные отсутствуют',
        'success' => 'Успешно!',
        'error' => 'Ошибка!',
        'settings' => 'Настройки',

        // Главная страница
        'crypto_analytics' => 'Аналитика криптовалют',
        'buy' => 'Купить',
        'hello' => 'Привет',
        'moderator' => 'Модератор',

        // Чат-бот
        'chatbot' => 'Чат-бот',
        'send' => 'Отправить',
        'type_message' => 'Введите сообщение...',
        'chat_crypto' => 'Чат криптовалют',
        'support' => 'Поддержка',
        'welcome_chat' => 'Привет! Чем могу помочь?',
        'error_chat' => 'Произошла ошибка. Попробуйте снова.',
        'chat_closed_by_moderator' => 'Чат закрыт модератором. Начните новую сессию, отправив сообщение.',

        // Админ-панель
        'admin_panel' => 'Админ-панель',
        'add_price' => 'Добавить цену',
        'crypto' => 'Криптовалюта',
        'price' => 'Цена',
        'date' => 'Дата',
        'add' => 'Добавить',
        'edit' => 'Редактировать',
        'delete' => 'Удалить',
        'crypto_data' => 'Данные о криптовалютах',
        'actions' => 'Действия',
        'confirm_delete' => 'Вы уверены, что хотите удалить?',
        'add_success' => 'Успешно добавлено!',
        'edit_success' => 'Успешно отредактировано!',
        'delete_success' => 'Успешно удалено!',
        'users' => 'Пользователи',
        'orders' => 'Заказы',
        'user' => 'Пользователь',
        'amount' => 'Количество',
        'payment_method' => 'Способ оплаты',
        'status' => 'Статус',
        'no_orders' => 'Заказы отсутствуют',
        'no_users' => 'Пользователи отсутствуют',
        'approve' => 'Принять',
        'reject' => 'Отклонить',

        // Страница заказов
        'buy_crypto' => 'Покупка криптовалюты',
        'select_payment' => 'Выберите способ оплаты',
        'submit_order' => 'Отправить заказ',
        'order_success' => 'Ваш заказ на %s %s принят на проверку. Ждите, пока админ решит!',
        'kaspi_bank' => 'Каспи Банк',
        'jusan_bank' => 'Жусан Банк',
        'halyk_bank' => 'Халык Банк',
        'kaspi_gold' => 'Kaspi Gold',

        // Уведомления
        'notifications' => 'Уведомления',
        'no_notifications' => 'Уведомления отсутствуют',
        'mark_as_read' => 'Отметить как прочитанное',

        // Модератор
        'moderator_panel' => 'Панель модератора',
        'moderate_orders' => 'Модерировать заказы',

        // Поддержка
        'support_chat' => 'Чат поддержки',
        'help_user' => 'Помочь пользователю',
    ];
} elseif ($lang === 'en') {
    $translations = [
        'hello' => 'Hello',
        'to_main' => 'To main',
        'logout' => 'Logout',
        'type_message' => 'Type a message...',
        'send' => 'Send',
        'helper_panel' => 'Helper Panel',
        'active_support_chats' => 'Active Support Chats',
        'no_active_chats' => 'No active chats',
        'chat_with' => 'Chat with',
        'end_session' => 'End Session',
        'helper_ended_session' => 'Helper has ended the support chat session',

        // Общие фразы
        'welcome' => 'Welcome',
        'login' => 'Login',
        'logout' => 'Logout',
        'register' => 'Register',
        'to_main' => 'To Main Page',
        'username' => 'Username',
        'password' => 'Password',
        'role' => 'Role',
        'no_data' => 'No data available',
        'success' => 'Success!',
        'error' => 'Error!',
        'settings' => 'Settings',

        // Главная страница
        'crypto_analytics' => 'Cryptocurrency Analytics',
        'buy' => 'Buy',
        'hello' => 'Hello',
        'moderator' => 'Moderator',

        // Чат-бот
        'chatbot' => 'Chatbot',
        'send' => 'Send',
        'type_message' => 'Type a message...',
        'chat_crypto' => 'Cryptocurrency Chat',
        'support' => 'Support',
        'welcome_chat' => 'Hello! How can I help you?',
        'error_chat' => 'An error occurred. Please try again.',
        'chat_closed_by_moderator' => 'Chat closed by moderator. Start a new session by sending a message.',

        // Админ-панель
        'admin_panel' => 'Admin Panel',
        'add_price' => 'Add Price',
        'crypto' => 'Cryptocurrency',
        'price' => 'Price',
        'date' => 'Date',
        'add' => 'Add',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'crypto_data' => 'Cryptocurrency Data',
        'actions' => 'Actions',
        'confirm_delete' => 'Are you sure you want to delete?',
        'add_success' => 'Successfully added!',
        'edit_success' => 'Successfully edited!',
        'delete_success' => 'Successfully deleted!',
        'users' => 'Users',
        'orders' => 'Orders',
        'user' => 'User',
        'amount' => 'Amount',
        'payment_method' => 'Payment Method',
        'status' => 'Status',
        'no_orders' => 'No orders available',
        'no_users' => 'No users available',
        'approve' => 'Approve',
        'reject' => 'Reject',

        // Страница заказов
        'buy_crypto' => 'Buy Cryptocurrency',
        'select_payment' => 'Select Payment Method',
        'submit_order' => 'Submit Order',
        'order_success' => 'Your order for %s %s has been submitted for review. Wait for admin approval!',
        'kaspi_bank' => 'Kaspi Bank',
        'jusan_bank' => 'Jusan Bank',
        'halyk_bank' => 'Halyk Bank',
        'kaspi_gold' => 'Kaspi Gold',

        // Уведомления
        'notifications' => 'Notifications',
        'no_notifications' => 'No notifications available',
        'mark_as_read' => 'Mark as Read',

        // Модератор
        'moderator_panel' => 'Moderator Panel',
        'moderate_orders' => 'Moderate Orders',

        // Поддержка
        'support_chat' => 'Support Chat',
        'help_user' => 'Help User',
    ];
} elseif ($lang === 'kz') {
    $translations = [
        'hello' => 'Сәлем',
        'to_main' => 'Басты бетке',
        'logout' => 'Шығу',
        'type_message' => 'Хабарлама жазыңыз...',
        'send' => 'Жіберу',
        'helper_panel' => 'Көмекші панелі',
        'active_support_chats' => 'Белсенді қолдау чаттары',
        'no_active_chats' => 'Белсенді чаттар жоқ',
        'chat_with' => 'Чатпен',
        'end_session' => 'Сессияны аяқтау',
        'helper_ended_session' => 'Көмекші қолдау чатының сессиясын аяқтады',

        // Общие фразы
        'welcome' => 'Қош келдіңіз',
        'login' => 'Кіру',
        'logout' => 'Шығу',
        'register' => 'Тіркелу',
        'to_main' => 'Басты бетке',
        'username' => 'Пайдаланушы аты',
        'password' => 'Құпия сөз',
        'role' => 'Рөл',
        'no_data' => 'Деректер жоқ',
        'success' => 'Сәтті!',
        'error' => 'Қате!',
        'settings' => 'Параметрлер',

        // Главная страница
        'crypto_analytics' => 'Криптовалюта аналитикасы',
        'buy' => 'Сатып алу',
        'hello' => 'Сәлем',
        'moderator' => 'Модератор',

        // Чат-бот
        'chatbot' => 'Чат-бот',
        'send' => 'Жіберу',
        'type_message' => 'Хабарламады енгізіңіз...',
        'chat_crypto' => 'Криптовалюта чаты',
        'support' => 'Қолдау',
        'welcome_chat' => 'Сәлем! Қалай көмектесе аламын?',
        'error_chat' => 'Қате болды. Қайтадан көріңіз.',
        'chat_closed_by_moderator' => 'Чатты модератор жапты. Жаңа сессияны бастау үшін хабарлама жіберіңіз.',

        // Админ-панель
        'admin_panel' => 'Админ панелі',
        'add_price' => 'Бағаны қосу',
        'crypto' => 'Криптовалюта',
        'price' => 'Баға',
        'date' => 'Күні',
        'add' => 'Қосу',
        'edit' => 'Өңдеу',
        'delete' => 'Жою',
        'crypto_data' => 'Криптовалюта деректері',
        'actions' => 'Әрекеттер',
        'confirm_delete' => 'Жоюды растағыңыз келе ме?',
        'add_success' => 'Сәтті қосылды!',
        'edit_success' => 'Сәтті өңделді!',
        'delete_success' => 'Сәтті жойылды!',
        'users' => 'Пайдаланушылар',
        'orders' => 'Тапсырыстар',
        'user' => 'Пайдаланушы',
        'amount' => 'Саны',
        'payment_method' => 'Төлем әдісі',
        'status' => 'Күй',
        'no_orders' => 'Тапсырыстар жоқ',
        'no_users' => 'Пайдаланушылар жоқ',
        'approve' => 'Қабылдау',
        'reject' => 'Қабылдамау',

        // Страница заказов
        'buy_crypto' => 'Криптовалюта сатып алу',
        'select_payment' => 'Төлем әдісін таңдаңыз',
        'submit_order' => 'Тапсырыс жіберу',
        'order_success' => 'Сіздің %s %s тапсырысыңыз тексеруге қабылданды. Админ шешімін күтіңіз!',
        'kaspi_bank' => 'Каспи Банк',
        'jusan_bank' => 'Жусан Банк',
        'halyk_bank' => 'Халық Банк',
        'kaspi_gold' => 'Kaspi Gold',

        // Уведомления
        'notifications' => 'Хабарламалар',
        'no_notifications' => 'Хабарламалар жоқ',
        'mark_as_read' => 'Оқылды деп белгілеу',

        // Модератор
        'moderator_panel' => 'Модератор панелі',
        'moderate_orders' => 'Тапсырыстарды модерациялау',

        // Поддержка
        'support_chat' => 'Қолдау чаты',
        'help_user' => 'Пайдаланушыға көмектесу',
    ];
}
?>