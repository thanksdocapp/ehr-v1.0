-- Booking / clinic discount codes — run in phpMyAdmin against your app database.
--
-- Run top to bottom. If you see "Duplicate column" / "already exists", that part
-- was already applied (e.g. by php artisan migrate) — skip those lines.
--
-- Requires existing tables: doctors, departments, booking_services, invoices.

-- ---------------------------------------------------------------------------
-- 1) Doctor booking discount codes + invoice columns
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `doctor_booking_discount_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `doctor_id` bigint unsigned NOT NULL,
  `code` varchar(64) NOT NULL,
  `discount_type` enum('percent','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `booking_service_id` bigint unsigned DEFAULT NULL,
  `max_uses` int unsigned DEFAULT NULL,
  `uses_count` int unsigned NOT NULL DEFAULT '0',
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doctor_booking_discount_codes_doctor_id_code_unique` (`doctor_id`,`code`),
  KEY `doctor_booking_discount_codes_booking_service_id_foreign` (`booking_service_id`),
  CONSTRAINT `doctor_booking_discount_codes_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `doctor_booking_discount_codes_booking_service_id_foreign` FOREIGN KEY (`booking_service_id`) REFERENCES `booking_services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `invoices`
  ADD COLUMN `doctor_booking_discount_code_id` bigint unsigned DEFAULT NULL;

ALTER TABLE `invoices`
  ADD COLUMN `discount_code_redemption_recorded_at` timestamp NULL DEFAULT NULL;

ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_doctor_booking_discount_code_id_foreign` FOREIGN KEY (`doctor_booking_discount_code_id`) REFERENCES `doctor_booking_discount_codes` (`id`) ON DELETE SET NULL;

-- ---------------------------------------------------------------------------
-- 2) Clinic booking discount codes + invoice column
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `clinic_booking_discount_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint unsigned NOT NULL,
  `code` varchar(64) NOT NULL,
  `discount_type` enum('percent','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `booking_service_id` bigint unsigned DEFAULT NULL,
  `max_uses` int unsigned DEFAULT NULL,
  `uses_count` int unsigned NOT NULL DEFAULT '0',
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clinic_booking_discount_codes_department_id_code_unique` (`department_id`,`code`),
  KEY `clinic_booking_discount_codes_booking_service_id_foreign` (`booking_service_id`),
  CONSTRAINT `clinic_booking_discount_codes_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clinic_booking_discount_codes_booking_service_id_foreign` FOREIGN KEY (`booking_service_id`) REFERENCES `booking_services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `invoices`
  ADD COLUMN `clinic_booking_discount_code_id` bigint unsigned DEFAULT NULL;

ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_clinic_booking_discount_code_id_foreign` FOREIGN KEY (`clinic_booking_discount_code_id`) REFERENCES `clinic_booking_discount_codes` (`id`) ON DELETE SET NULL;
