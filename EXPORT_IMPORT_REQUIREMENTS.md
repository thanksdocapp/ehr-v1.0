# Export & Import Requirements for Patients and Doctors

## 📋 Overview

This document outlines all possible export and import options for **Patients** and **Doctors** in the EHR system.

---

## 👥 PATIENTS - Export Options

### 1. **CSV Export** ✅
**Format:** Comma-Separated Values  
**Use Cases:**
- Data backup
- Import into other systems
- Spreadsheet analysis
- Reporting tools

**Fields to Include:**
- Basic Information:
  - Patient ID
  - First Name
  - Last Name
  - Full Name
  - Email
  - Phone
  - Date of Birth
  - Age
  - Gender
  - Blood Group
  
- Contact Information:
  - Address
  - City
  - State
  - Country
  - Postal Code
  
- Emergency Contact:
  - Emergency Contact Name
  - Emergency Contact Phone
  
- Medical Information:
  - Allergies (comma-separated)
  - Medical Conditions (comma-separated)
  - Insurance Provider
  - Insurance Number
  
- Assignment Information:
  - Assigned Clinics (comma-separated)
  - Created By Doctor ID
  - Created By Doctor Name
  
- Status Information:
  - Status (Active/Inactive)
  - Registration Date
  - Last Updated Date
  - Email Verified Status

**Filter Options:**
- By Clinic (Department)
- By Status (Active/Inactive)
- By Date Range (Registration Date)
- By Gender
- By Blood Group
- By Created Doctor
- Search by Name/Email/Patient ID

---

### 2. **Excel Export** 📊
**Format:** Microsoft Excel (.xlsx)  
**Use Cases:**
- Advanced data analysis
- Formatted reports
- Multi-sheet workbooks
- Charts and graphs

**Features:**
- **Sheet 1: Patient List** (Main data)
- **Sheet 2: Statistics** (Summary stats)
- **Sheet 3: Clinics Breakdown** (Patients per clinic)
- **Sheet 4: Registration Trends** (Monthly registrations)

**Additional Formatting:**
- Colored headers
- Conditional formatting (Active = Green, Inactive = Red)
- Auto-width columns
- Filters enabled
- Data validation for dropdown fields
- Charts (Age distribution, Gender distribution, Registration trends)

**Fields:** Same as CSV + Calculated fields:
- Age
- Days since registration
- Number of appointments
- Last appointment date
- Total medical records

---

### 3. **PDF Export** 📄
**Format:** Portable Document Format (.pdf)  
**Use Cases:**
- Official documentation
- Printing
- Archiving
- Sharing with external parties

**Export Types:**

#### A. **Patient List Report**
- Table format with all patients
- Pagination (multiple pages)
- Headers/Footers with hospital branding
- Date and time of export
- Page numbers

#### B. **Individual Patient Details**
- Full patient profile
- Formatted with sections:
  - Personal Information
  - Contact Details
  - Emergency Contact
  - Medical Information
  - Clinic Assignments
  - Registration History
- QR code for patient ID

#### C. **Summary Report**
- Statistics dashboard
- Charts and graphs
- Breakdown by clinic
- Registration trends

---

### 4. **JSON Export** 📦
**Format:** JavaScript Object Notation (.json)  
**Use Cases:**
- System integration
- Data migration
- Backup and restore
- API consumption

**Structure:**
```json
{
  "export_info": {
    "exported_at": "2025-11-16T10:00:00Z",
    "total_records": 150,
    "version": "1.0"
  },
  "patients": [
    {
      "patient_id": "PAT001",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@example.com",
      ...
      "departments": [
        {"id": 1, "name": "Cardiology", "is_primary": true}
      ],
      "relationships": {
        "appointments": 5,
        "medical_records": 12
      }
    }
  ]
}
```

---

### 5. **XML Export** 🔷
**Format:** eXtensible Markup Language (.xml)  
**Use Cases:**
- Legacy system integration
- HL7 FHIR compatibility
- Medical record exchange standards

**Features:**
- XML schema validation
- HL7 FHIR format support
- Medical data standards compliance

---

## 👥 PATIENTS - Import Options

### 1. **CSV Import** ✅
**Format:** Comma-Separated Values  
**Use Cases:**
- Bulk patient registration
- Data migration from other systems
- Batch updates

**Required Fields:**
- Patient ID (or generate automatically)
- First Name
- Last Name
- Email (unique)
- Phone
- Date of Birth
- Gender

**Optional Fields:**
- All other patient fields
- Clinic assignments (comma-separated clinic IDs or names)

**Import Modes:**
- **Insert Only:** Only create new patients
- **Update Existing:** Update if patient ID/email exists
- **Upsert:** Create if not exists, update if exists
- **Skip Duplicates:** Skip if patient ID/email already exists

**Validation:**
- Email format validation
- Date format validation
- Required field checks
- Duplicate detection
- Clinic name/ID validation

**Error Handling:**
- Show preview before import
- List validation errors
- Allow corrections
- Skip invalid rows or stop import
- Import summary report

---

### 2. **Excel Import** 📊
**Format:** Microsoft Excel (.xlsx, .xls)  
**Use Cases:**
- Import from Excel templates
- Multiple sheets support
- Advanced data mapping

**Features:**
- Template download (pre-filled with column headers)
- Sheet selection (import from specific sheet)
- Column mapping (map Excel columns to database fields)
- Data type detection
- Preview before import
- Batch validation

**Template Features:**
- Dropdown lists for:
  - Gender
  - Blood Group
  - Status
  - Clinics
- Data validation
- Example rows
- Instructions sheet

---

### 3. **JSON Import** 📦
**Format:** JavaScript Object Notation (.json)  
**Use Cases:**
- System-to-system data transfer
- Backup restoration
- API-driven imports

**Structure:** Same as JSON Export format

**Validation:**
- Schema validation
- Required fields check
- Data type validation
- Relationship validation (clinics, doctors)

---

### 4. **XML Import** 🔷
**Format:** eXtensible Markup Language (.xml)  
**Use Cases:**
- HL7 FHIR import
- Legacy system integration
- Medical record exchange

**Features:**
- XML schema validation
- HL7 FHIR parser
- Medical data standards compliance

---

### 5. **Bulk Registration Form** 📝
**Format:** Web-based form  
**Use Cases:**
- Manual bulk entry
- Quick registration
- Guided import

**Features:**
- Multiple patient entry form
- Add/remove rows dynamically
- Real-time validation
- Save as draft
- Preview before submit

---

## 👨‍⚕️ DOCTORS - Export Options

### 1. **CSV Export** ✅
**Format:** Comma-Separated Values  
**Use Cases:**
- Data backup
- Import into other systems
- Spreadsheet analysis
- Reporting tools

**Fields to Include:**
- Basic Information:
  - Doctor ID (User ID)
  - Title (Dr., Prof., etc.)
  - First Name
  - Last Name
  - Full Name
  - Email
  - Phone
  - Employee ID
  
- Professional Information:
  - Specialization
  - Specialties (comma-separated)
  - Qualification
  - License Number
  - Experience Years
  - Languages (comma-separated)
  
- Department Information:
  - Primary Clinic
  - Additional Clinics (comma-separated)
  - Room Number
  
- Contact & Availability:
  - Email
  - Phone
  - Online Availability (Yes/No)
  - Working Hours (JSON or formatted string)
  
- Financial Information:
  - Consultation Fee
  
- Profile Information:
  - Bio
  - Photo URL (if available)
  
- Status Information:
  - Status (Active/Inactive)
  - Is Featured
  - Is Available Online
  - Registration Date
  - Last Updated Date

**Filter Options:**
- By Clinic (Department)
- By Specialization
- By Status (Active/Inactive)
- By Availability
- By Date Range (Registration Date)
- Search by Name/Email/Employee ID

---

### 2. **Excel Export** 📊
**Format:** Microsoft Excel (.xlsx)  
**Use Cases:**
- Advanced data analysis
- Formatted reports
- Multi-sheet workbooks
- Charts and graphs

**Features:**
- **Sheet 1: Doctor List** (Main data)
- **Sheet 2: Statistics** (Summary stats)
- **Sheet 3: Specializations** (Doctors per specialization)
- **Sheet 4: Clinics Breakdown** (Doctors per clinic)
- **Sheet 5: Availability Schedule** (Working hours matrix)

**Additional Formatting:**
- Colored headers
- Conditional formatting (Active = Green, Inactive = Red)
- Auto-width columns
- Filters enabled
- Data validation for dropdown fields
- Charts (Specialization distribution, Experience distribution, Clinic distribution)

**Fields:** Same as CSV + Calculated fields:
- Number of appointments
- Number of patients
- Average consultation rating (if available)
- Total consultations
- Monthly consultation count

---

### 3. **PDF Export** 📄
**Format:** Portable Document Format (.pdf)  
**Use Cases:**
- Official documentation
- Directory printing
- Archiving
- Sharing with external parties

**Export Types:**

#### A. **Doctor Directory**
- List of all doctors with photos
- Searchable and printable
- Organized by clinic/specialization
- Contact information

#### B. **Individual Doctor Profile**
- Full doctor profile
- Formatted with sections:
  - Personal Information
  - Professional Details
  - Clinic Assignments
  - Qualifications & Experience
  - Availability Schedule
  - Consultation Fee
- QR code for doctor profile link

#### C. **Summary Report**
- Statistics dashboard
- Charts and graphs
- Breakdown by specialization
- Breakdown by clinic
- Availability overview

---

### 4. **JSON Export** 📦
**Format:** JavaScript Object Notation (.json)  
**Use Cases:**
- System integration
- Data migration
- Backup and restore
- API consumption

**Structure:**
```json
{
  "export_info": {
    "exported_at": "2025-11-16T10:00:00Z",
    "total_records": 50,
    "version": "1.0"
  },
  "doctors": [
    {
      "user_id": 1,
      "title": "Dr.",
      "first_name": "John",
      "last_name": "Smith",
      "email": "john.smith@hospital.com",
      "specialization": "Cardiology",
      "departments": [
        {"id": 1, "name": "Cardiology", "is_primary": true},
        {"id": 2, "name": "Emergency Medicine", "is_primary": false}
      ],
      "qualification": "MBBS, MD",
      "experience_years": 10,
      "consultation_fee": 150.00,
      "availability": {
        "monday": "09:00-17:00",
        "tuesday": "09:00-17:00"
      },
      "relationships": {
        "appointments": 250,
        "patients": 150
      }
    }
  ]
}
```

---

### 5. **XML Export** 🔷
**Format:** eXtensible Markup Language (.xml)  
**Use Cases:**
- Legacy system integration
- HL7 FHIR compatibility
- Medical directory standards

**Features:**
- XML schema validation
- HL7 FHIR format support
- Medical directory standards compliance

---

### 6. **Doctor Directory HTML** 🌐
**Format:** HTML/Website  
**Use Cases:**
- Public doctor directory
- Website integration
- Online profile pages

**Features:**
- Responsive design
- Search functionality
- Filter by specialization/clinic
- Doctor profile cards
- Contact information
- Online booking links

---

## 👨‍⚕️ DOCTORS - Import Options

### 1. **CSV Import** ✅
**Format:** Comma-Separated Values  
**Use Cases:**
- Bulk doctor registration
- Data migration from other systems
- Batch updates

**Required Fields:**
- Email (unique)
- First Name
- Last Name
- Specialization
- Primary Clinic (Department)

**Optional Fields:**
- Title
- Phone
- Employee ID
- Qualification
- License Number
- Experience Years
- Consultation Fee
- Bio
- Additional Clinics (comma-separated)

**Import Modes:**
- **Insert Only:** Only create new doctors (and user accounts)
- **Update Existing:** Update if doctor email exists
- **Upsert:** Create if not exists, update if exists
- **Skip Duplicates:** Skip if email already exists

**Validation:**
- Email format validation
- Unique email check
- Clinic name/ID validation
- User account creation (if needed)
- License number format (if applicable)

**Error Handling:**
- Show preview before import
- List validation errors
- Allow corrections
- Skip invalid rows or stop import
- Import summary report
- User account creation summary

---

### 2. **Excel Import** 📊
**Format:** Microsoft Excel (.xlsx, .xls)  
**Use Cases:**
- Import from Excel templates
- Multiple sheets support
- Advanced data mapping

**Features:**
- Template download (pre-filled with column headers)
- Sheet selection (import from specific sheet)
- Column mapping (map Excel columns to database fields)
- Data type detection
- Preview before import
- Batch validation

**Template Features:**
- Dropdown lists for:
  - Title
  - Specialization
  - Clinics
  - Status
- Data validation
- Example rows
- Instructions sheet
- Working hours template (separate columns for each day)

---

### 3. **JSON Import** 📦
**Format:** JavaScript Object Notation (.json)  
**Use Cases:**
- System-to-system data transfer
- Backup restoration
- API-driven imports

**Structure:** Same as JSON Export format

**Validation:**
- Schema validation
- Required fields check
- Data type validation
- Relationship validation (clinics, user accounts)

**Additional Features:**
- User account auto-creation
- Password generation (with notification)
- Department assignment handling

---

### 4. **XML Import** 🔷
**Format:** eXtensible Markup Language (.xml)  
**Use Cases:**
- HL7 FHIR import
- Legacy system integration
- Medical directory standards

**Features:**
- XML schema validation
- HL7 FHIR parser
- Medical directory standards compliance

---

### 5. **Bulk Registration Form** 📝
**Format:** Web-based form  
**Use Cases:**
- Manual bulk entry
- Quick registration
- Guided import

**Features:**
- Multiple doctor entry form
- Add/remove rows dynamically
- Real-time validation
- User account creation options
- Department/clinic assignment interface
- Save as draft
- Preview before submit

---

## 🔄 Import/Export Features (Both)

### Common Features:
1. **Scheduling:**
   - Scheduled exports (daily, weekly, monthly)
   - Automated backups
   - Email delivery of exports

2. **Filtering:**
   - Date range filters
   - Status filters
   - Department/Clinic filters
   - Custom field filters
   - Search functionality

3. **Validation:**
   - Data validation rules
   - Duplicate detection
   - Format checking
   - Relationship validation

4. **Error Handling:**
   - Error reporting
   - Validation summaries
   - Rollback on failure
   - Partial import support

5. **Logging:**
   - Import/Export history
   - Audit trails
   - User activity tracking
   - Error logs

6. **Templates:**
   - Downloadable templates
   - Example files
   - Format specifications
   - Documentation

7. **Progress Tracking:**
   - Progress bars for large imports/exports
   - Background job processing
   - Email notifications on completion
   - Queue management

---

## 📊 Recommended Priority Implementation

### Phase 1 (Essential): ⚡
1. ✅ **CSV Export** - Patients
2. ✅ **CSV Export** - Doctors
3. ✅ **CSV Import** - Patients
4. ✅ **CSV Import** - Doctors

### Phase 2 (Important): 📈
5. ✅ **Excel Export** - Patients
6. ✅ **Excel Export** - Doctors
7. ✅ **Excel Import** - Patients (with template)
8. ✅ **Excel Import** - Doctors (with template)

### Phase 3 (Advanced): 🚀
9. ✅ **PDF Export** - Patients (List & Individual)
10. ✅ **PDF Export** - Doctors (Directory & Profile)
11. ✅ **JSON Export/Import** - Both
12. ✅ **Bulk Registration Forms** - Both

### Phase 4 (Future): 🔮
13. ✅ **XML Export/Import** - Both
14. ✅ **HL7 FHIR Support** - Both
15. ✅ **Scheduled Exports** - Both
16. ✅ **API Endpoints** - Both

---

## 📝 Implementation Notes

1. **Library Recommendation:**
   - **maatwebsite/excel** - For Excel import/export
   - **barryvdh/laravel-dompdf** or **barryvdh/laravel-snappy** - For PDF generation
   - Native PHP for CSV
   - Laravel Collections for JSON

2. **File Storage:**
   - Store exports in `storage/app/exports/`
   - Temporary files cleanup (auto-delete after 7 days)
   - Consider cloud storage for large files

3. **Performance:**
   - Use Laravel Queues for large exports
   - Chunk processing for large imports
   - Stream responses for downloads
   - Database indexing for faster queries

4. **Security:**
   - Admin-only access
   - File validation
   - Sanitize imported data
   - Rate limiting
   - Audit logging

5. **User Experience:**
   - Progress indicators
   - Download links
   - Error messages
   - Success notifications
   - Preview functionality

---

## ✅ Summary

**Export Formats Available:**
- CSV ✅
- Excel 📊
- PDF 📄
- JSON 📦
- XML 🔷
- HTML 🌐 (Doctors only)

**Import Formats Available:**
- CSV ✅
- Excel 📊
- JSON 📦
- XML 🔷
- Bulk Form 📝

**Key Features:**
- Filtering & Search
- Validation & Error Handling
- Templates & Documentation
- Progress Tracking
- Audit Logging
- Scheduled Exports

---

**Last Updated:** 2025-11-16

