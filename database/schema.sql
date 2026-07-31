-- G2Mena Rate Card — Database Schema
-- Run once: mysql -u root g2ratecard < database/schema.sql
-- Or paste into phpMyAdmin

CREATE DATABASE IF NOT EXISTS `g2ratecard`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `g2ratecard`;

CREATE TABLE IF NOT EXISTS `positions` (
    `id`             INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `designation`    VARCHAR(255)  NOT NULL,
    `monthly_salary` DECIMAL(12,2) NOT NULL,
    `is_active`      TINYINT(1)    NOT NULL DEFAULT 1,
    `sort_order`     SMALLINT      NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `proposals` (
    `id`           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `client_name`  VARCHAR(255)  NOT NULL,
    `project_name` VARCHAR(255)  NOT NULL,
    `multiplier`   DECIMAL(4,2)  NOT NULL,
    `currency`     CHAR(3)       NOT NULL DEFAULT 'AED',
    `notes`        TEXT,
    `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `proposal_items` (
    `id`             INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `proposal_id`    INT UNSIGNED  NOT NULL,
    `position_id`    INT UNSIGNED  NULL,
    `designation`    VARCHAR(255)  NOT NULL,
    `monthly_salary` DECIMAL(12,2) NOT NULL,
    `allocation`     DECIMAL(5,2)  NOT NULL DEFAULT 1.00,
    `sort_order`     SMALLINT      NOT NULL DEFAULT 0,
    FOREIGN KEY (`proposal_id`) REFERENCES `proposals`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Sample agency positions (monthly salary in AED)
INSERT INTO `positions` (`designation`, `monthly_salary`, `sort_order`) VALUES
('Account Executive',          15000.00,  1),
('Senior Account Executive',   22000.00,  2),
('Account Manager',            28000.00,  3),
('Account Director',           40000.00,  4),
('Social Media Executive',     15000.00,  5),
('Social Media Manager',       25000.00,  6),
('Creative Director',          50000.00,  7),
('Art Director',               35000.00,  8),
('Graphic Designer',           20000.00,  9),
('Senior Graphic Designer',    28000.00, 10),
('Copywriter',                 18000.00, 11),
('Senior Copywriter',          28000.00, 12),
('Content Creator',            15000.00, 13),
('Content Manager',            25000.00, 14),
('PR Manager',                 30000.00, 15),
('PR Executive',               18000.00, 16),
('Digital Marketing Manager',  32000.00, 17),
('SEO Specialist',             20000.00, 18),
('Media Planner',              25000.00, 19),
('Media Director',             45000.00, 20),
('Strategy Director',          55000.00, 21),
('Brand Strategist',           35000.00, 22),
('Project Manager',            28000.00, 23),
('Office Manager',             15000.00, 24);
