-- Migration: Add multi-company support
-- Run once: mysql -u root g2ratecard < database/migration_companies.sql

USE `g2ratecard`;

CREATE TABLE IF NOT EXISTS `companies` (
    `id`         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(255)  NOT NULL,
    `slug`       VARCHAR(100)  NOT NULL,
    `logo_path`  VARCHAR(500)  NULL,
    `is_active`  TINYINT(1)    NOT NULL DEFAULT 1,
    `sort_order` SMALLINT      NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB;

-- Seed the four agencies
INSERT IGNORE INTO `companies` (`name`, `slug`, `sort_order`) VALUES
('G2Mena',    'g2mena',    1),
('GreyDoha',  'greydoha',  2),
('Pin&Notch', 'pinnotch',  3),
('8Mena',     '8mena',     4);

-- Add company_id to positions (nullable so existing rows don't break)
ALTER TABLE `positions`
    ADD COLUMN IF NOT EXISTS `company_id` INT UNSIGNED NULL AFTER `id`;

-- Assign existing positions to G2Mena
UPDATE `positions` SET `company_id` = 1 WHERE `company_id` IS NULL;

-- Add FK after data is set
ALTER TABLE `positions`
    ADD CONSTRAINT IF NOT EXISTS `fk_positions_company`
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL;

-- Add company_id to proposals
ALTER TABLE `proposals`
    ADD COLUMN IF NOT EXISTS `company_id` INT UNSIGNED NULL AFTER `id`;

ALTER TABLE `proposals`
    ADD CONSTRAINT IF NOT EXISTS `fk_proposals_company`
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL;
