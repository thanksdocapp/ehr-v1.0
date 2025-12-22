# 🔐 Quick Start: Data Encryption

## ⚡ For New Deployments

### Step 1: Generate Encryption Key
```bash
php artisan key:generate
```

### Step 2: Configure HTTPS (Production Only)
```env
# .env
APP_ENV=production
APP_URL=https://your-domain.com
SESSION_SECURE_COOKIE=true
```

### Step 3: Done!
New data will be automatically encrypted. No migration needed.

---

## 🔄 For Existing Systems (Upgrading)

### ⚠️ CRITICAL: Backup First!
```bash
# Create database backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Or use Laravel backup
php artisan backup:run
```

### Step 1: Verify APP_KEY
```bash
# Check your .env file has APP_KEY
cat .env | grep APP_KEY

# If missing, generate one:
php artisan key:generate
```

### Step 2: Encrypt Existing Data
```bash
# This is SAFE - it uses database transactions and can be rolled back
php artisan data:encrypt-existing --model=all
```

**What this does:**
- Reads plaintext data
- Encrypts with AES-256-CBC
- Updates database records
- Skips already-encrypted data
- Uses batches (memory efficient)

**Time estimate**: ~1-5 minutes for 10,000 records

### Step 3: Verify Encryption
```bash
php artisan tinker
```
```php
// Test a patient record
$patient = \App\Models\Patient::first();
echo "Insurance Number (decrypted): " . $patient->insurance_number . "\n";

// Check database (should be encrypted)
DB::table('patients')->where('id', $patient->id)->value('insurance_number');
// Should return: eyJpdiI6I... (encrypted)
```

### Step 4: Configure Production Settings
```env
# .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Enable MySQL SSL (optional but recommended)
MYSQL_ATTR_SSL_CA=/path/to/ca-cert.pem
```

---

## 🧪 Testing Encryption

### Test Read/Write
```php
// In tinker: php artisan tinker

// Write encrypted data
$patient = \App\Models\Patient::find(1);
$patient->insurance_number = 'TEST-123-456';
$patient->save();

// Read encrypted data (automatically decrypted)
echo $patient->insurance_number;  // Output: TEST-123-456

// Check raw database value (should be encrypted)
DB::table('patients')->where('id', 1)->value('insurance_number');
// Output: eyJpdiI6IkJhc2U2... (encrypted string)
```

---

## 🚨 Troubleshooting

### "The MAC is invalid" Error

**Cause**: APP_KEY has changed since data was encrypted

**Fix**:
```bash
# Option 1: Restore original APP_KEY from backup
# Copy old APP_KEY back to .env

# Option 2: Re-encrypt with new key
php artisan data:encrypt-existing --model=all --force

# Option 3: For payment gateways only
# Re-enter credentials in admin panel at /admin/payment-gateways
```

### Data Appears Encrypted in Application

**Cause**: Model cast not configured

**Fix**: Check model has encryption cast:
```php
protected $casts = [
    'field_name' => 'encrypted',
];
```

### Performance Issues

**Solution**: Only select fields you need
```php
// Good (fast)
Patient::select('id', 'first_name', 'email')->get();

// Bad (slow - decrypts all encrypted fields)
Patient::all();
```

---

## 📋 What Gets Encrypted?

### ✅ Automatically Encrypted:
- **Patient**: Insurance number, emergency contacts, notes
- **Medical Records**: All clinical data (12 fields)
- **Appointments**: Reason, symptoms, notes, diagnosis, etc.
- **Prescriptions**: Diagnosis, notes
- **Email Logs**: Body content (PHI)
- **Payment Gateways**: API credentials

### ❌ Not Encrypted (By Design):
- **Patient Names**: Needed for search/display
- **Email Addresses**: Needed for auth/communication
- **Phone Numbers** (main): Needed for contact
- **Dates**: Needed for filtering/sorting
- **IDs**: Needed for relationships
- **Status Fields**: Needed for queries

---

## 🎯 Commands Reference

```bash
# Encrypt all existing data
php artisan data:encrypt-existing --model=all

# Encrypt specific model only
php artisan data:encrypt-existing --model=patient
php artisan data:encrypt-existing --model=medical-record
php artisan data:encrypt-existing --model=appointment

# Custom batch size (default 100)
php artisan data:encrypt-existing --model=all --batch=50

# Skip confirmation (for scripts)
php artisan data:encrypt-existing --model=all --force

# Check command help
php artisan data:encrypt-existing --help
```

---

## 📞 Support

For detailed information, see:
- **Full Guide**: `ENCRYPTION-SECURITY.md`
- **Deployment Checklist**: `DEPLOYMENT-SECURITY-CHECKLIST.md`
- **Environment Config**: `ENCRYPTION-ENV-EXAMPLE.txt`

---

**Security Hotline**: Report security issues immediately to security@your-domain.com

