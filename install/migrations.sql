-- ======================================================
-- GreyPanel v2.0 – Основные таблицы
-- ======================================================

-- 1. Пользователи
CREATE TABLE IF NOT EXISTS `{prefix}users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(250) NOT NULL,
  `group` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-пользователь,1-меценат,2-модератор,3-админ,4-root',
  `money` int(11) NOT NULL DEFAULT '0',
  `all_money` int(11) NOT NULL DEFAULT '0',
  `avatar` varchar(500) NOT NULL DEFAULT 'public/assets/img/avatar_default.png',
  `vk_id` int(11) NOT NULL DEFAULT '0',
  `reg_data` int(11) NOT NULL DEFAULT '0',
  `reg_ip` varchar(45) NOT NULL,
  `referral` int(11) NOT NULL DEFAULT '0',
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `vk_id` (`vk_id`),
  KEY `referral` (`referral`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Токены "запомнить меня"
CREATE TABLE IF NOT EXISTS `{prefix}user_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Настройки (ключ-значение)
CREATE TABLE IF NOT EXISTS `{prefix}settings` (
  `key` varchar(100) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Меню
CREATE TABLE IF NOT EXISTS `{prefix}menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num` int(11) NOT NULL DEFAULT '0',
  `text` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `module` varchar(100) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `ses` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-все,1-только авторизованные,2-только гости',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Чат
CREATE TABLE IF NOT EXISTS `{prefix}chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Онлайн
CREATE TABLE IF NOT EXISTS `{prefix}online` (
  `user_id` int(11) NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Ключи (для оплаты)
CREATE TABLE IF NOT EXISTS `{prefix}keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL,
  `coins` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-активен,1-использован,2-заблокирован',
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Логи денег
CREATE TABLE IF NOT EXISTS `{prefix}money_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-пополнение,1-списание',
  `amount` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Логи действий
CREATE TABLE IF NOT EXISTS `{prefix}logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Платежи
CREATE TABLE IF NOT EXISTS `{prefix}payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `system` varchar(50) NOT NULL,
  `amount` int(11) NOT NULL,
  `external_id` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0-ожидание,1-успех',
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_id` (`external_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Платежи (идемпотентность)
CREATE TABLE IF NOT EXISTS `{prefix}payment_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `system` varchar(50) NOT NULL,
  `amount` int(11) NOT NULL,
  `external_id` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- VIP система (устаревшая, но оставлена для совместимости)
-- ======================================================
CREATE TABLE IF NOT EXISTS `{prefix}vip_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-amxadmins,1-ftp,2-users_sql',
  `host` varchar(250) NOT NULL,
  `user` varchar(250) NOT NULL,
  `encrypted_password` text NOT NULL,
  `database` varchar(250) NOT NULL,
  `prefix` varchar(250) NOT NULL,
  `amx_id` int(11) NOT NULL DEFAULT '0',
  `server_name` varchar(250) NOT NULL,
  `server_ip` varchar(255) NOT NULL,
  `server_port` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}vip_privileges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `flags` varchar(32) NOT NULL,
  `price_per_day` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `server_id` (`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}vip_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `privilege_id` int(11) NOT NULL,
  `amx_id` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `expired_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `server_id` (`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- Форум
-- ======================================================
CREATE TABLE IF NOT EXISTS `{prefix}forum_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL,
  `description` varchar(250) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}forum_forums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` varchar(250) NOT NULL,
  `icon` varchar(100) NOT NULL DEFAULT 'fa fa-comments',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}forum_threads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `last_post_at` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `replies` int(11) NOT NULL DEFAULT '0',
  `is_sticky` tinyint(1) NOT NULL DEFAULT '0',
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `forum_id` (`forum_id`),
  KEY `last_post_at` (`last_post_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}forum_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `thread_id` (`thread_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}forum_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `target_type` enum('thread','post') NOT NULL,
  `target_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_target` (`user_id`, `target_type`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}forum_read` (
  `user_id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `last_read_at` int(11) NOT NULL,
  PRIMARY KEY (`user_id`, `thread_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- Мониторинг серверов (LGSL)
-- ======================================================
CREATE TABLE IF NOT EXISTS `{prefix}monitor_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT 'halflife',
  `ip` varchar(255) NOT NULL,
  `c_port` int(5) NOT NULL,
  `q_port` int(5) NOT NULL,
  `s_port` int(5) NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `cache` text NOT NULL,
  `cache_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- Warcraft модуль (опыт)
-- ======================================================
CREATE TABLE IF NOT EXISTS `{prefix}warcraft_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(250) NOT NULL,
  `user` varchar(250) NOT NULL,
  `encrypted_password` text NOT NULL,
  `database` varchar(250) NOT NULL,
  `prefix` varchar(250) NOT NULL,
  `server_name` varchar(250) NOT NULL,
  `server_ip_port` varchar(255) NOT NULL,
  `heroes_data` text NOT NULL,
  `const_one` int(11) NOT NULL DEFAULT '0',
  `const_all` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- Тикеты (обращения)
-- ======================================================
CREATE TABLE IF NOT EXISTS `{prefix}tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}ticket_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- Модули
-- ======================================================
CREATE TABLE `{prefix}modules` (
  `name` varchar(50) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- Вложения (для форума)
-- ======================================================
CREATE TABLE IF NOT EXISTS `{prefix}attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- Дополнительные поля и доработки (ALTER)
-- ======================================================

-- Добавляем счётчики пользователям
ALTER TABLE `{prefix}users` 
ADD COLUMN `count_theard` INT NOT NULL DEFAULT 0 AFTER `all_money`,
ADD COLUMN `count_post` INT NOT NULL DEFAULT 0 AFTER `count_theard`,
ADD COLUMN `count_like` INT NOT NULL DEFAULT 0 AFTER `count_post`;

-- Добавляем счётчики лайков в темы и посты
ALTER TABLE `{prefix}forum_threads` ADD COLUMN `likes_count` INT NOT NULL DEFAULT 0;
ALTER TABLE `{prefix}forum_posts` ADD COLUMN `likes_count` INT NOT NULL DEFAULT 0;

-- Добавляем заработок рефералов
ALTER TABLE `{prefix}users` ADD COLUMN `referral_earnings` INT NOT NULL DEFAULT 0 AFTER `referral`;

-- Добавляем настройки активной темы
INSERT INTO `{prefix}settings` (`key`, `value`) VALUES ('active_theme', 'default')
ON DUPLICATE KEY UPDATE `key` = `key`;

-- Вставляем модули по умолчанию
INSERT INTO `{prefix}modules` (`name`, `enabled`) VALUES 
('chat', 1), ('monitor', 1), ('bans', 1), ('warcraft', 0)
ON DUPLICATE KEY UPDATE `name` = `name`;

-- ======================================================
-- Интеграции в мониторинг серверов (новые поля)
-- ======================================================
ALTER TABLE `{prefix}monitor_servers` 
ADD COLUMN `privilege_storage` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1-users.ini, 2-AmXBans, 3-both',
ADD COLUMN `stats_engine` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1-CSStats, 2-AES, 3-both',
ADD COLUMN `amxbans_db_host` VARCHAR(255) NULL,
ADD COLUMN `amxbans_db_user` VARCHAR(100) NULL,
ADD COLUMN `amxbans_db_pass` VARCHAR(255) NULL,
ADD COLUMN `amxbans_db_name` VARCHAR(100) NULL,
ADD COLUMN `amxbans_db_prefix` VARCHAR(50) NULL,
ADD COLUMN `csstats_db_host` VARCHAR(255) NULL,
ADD COLUMN `csstats_db_user` VARCHAR(100) NULL,
ADD COLUMN `csstats_db_pass` VARCHAR(255) NULL,
ADD COLUMN `csstats_db_name` VARCHAR(100) NULL,
ADD COLUMN `aes_stats_db_host` VARCHAR(255) NULL,
ADD COLUMN `aes_stats_db_user` VARCHAR(100) NULL,
ADD COLUMN `aes_stats_db_pass` VARCHAR(255) NULL,
ADD COLUMN `aes_stats_db_name` VARCHAR(100) NULL,
ADD COLUMN `ftp_host` VARCHAR(255) NULL COMMENT 'FTP хост для users.ini',
ADD COLUMN `ftp_user` VARCHAR(100) NULL,
ADD COLUMN `ftp_pass` VARCHAR(255) NULL,
ADD COLUMN `ftp_path` VARCHAR(255) NULL COMMENT 'Путь к файлу users.ini';

-- ======================================================
-- Новостная лента
-- ======================================================
CREATE TABLE IF NOT EXISTS `{prefix}news` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `content` TEXT NOT NULL,
  `author_id` INT NOT NULL,
  `is_published` TINYINT(1) DEFAULT 1,
  `views` INT UNSIGNED DEFAULT 0,
  `created_at` INT NOT NULL,
  `updated_at` INT NOT NULL,
  FOREIGN KEY (`author_id`) REFERENCES `{prefix}users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- Расширение таблицы тикетов (для полноценной поддержки)
-- ======================================================
ALTER TABLE `{prefix}tickets` 
ADD COLUMN `category` ENUM('unban','general','bug','payment') NOT NULL DEFAULT 'general' AFTER `user_id`,
ADD COLUMN `subject` VARCHAR(255) NOT NULL AFTER `category`,
ADD COLUMN `ban_id` INT NULL AFTER `subject`;

ALTER TABLE `{prefix}ticket_messages` 
ADD COLUMN `is_staff` TINYINT(1) NOT NULL DEFAULT 0 AFTER `user_id`;