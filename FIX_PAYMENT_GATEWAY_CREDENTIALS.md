# Fix Payment Gateway Credentials Constraint Error

## Problem
You're getting this error when creating a payment gateway:
```
SQLSTATE[23000]: Integrity constraint violation: 4025 CONSTRAINT `payment_gateways.credentials` failed
```

## Root Cause
The `credentials` column is defined as `JSON` type, but the system stores **encrypted strings** (not JSON). MySQL's JSON column type has a CHECK constraint that validates JSON format, which fails for encrypted strings.

## Solution

### Option 1: Run Migrations (Recommended)
```bash
php artisan migrate
```

This will run both migrations:
- `2025_11_27_045018_change_credentials_column_to_text_in_payment_gateways_table.php`
- `2025_11_27_045151_remove_credentials_constraint_from_payment_gateways_table.php`

### Option 2: Manual SQL Fix (If migrations don't work)

Run this SQL directly on your production database:

```sql
-- Step 1: Drop CHECK constraints
ALTER TABLE `payment_gateways` DROP CHECK IF EXISTS `payment_gateways_credentials_check`;

-- Step 2: Change column type
ALTER TABLE `payment_gateways` MODIFY COLUMN `credentials` TEXT NOT NULL;
```

Or use the comprehensive script in `database/migrations/SQL/fix_payment_gateways_credentials_manual.sql`

### Option 3: Quick Fix via phpMyAdmin or MySQL Client

1. Connect to your database
2. Run:
   ```sql
   ALTER TABLE `payment_gateways` MODIFY COLUMN `credentials` TEXT NOT NULL;
   ```

## Verification

After running the fix, verify with:
```sql
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'payment_gateways' 
AND COLUMN_NAME = 'credentials';
```

Expected result: `DATA_TYPE` should be `text` or `longtext` (NOT `json`)

## Important Notes

- **Backup your database first** before running any SQL changes
- The migration is safe to run - it only changes the column type
- Existing encrypted credentials will remain intact
- After the fix, you'll be able to create payment gateways successfully

## Still Having Issues?

If the error persists after running the fix:
1. Check if there are multiple CHECK constraints
2. Verify the column type actually changed
3. Check MySQL error logs for more details
4. Ensure you have proper database permissions

