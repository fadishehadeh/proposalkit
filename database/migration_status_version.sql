-- ProposalKit — Status, Version & Parent migration
-- Run in phpMyAdmin or: mysql -u root g2ratecard < database/migration_status_version.sql

USE `g2ratecard`;

ALTER TABLE `proposals`
    ADD COLUMN `status`    ENUM('draft','sent','approved','rejected') NOT NULL DEFAULT 'draft' AFTER `notes`,
    ADD COLUMN `version`   SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER `status`,
    ADD COLUMN `parent_id` INT UNSIGNED NULL AFTER `version`;

ALTER TABLE `proposals`
    ADD CONSTRAINT `fk_proposals_parent`
    FOREIGN KEY (`parent_id`) REFERENCES `proposals`(`id`) ON DELETE SET NULL;
