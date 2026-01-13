-- Create medical_record_attachments table
-- This table stores file attachments for medical records

CREATE TABLE IF NOT EXISTS `medical_record_attachments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `medical_record_id` BIGINT UNSIGNED NOT NULL,
    `uploaded_by` BIGINT UNSIGNED NOT NULL,
    `file_name` VARCHAR(255) NOT NULL COMMENT 'Original file name',
    `file_path` VARCHAR(255) NOT NULL COMMENT 'Storage path',
    `file_type` VARCHAR(255) NOT NULL COMMENT 'MIME type',
    `file_extension` VARCHAR(255) NOT NULL COMMENT 'File extension',
    `file_size` BIGINT UNSIGNED NOT NULL COMMENT 'File size in bytes',
    `storage_disk` VARCHAR(255) NOT NULL DEFAULT 'private' COMMENT 'Storage disk (private/public)',
    `file_category` ENUM('photo', 'results', 'documents', 'other') NOT NULL DEFAULT 'documents',
    `description` TEXT NULL COMMENT 'Optional description',
    `is_private` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Access control',
    `virus_scan_status` VARCHAR(255) NOT NULL DEFAULT 'pending' COMMENT 'pending, clean, infected, error',
    `virus_scan_at` TIMESTAMP NULL DEFAULT NULL,
    `virus_scan_result` TEXT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Retention policy expiration',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `medical_record_attachments_medical_record_id_created_at_index` (`medical_record_id`, `created_at`),
    INDEX `medical_record_attachments_uploaded_by_index` (`uploaded_by`),
    INDEX `medical_record_attachments_file_category_index` (`file_category`),
    INDEX `medical_record_attachments_virus_scan_status_index` (`virus_scan_status`),
    INDEX `medical_record_attachments_expires_at_index` (`expires_at`),
    CONSTRAINT `medical_record_attachments_medical_record_id_foreign` 
        FOREIGN KEY (`medical_record_id`) 
        REFERENCES `medical_records` (`id`) 
        ON DELETE CASCADE,
    CONSTRAINT `medical_record_attachments_uploaded_by_foreign` 
        FOREIGN KEY (`uploaded_by`) 
        REFERENCES `users` (`id`) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
