CREATE TABLE IF NOT EXISTS `{prefix}user_groups` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `flags` varchar(32) NOT NULL DEFAULT '',
    `is_default` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` int(11) NOT NULL,
    `updated_at` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT 0,
  `username` varchar(32) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(250) NOT NULL,
  `money` int(11) NOT NULL DEFAULT '0',
  `all_money` int(11) NOT NULL DEFAULT '0',
  `count_thread` INT NOT NULL DEFAULT 0,
  `count_post` INT NOT NULL DEFAULT 0,
  `count_like` INT NOT NULL DEFAULT 0,
  `avatar` varchar(500) NOT NULL DEFAULT 'public/assets/img/avatar_default.png',
  `vk_id` int(11) NOT NULL DEFAULT '0',
  `reg_data` int(11) NOT NULL DEFAULT '0',
  `reg_ip` varchar(45) NOT NULL,
  `referral` int(11) NOT NULL DEFAULT '0',
  `referral_earnings` INT NOT NULL DEFAULT 0,
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `vk_id` (`vk_id`),
  KEY `referral` (`referral`),
  FOREIGN KEY (`group_id`) REFERENCES `{prefix}user_groups`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}user_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}settings` (
  `key` varchar(100) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS `{prefix}chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}online` (
  `user_id` int(11) NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `likes_count` INT NOT NULL DEFAULT 0,
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
  `likes_count` INT NOT NULL DEFAULT 0,
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
  `privilege_storage` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1-users.ini, 2-AmXBans, 3-both',
  `stats_engine` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1-CSStats, 2-AES, 3-both',
  `amxbans_db_host` VARCHAR(255) NULL,
  `amxbans_db_user` VARCHAR(100) NULL,
  `amxbans_db_pass` VARCHAR(255) NULL,
  `amxbans_db_name` VARCHAR(100) NULL,
  `amxbans_db_prefix` VARCHAR(50) NULL,
  `csstats_db_host` VARCHAR(255) NULL,
  `csstats_db_user` VARCHAR(100) NULL,
  `csstats_db_pass` VARCHAR(255) NULL,
  `csstats_db_name` VARCHAR(100) NULL,
  `aes_stats_db_host` VARCHAR(255) NULL,
  `aes_stats_db_user` VARCHAR(100) NULL,
  `aes_stats_db_pass` VARCHAR(255) NULL,
  `aes_stats_db_name` VARCHAR(100) NULL,
  `ftp_host` VARCHAR(255) NULL COMMENT 'FTP хост для users.ini',
  `ftp_user` VARCHAR(100) NULL,
  `ftp_pass` VARCHAR(255) NULL,
  `ftp_path` VARCHAR(255) NULL COMMENT 'Путь к файлу users.ini',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category` ENUM('unban','general','bug','payment') NOT NULL DEFAULT 'general',
  `subject` VARCHAR(255) NOT NULL,
  `ban_id` INT NULL,
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
  `is_staff` TINYINT(1) NOT NULL DEFAULT 0,
  `message` text NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}modules` (
  `name` varchar(50) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `{prefix}modules` (`name`, `enabled`) VALUES 
('chat', 1), ('monitor', 1), ('bans', 1), ('warcraft', 0)
ON DUPLICATE KEY UPDATE `name` = `name`;

CREATE TABLE IF NOT EXISTS `{prefix}attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS `{prefix}services` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `rights` varchar(64) NOT NULL DEFAULT '',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `created_at` int(11) NOT NULL,
    `updated_at` int(11) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}service_servers` (
    `service_id` int(11) NOT NULL,
    `server_id` int(11) NOT NULL,
    PRIMARY KEY (`service_id`, `server_id`),
    FOREIGN KEY (`service_id`) REFERENCES `{prefix}services`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`server_id`) REFERENCES `{prefix}monitor_servers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}tariffs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `service_id` int(11) NOT NULL,
    `duration_days` int(11) NOT NULL,
    `price` int(11) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `created_at` int(11) NOT NULL,
    `updated_at` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`service_id`) REFERENCES `{prefix}services`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{prefix}user_services` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `service_id` int(11) NOT NULL,
    `tariff_id` int(11) NOT NULL,
    `expires_at` int(11) NOT NULL,
    `created_at` int(11) NOT NULL,
    `updated_at` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `expires_at` (`expires_at`),
    FOREIGN KEY (`user_id`) REFERENCES `{prefix}users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `{prefix}services`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tariff_id`) REFERENCES `{prefix}tariffs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `{prefix}services` ADD COLUMN `group_id` INT NULL DEFAULT NULL AFTER `sort_order`;