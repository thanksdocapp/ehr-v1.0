-- Fix file_category ENUM in medical_record_attachments table
-- Update from old values to new values: ('photo', 'results', 'documents', 'other')

-- Step 1: Update any existing data to map old values to new values
UPDATE `medical_record_attachments` 
SET `file_category` = 'documents' 
WHERE `file_category` IN ('notes', 'pre_consult', 'reference');

-- Step 2: Modify the ENUM column to use the new values
ALTER TABLE `medical_record_attachments` 
MODIFY COLUMN `file_category` ENUM('photo', 'results', 'documents', 'other') NOT NULL DEFAULT 'documents';
