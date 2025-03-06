CREATE TABLE IF NOT EXISTS `services` (
    `service_name` VARCHAR(255) NOT NULL,
    `service_description` TEXT NOT NULL,
    `service_price` DECIMAL(10,2) NOT NULL,
    `service_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;