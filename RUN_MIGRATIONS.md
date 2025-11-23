# Running Database Migrations

The email feature requires new database tables. Please run the following migrations:

## Migration Files Created:
1. `2025_01_15_000001_create_patient_email_consent_table.php`
2. `2025_01_15_000002_add_medical_record_email_fields_to_email_logs_table.php`
3. `2025_01_15_000003_create_email_bounces_table.php`

## To Run Migrations:

### Option 1: Using Artisan (Recommended)
```bash
php artisan migrate
```

### Option 2: Run Specific Migrations
```bash
php artisan migrate --path=database/migrations/2025_01_15_000001_create_patient_email_consent_table.php
php artisan migrate --path=database/migrations/2025_01_15_000002_add_medical_record_email_fields_to_email_logs_table.php
php artisan migrate --path=database/migrations/2025_01_15_000003_create_email_bounces_table.php
```

### Option 3: If you have database access, run the SQL directly
You can extract the SQL from the migration files and run them manually in your database.

## After Running Migrations:

1. The `patient_email_consent` table will be created
2. The `email_logs` table will be extended with new fields
3. The `email_bounces` table will be created

## Note:
The application will work without these tables (with warnings in logs), but consent checking and email logging features will not function properly until migrations are run.

