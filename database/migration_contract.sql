-- ProposalKit — Contract file path on proposals
-- Run in phpMyAdmin or: mysql -u root g2ratecard < database/migration_contract.sql

ALTER TABLE `proposals`
    ADD COLUMN `contract_path` VARCHAR(255) NULL AFTER `notes`;
