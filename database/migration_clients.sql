-- ProposalKit — Clients migration
-- Run in phpMyAdmin or: mysql -u root g2ratecard < database/migration_clients.sql

USE `g2ratecard`;

CREATE TABLE IF NOT EXISTS `clients` (
    `id`            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(150)  NOT NULL,
    `contact_name`  VARCHAR(100)  NULL,
    `contact_email` VARCHAR(150)  NULL,
    `contact_phone` VARCHAR(50)   NULL,
    `industry`      VARCHAR(100)  NULL,
    `notes`         TEXT          NULL,
    `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `proposals`
    ADD COLUMN `client_id` INT UNSIGNED NULL AFTER `company_id`;

ALTER TABLE `proposals`
    ADD CONSTRAINT `fk_proposals_client`
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL;
