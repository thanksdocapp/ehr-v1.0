-- =============================================================================
-- Patient shared contacts + password reset tokens (MySQL / MariaDB / phpMyAdmin)
-- Mirrors: database/migrations/2026_05_10_120000_patient_shared_contacts_and_reset_tokens.php
--
-- Run in order. If a step errors because the object already exists, skip that step.
-- If DROP INDEX fails, run: SHOW INDEX FROM `patients` WHERE Column_name = 'email';
-- and replace `patients_email_unique` below with the Key_name shown for the UNIQUE index.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1) Lookup table (must exist before FK on patients.contact_group_id)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `patient_contact_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed rows (idempotent per label)
INSERT INTO `patient_contact_groups` (`label`, `description`, `created_at`, `updated_at`)
SELECT 'Household / family', 'Shared email or phone for family members', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `patient_contact_groups` WHERE `label` = 'Household / family'
);

INSERT INTO `patient_contact_groups` (`label`, `description`, `created_at`, `updated_at`)
SELECT 'Management / agency', 'One contact manages multiple patient records (e.g. casting)', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `patient_contact_groups` WHERE `label` = 'Management / agency'
);

-- -----------------------------------------------------------------------------
-- 2) patients: allow duplicate emails (drop UNIQUE), add normal index, new column
-- -----------------------------------------------------------------------------
-- Laravel default name for unique on patients.email — change if SHOW INDEX differs:
ALTER TABLE `patients` DROP INDEX `patients_email_unique`;

ALTER TABLE `patients` ADD INDEX `patients_email_index` (`email`);

-- Skip this block if `contact_group_id` already exists (check Structure tab first).
ALTER TABLE `patients`
  ADD COLUMN `contact_group_id` bigint unsigned NULL DEFAULT NULL AFTER `department_id`;

ALTER TABLE `patients`
  ADD CONSTRAINT `patients_contact_group_id_foreign`
    FOREIGN KEY (`contact_group_id`) REFERENCES `patient_contact_groups` (`id`)
    ON DELETE SET NULL;

-- -----------------------------------------------------------------------------
-- 3) Per-patient password reset tokens (duplicate-safe login email)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `patient_password_reset_tokens` (
  `patient_id` bigint unsigned NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`patient_id`),
  CONSTRAINT `patient_password_reset_tokens_patient_id_foreign`
    FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Rollback (optional — only if you need to undo manually; may fail if duplicates)
-- =============================================================================
-- SET FOREIGN_KEY_CHECKS = 0;
-- DROP TABLE IF EXISTS `patient_password_reset_tokens`;
-- ALTER TABLE `patients` DROP FOREIGN KEY `patients_contact_group_id_foreign`;
-- ALTER TABLE `patients` DROP COLUMN `contact_group_id`;
-- DROP TABLE IF EXISTS `patient_contact_groups`;
-- ALTER TABLE `patients` DROP INDEX `patients_email_index`;
-- ALTER TABLE `patients` ADD UNIQUE KEY `patients_email_unique` (`email`);
-- SET FOREIGN_KEY_CHECKS = 1;
