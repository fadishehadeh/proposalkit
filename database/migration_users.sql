-- Run this migration on any new environment
-- Creates users table and user_company assignments

CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(100)  NOT NULL,
  `email`         VARCHAR(150)  NOT NULL,
  `password_hash` VARCHAR(255)  NOT NULL,
  `role`          ENUM('superadmin','user') NOT NULL DEFAULT 'user',
  `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_companies` (
  `user_id`    INT UNSIGNED NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `company_id`),
  CONSTRAINT `fk_uc_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)     ON DELETE CASCADE,
  CONSTRAINT `fk_uc_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default superadmin: admin@g2group.com / Admin@2026
INSERT IGNORE INTO `users` (`name`, `email`, `password_hash`, `role`, `is_active`)
VALUES (
  'Super Admin',
  'admin@g2group.com',
  '$2y$10$MhpuK3In1kMi1C9GDpv3C.K5KfmTjZuLHiCPMfANbvsz0uHbFs6YG',
  'superadmin',
  1
);
