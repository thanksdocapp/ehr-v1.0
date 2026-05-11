-- Optional manual migration (MySQL/MariaDB) — mirrors:
-- database/migrations/2026_05_11_150000_add_exclude_from_consultation_report_to_appointments_table.php
--
-- Skip if column already exists.

ALTER TABLE `appointments`
  ADD COLUMN `exclude_from_consultation_report` TINYINT(1) NOT NULL DEFAULT 0 AFTER `created_from`;
