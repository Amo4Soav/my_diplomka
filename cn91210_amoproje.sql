-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Апр 24 2025 г., 22:30
-- Версия сервера: 8.0.41-32
-- Версия PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `cn91210_amoproje`
--

-- --------------------------------------------------------

--
-- Структура таблицы `chat_messages`
--

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `chat_type` enum('crypto','support') NOT NULL,
  `sender` enum('user','moderator','helper') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `session_active` tinyint(1) DEFAULT '1',
  `session_id` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `user_id`, `message`, `chat_type`, `sender`, `created_at`, `session_active`, `session_id`) VALUES
(56, 9, 'привет я купил у вас 15 криптовалют', 'support', 'user', '2025-04-07 13:38:15', 0, 1),
(57, 9, 'ghbdtn', 'support', 'helper', '2025-04-07 13:38:23', 0, 1),
(58, 9, 'ывывы', 'support', 'user', '2025-04-07 13:38:35', 0, 1),
(59, 12, 'привет', 'support', 'user', '2025-04-08 07:06:02', 0, 1),
(60, 12, 'привет', 'support', 'helper', '2025-04-08 07:06:15', 0, 1),
(61, 12, 'у меня не работает чат гпт с ии из за этого не смог получить данные', 'support', 'user', '2025-04-08 07:07:01', 0, 1),
(62, 12, 'можете помочь ?', 'support', 'user', '2025-04-08 07:07:08', 0, 1),
(63, 12, 'да щяс проверю', 'support', 'helper', '2025-04-08 07:07:36', 0, 1),
(64, 12, 'да данныы', 'support', 'helper', '2025-04-08 07:07:41', 0, 1),
(65, 12, 'момент сайте ии неработает извеняюсь за  такое ошибку ', 'support', 'helper', '2025-04-08 07:08:04', 0, 1),
(66, 12, 'скором времени все исправим', 'support', 'helper', '2025-04-08 07:08:19', 0, 1),
(67, 12, 'ку', 'support', 'helper', '2025-04-08 07:09:02', 0, 1),
(68, 12, 'как дела че делаешь ?', 'support', 'helper', '2025-04-08 07:09:10', 0, 1),
(69, 12, 'Я тупой', 'support', 'user', '2025-04-08 07:09:29', 0, 1),
(70, 12, 'согласен', 'support', 'helper', '2025-04-08 07:09:37', 0, 1),
(71, 12, 'АХАХАХХАХ', 'support', 'helper', '2025-04-08 07:09:41', 0, 1),
(72, 12, 'спасбо', 'support', 'user', '2025-04-08 07:09:45', 0, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `cryptocurrencies`
--

CREATE TABLE IF NOT EXISTS `cryptocurrencies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `cryptocurrencies`
--

INSERT INTO `cryptocurrencies` (`id`, `name`, `price`, `date`) VALUES
(13, 'BitLion', 150.00, '2024-06-15'),
(14, 'BitLion', 155.00, '2024-06-16'),
(15, 'BitLion', 160.00, '2024-06-17'),
(16, 'BitLion', 158.00, '2024-06-18'),
(17, 'BitLion', 162.00, '2024-06-19'),
(18, 'BitLion', 165.00, '2024-06-20'),
(19, 'BitLion', 170.00, '2024-07-01'),
(20, 'BitLion', 175.00, '2024-07-02'),
(21, 'BitLion', 180.00, '2024-07-03'),
(22, 'BitLion', 178.00, '2024-07-04'),
(23, 'BitLion', 182.00, '2024-07-05'),
(24, 'EtherOwl', 80.00, '2024-06-15'),
(25, 'EtherOwl', 85.00, '2024-06-16'),
(26, 'EtherOwl', 90.00, '2024-06-17'),
(27, 'EtherOwl', 88.00, '2024-06-18'),
(28, 'EtherOwl', 92.00, '2024-06-19'),
(29, 'EtherOwl', 95.00, '2024-06-20'),
(30, 'EtherOwl', 100.00, '2024-07-01'),
(31, 'EtherOwl', 105.00, '2024-07-02'),
(32, 'EtherOwl', 110.00, '2024-07-03'),
(33, 'EtherOwl', 108.00, '2024-07-04'),
(34, 'EtherOwl', 112.00, '2024-07-05'),
(35, 'LiteFox', 30.00, '2024-06-15'),
(36, 'LiteFox', 35.00, '2024-06-16'),
(37, 'LiteFox', 40.00, '2024-06-17'),
(38, 'LiteFox', 38.00, '2024-06-18'),
(39, 'LiteFox', 42.00, '2024-06-19'),
(40, 'LiteFox', 45.00, '2024-06-20'),
(41, 'LiteFox', 50.00, '2024-07-01'),
(42, 'LiteFox', 55.00, '2024-07-02'),
(43, 'LiteFox', 60.00, '2024-07-03'),
(44, 'LiteFox', 58.00, '2024-07-04'),
(45, 'LiteFox', 62.00, '2024-07-05'),
(46, 'RavenCoin', 10.00, '2024-06-15'),
(47, 'RavenCoin', 12.00, '2024-06-16'),
(48, 'RavenCoin', 15.00, '2024-06-17'),
(49, 'RavenCoin', 14.00, '2024-06-18'),
(50, 'RavenCoin', 16.00, '2024-06-19'),
(51, 'RavenCoin', 18.00, '2024-06-20'),
(52, 'RavenCoin', 20.00, '2024-07-01'),
(53, 'RavenCoin', 22.00, '2024-07-02'),
(54, 'RavenCoin', 25.00, '2024-07-03'),
(55, 'RavenCoin', 24.00, '2024-07-04'),
(56, 'RavenCoin', 26.00, '2024-07-05');

-- --------------------------------------------------------

--
-- Структура таблицы `logStart`
--

CREATE TABLE IF NOT EXISTS `logStart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `path` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `logStart`
--

INSERT INTO `logStart` (`id`, `login`, `password`, `path`) VALUES
(1, 'Diana', '112211', 'food/index.php'),
(2, 'Amo', '11221122', 'https://cn91210.tw1.ru/amo/index.php');

-- --------------------------------------------------------

--
-- Структура таблицы `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 4, 'Модератор завершил сессию чата по криптовалюте.', 1, '2025-03-23 15:52:55'),
(2, 4, 'Ваш заказ на 15.00 BitLion одобрен модератором!', 1, '2025-03-23 15:52:59'),
(3, 4, 'Ваш заказ на 25.00 RavenCoin отклонён модератором.', 1, '2025-03-23 17:51:57'),
(4, 4, 'Модератор завершил сессию чата по криптовалюте.', 1, '2025-03-23 18:00:02'),
(5, 4, 'Модератор завершил сессию чата по криптовалюте.', 1, '2025-03-23 18:00:21'),
(6, 4, 'Ваш заказ на 35.00 LiteFox одобрен модератором!', 1, '2025-03-23 18:00:24'),
(7, 4, 'Модератор завершил сессию чата по криптовалюте.', 1, '2025-03-23 18:07:25'),
(8, 4, 'Ваш заказ на 155.00 EtherOwl одобрен модератором!', 1, '2025-03-23 18:07:27'),
(9, 4, 'Вы завершили сессию чата поддержки.', 1, '2025-03-23 18:28:15'),
(10, 4, 'Хелпер завершил сессию чата поддержки.', 1, '2025-03-23 18:28:19'),
(11, 4, 'Вы завершили сессию чата поддержки.', 1, '2025-03-23 18:33:06'),
(12, 4, 'Вы завершили сессию чата поддержки.', 1, '2025-03-23 18:36:02'),
(13, 4, 'Вы завершили сессию чата поддержки.', 1, '2025-03-23 18:38:59'),
(14, 7, 'Модератор завершил сессию чата по криптовалюте.', 1, '2025-03-27 18:30:34'),
(15, 7, 'Ваш заказ на 112.00 BitLion одобрен модератором!', 1, '2025-03-27 18:30:35'),
(16, 7, 'Ваш заказ на 154.00 RavenCoin одобрен модератором!', 1, '2025-03-27 18:30:35'),
(17, 7, 'Вы завершили сессию чата поддержки.', 1, '2025-03-29 08:43:54'),
(18, 7, 'Хелпер завершил сессию чата поддержки', 1, '2025-03-29 09:11:04'),
(19, 7, 'Чат поддержки: Чат закрыт модератором. Начните новую сессию, отправив сообщение.', 1, '2025-03-29 09:11:18'),
(20, 7, 'Чат поддержки: Чат закрыт модератором. Начните новую сессию, отправив сообщение.', 1, '2025-03-29 09:14:12'),
(21, 7, 'Хелпер завершил сессию чата поддержки', 1, '2025-03-29 09:14:13'),
(22, 9, 'Хелпер завершил сессию чата поддержки', 1, '2025-04-07 13:38:41'),
(23, 12, 'Хелпер завершил сессию чата поддержки', 1, '2025-04-08 07:09:47');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `crypto_name` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `crypto_name`, `amount`, `payment_method`, `status`, `created_at`) VALUES
(1, 4, 'BitLion', 15.00, 'Kaspi Gold', 'approved', '2025-03-23 14:02:51'),
(2, 4, 'RavenCoin', 25.00, 'Каспи Банк', 'rejected', '2025-03-23 15:55:34'),
(3, 4, 'LiteFox', 35.00, 'Халык Банк', 'approved', '2025-03-23 17:53:03'),
(4, 4, 'EtherOwl', 155.00, 'Каспи Банк', 'approved', '2025-03-23 18:00:56'),
(5, 7, 'BitLion', 112.00, 'Kaspi Gold', 'approved', '2025-03-27 18:29:14'),
(6, 7, 'RavenCoin', 154.00, 'Каспи Банк', 'approved', '2025-03-27 18:29:25');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(4, 'AmoPeek', '$2y$10$hTQYqjZTGbBMkzEAuJNvy.K0jdLi0KaFwhpcCNMnhrPQxzYVQTLpu', 'user'),
(7, 'Amo112211', '$2y$10$yl9NQZzDQPdyNkLceDVTXONHhgIZb4IKTS7rs0VwS2gF5LwjVu0Zq', 'user'),
(8, 'Alo', '$2y$10$NuXwzY5ZXpH1TFsyNUm/lucpn/bi5wrmigWv7XPHTxm9ww.VViz2u', 'helper'),
(9, 'Amo', '$2y$10$uznHk0YY8Vh5.3PzQ.BrA.jTufdZKBMKVwZtfGGrDSSy08cAoj5Zi', 'user'),
(10, 'Amo2', '$2y$10$iu/54lK7mHhbTMu.aQNq3uB8PH7vJA/t188htmmtwS3l3oh48.W.e', 'user'),
(11, 'Aslan', '$2y$10$WK46FO5rFZQhHgxDSuU.Yeq91Yb66nhYkomXJooDJpI6DgBnKuYcO', 'user'),
(12, 'Nur', '$2y$10$HPwnAYUBPXhmQ6eFX3jVvuh7uOaVun.HFh.JNTSkkYcZHsbrVo86S', 'user');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
