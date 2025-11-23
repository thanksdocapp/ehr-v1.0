# Environment Variables for SuperAdminSeeder

Add these variables to your `.env` file to customize admin credentials:

## Super Admin Variables

```env
# Super Admin Email (default: kelvin@newwaves.com)
SUPER_ADMIN_EMAIL=kelvin@newwaves.com

# Super Admin Password (default: NewWaves2024!)
# ⚠️ WARNING: Change this in production!
SUPER_ADMIN_PASSWORD=NewWaves2024!

# Super Admin Name (default: Kelvin NewWaves)
SUPER_ADMIN_NAME=Kelvin NewWaves
```

## Test Admin Variables

```env
# Test Admin Email (default: admin@hospital.com)
TEST_ADMIN_EMAIL=admin@hospital.com

# Test Admin Password (default: admin123)
# ⚠️ WARNING: Change this in production!
TEST_ADMIN_PASSWORD=admin123

# Test Admin Name (default: Hospital Admin)
TEST_ADMIN_NAME=Hospital Admin
```

## Usage

### Development
1. Add to `.env` file (optional - defaults will be used if not set)
2. Run `php artisan db:seed` or `php artisan db:seed --class=SuperAdminSeeder`

### Production
1. **REQUIRED**: Set strong passwords in `.env` file
2. **REQUIRED**: Use environment variables for all credentials
3. Run seeder (it will ask for confirmation in production)

## Security Notes

⚠️ **Important**:
- Always change default passwords in production
- Use strong passwords (min 12 characters, mixed case, numbers, symbols)
- Never commit `.env` file to version control
- Credentials are only shown in console for local/testing environments

## Example .env Configuration

```env
# For Development
SUPER_ADMIN_EMAIL=dev-admin@hospital.local
SUPER_ADMIN_PASSWORD=DevPassword123!
SUPER_ADMIN_NAME=Development Admin

TEST_ADMIN_EMAIL=test-admin@hospital.local
TEST_ADMIN_PASSWORD=TestPassword123!
TEST_ADMIN_NAME=Test Admin

# For Production
SUPER_ADMIN_EMAIL=admin@yourhospital.com
SUPER_ADMIN_PASSWORD=YourSecurePassword123!@#
SUPER_ADMIN_NAME=System Administrator

TEST_ADMIN_EMAIL=test@yourhospital.com
TEST_ADMIN_PASSWORD=AnotherSecurePassword456!@#
TEST_ADMIN_NAME=Test Administrator
```

## Running the Seeder

### Automatic (via DatabaseSeeder)
```bash
php artisan db:seed
```

### Manual
```bash
php artisan db:seed --class=SuperAdminSeeder
```

### Production (with confirmation)
```bash
# Will ask for confirmation before proceeding
php artisan db:seed --class=SuperAdminSeeder
```

---

**Remember**: Always use environment variables for credentials in production! 🔒

