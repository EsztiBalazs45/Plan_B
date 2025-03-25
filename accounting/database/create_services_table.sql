CREATE TABLE IF NOT EXISTS `services` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_name` VARCHAR(255) NOT NULL,
    `service_description` TEXT NOT NULL,
    `service_price` DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;