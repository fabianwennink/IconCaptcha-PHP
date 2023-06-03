-- Challenges
CREATE TABLE iconcaptcha_challenges (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `challenge_id` varchar(36) NOT NULL,
    `widget_id` varchar(36) NOT NULL,
    `puzzle` text NOT NULL,
    `ip_address` varbinary(16) NOT NULL,
    `expires_at` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY sessions_widget_challenge (`challenge_id`, `widget_id`)
);

-- Attempts
CREATE TABLE `iconcaptcha_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ip_address` varbinary(16) NOT NULL,
  `attempts` int NOT NULL,
  `timeout_until` datetime NULL,
  `valid_until` datetime NOT NULL,
  UNIQUE KEY iconcaptcha_attempts_ip_address (`ip_address`)
);
