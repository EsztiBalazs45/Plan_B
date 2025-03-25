CREATE TABLE IF NOT EXISTS `payment_details` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT(20) UNSIGNED NOT NULL,
    `subscription_id` BIGINT(20) UNSIGNED NOT NULL,
    `payment_method` ENUM('local', 'bank_transfer') NOT NULL,
    `name` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `bank_account` VARCHAR(20) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(19, 2) NOT NULL,
    PRIMARY KEY (`payment_id`),
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`subscription_id`) ON DELETE CASCADE
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

