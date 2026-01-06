# ThankDoc EHR - Installation Files

## Directory Contents

This directory contains the necessary files for the automatic database installation process.

### Files:

- **`database.mysql.sql`** - MySQL database structure and default data
  - Contains all hospital management tables with proper relationships
  - Includes default departments (Emergency, Internal Medicine, Cardiology, etc.)
  - Includes default services and system settings
  - Ready for production deployment on shared hosting

### How It Works:

1. During installation, the system automatically reads `database.mysql.sql`
2. The InstallController imports the database structure and data
3. Tables are created with proper foreign key relationships
4. Default hospital departments and services are pre-populated
5. System settings are configured with ThankDoc EHR branding

### Database Features:

- **Multi-user system** with role-based access (Admin, Doctor, Nurse, etc.)
- **Complete patient management** with medical records and history
- **Appointment scheduling** with doctor availability management
- **Prescription and lab report** management
- **Comprehensive billing** and payment processing
- **Department and service** management
- **Medical record keeping** with GDPR compliance considerations

### Technical Details:

- **Engine**: InnoDB (supports transactions and foreign keys)
- **Charset**: utf8mb4_unicode_ci (full Unicode support)
- **Compatible**: MySQL 5.7+ and MariaDB 10.3+
- **Optimized**: For shared hosting environments

### Security:

- All tables use proper foreign key constraints
- Sensitive data fields are properly encrypted
- Default admin user requires setup during installation
- System follows medical data security best practices

---
**ThankDoc EHR v1.0.0**  
Professional Healthcare Management Solution
