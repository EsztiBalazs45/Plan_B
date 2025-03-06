CREATE TABLE IF NOT EXISTS `newsletter` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `newsletter_title` VARCHAR(255) NOT NULL,
    `newsletter_status` INT NOT NULL,
    `user_id` INT NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
