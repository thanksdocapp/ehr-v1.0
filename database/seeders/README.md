# Patient Portal Test Data Seeders

This directory contains comprehensive test data seeders for the Patient Portal functionality. These seeders are designed for **development and testing purposes only** and will automatically run only in the `local` environment.

## Overview

The seeders create realistic test data for:

- **Test Patients** - 5 sample patients with complete profiles
- **Medical Records** - Patient consultation records with diagnoses, symptoms, treatments, and vital signs
- **Prescriptions** - Medication prescriptions with various statuses and expiry dates
- **Lab Reports** - Blood tests, urine tests, and imaging reports with realistic results
- **Invoices** - Billing information with line items, taxes, and discounts
- **Payments** - Payment records with various methods and gateway responses

## How to Use

### 1. Run All Seeders
```bash
php artisan db:seed
```

### 2. Run Individual Seeders
```bash
php artisan db:seed --class=TestPatientSeeder
php artisan db:seed --class=MedicalRecordSeeder
php artisan db:seed --class=PrescriptionSeeder
php artisan db:seed --class=LabReportSeeder
php artisan db:seed --class=InvoiceSeeder
php artisan db:seed --class=PaymentSeeder
```

### 3. Fresh Migration + Seeding
```bash
php artisan migrate:fresh --seed
```

## Test Data Created

### Test Patients
- **John Smith** - john.smith@example.com (Password: password123)
- **Sarah Johnson** - sarah.johnson@example.com (Password: password123)
- **Michael Brown** - michael.brown@example.com (Password: password123)
- **Emily Davis** - emily.davis@example.com (Password: password123)
- **Robert Wilson** - robert.wilson@example.com (Password: password123)

### Data Volumes (per patient)
- **Medical Records**: 2-6 records with realistic diagnoses and vital signs
- **Prescriptions**: 1-4 prescriptions with various statuses
- **Lab Reports**: 1-3 reports including blood tests, urine tests, and imaging
- **Invoices**: 1-3 invoices with multiple line items
- **Payments**: Linked to invoices with realistic payment methods and statuses

## Environment Safety

⚠️ **Important**: These seeders will **ONLY** run in the `local` environment to prevent accidental seeding in production.

## Removing Test Data

When you're ready to remove the test data:

```bash
# Complete database reset
php artisan migrate:fresh

# Or reset and re-seed basic data only
php artisan migrate:fresh --seed --force
```

## Data Relationships

The seeders maintain proper relationships between all models:
- Medical records link to patients, doctors, and appointments
- Prescriptions reference medical records and appointments
- Lab reports connect to appointments and medical records
- Invoices tie to patients and appointments
- Payments are linked to specific invoices

## Realistic Test Data

All data is designed to be as realistic as possible:
- **Medical terminology** - Proper diagnoses, symptoms, and treatments
- **Lab values** - Within normal ranges with reference values
- **Medication names** - Real medication names with proper dosages
- **Financial data** - Realistic pricing for medical services
- **Payment methods** - Various payment types with gateway responses

## Development Benefits

This test data allows you to:
- ✅ Test the complete patient portal functionality
- ✅ Demonstrate features to stakeholders
- ✅ Verify data relationships and constraints
- ✅ Test search, filtering, and pagination
- ✅ Validate the user interface with real data
- ✅ Test PDF generation and exports
- ✅ Verify billing and payment workflows

## Production Deployment

Before deploying to production:
1. Set `APP_ENV=production` in your `.env` file
2. Run `php artisan migrate:fresh` to remove all test data
3. Seed only the essential data for your live environment

The test data seeders will automatically be skipped in production environments.
