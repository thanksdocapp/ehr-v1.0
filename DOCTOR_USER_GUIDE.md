# Doctor User Guide - ThanksDoc EHR System

## Table of Contents
1. [Main Menu Navigation](#main-menu-navigation)
2. [Dashboard](#dashboard)
3. [Patients](#patients)
4. [Appointments](#appointments)
5. [Medical Records](#medical-records)
6. [Billing](#billing)
7. [Patient Alerts](#patient-alerts)

---

## Main Menu Navigation

### Overview
The main menu sidebar provides quick access to all major features of the EHR system. The sidebar is always visible on the left side of the screen and includes the following sections:

### Menu Items

1. **Dashboard** (`/staff/dashboard`)
   - Icon: `fas fa-home`
   - Access your main dashboard with statistics and quick actions

2. **Patients** (`/staff/patients`)
   - Icon: `fas fa-user-injured`
   - Manage patient records, view details, create new patients

3. **Appointments** (`/staff/appointments`)
   - Icon: `fas fa-calendar-alt`
   - View and manage appointments, calendar view, create new appointments

4. **Medical Records** (`/staff/medical-records`)
   - Icon: `fas fa-file-medical`
   - Create, view, and edit medical records

5. **Billing** (`/staff/billing`)
   - Icon: `fas fa-file-invoice-dollar`
   - Create bills, send invoices to patients, track payments

6. **Patient Alerts** (`/staff/alerts`)
   - Icon: `fas fa-exclamation-triangle`
   - View all patient alerts across the system

### Navigation Tips
- Click any menu item to navigate to that section
- The active menu item is highlighted
- Use the search functionality within each section to quickly find records
- The sidebar can be collapsed on smaller screens

---

## Dashboard

### Accessing the Dashboard
- URL: `/staff/dashboard` or `/staff/`
- Access from: Main menu → Dashboard icon

### Dashboard Features

#### 1. Statistics Overview
The dashboard displays key statistics filtered by your department(s):
- **Total Patients**: Number of patients visible to you (based on department/creation)
- **Total Appointments**: All appointments in your department(s)
- **Pending Appointments**: Appointments awaiting confirmation
- **Today's Appointments**: Appointments scheduled for today

#### 2. Today's Schedule Widget
- Displays all appointments scheduled for today
- Shows appointment time, patient name, and status
- Sorted by appointment time (earliest first)
- Click on an appointment to view details

#### 3. Recent Appointments
- Shows the 5 most recent appointments
- Displays patient name, doctor, date, and status
- Click to view full appointment details

#### 4. Quick Actions (if permissions allow)
Quick action buttons for common tasks:
- **New Patient**: Create a new patient record
- **Prescribe**: Create a new prescription
- **Medical Record**: Create a new medical record
- **Lab Report**: Order a new lab report

### Dashboard Actions

#### Viewing Statistics
1. Navigate to Dashboard from the main menu
2. Statistics are automatically displayed at the top
3. Statistics are filtered by your department(s) automatically

#### Viewing Today's Schedule
1. Scroll to the "Today's Schedule" section
2. Review all appointments for today
3. Click on any appointment to view details or edit

#### Accessing Quick Actions
1. Scroll to the "Quick Actions" section
2. Click on any quick action button
3. You'll be redirected to the create page for that resource

### API Endpoint
- **Get Stats**: `/staff/api/stats` (returns JSON statistics)

---

## Patients

### Accessing Patients
- URL: `/staff/patients`
- Access from: Main menu → Patients icon

### Patient Management Features

#### 1. View All Patients
- **URL**: `/staff/patients`
- **Features**:
  - List of all patients visible to you (filtered by department/creation)
  - Search functionality (name, email, phone, patient ID, insurance number)
  - Filter by status, department, date created
  - Sort by various columns
  - Pagination for large lists

#### 2. Create New Patient
- **URL**: `/staff/patients/create`
- **Steps**:
  1. Click "Create New Patient" button or use quick action
  2. Fill in required fields:
     - Personal Information (First Name, Last Name, Date of Birth, Gender, etc.)
     - Contact Information (Email, Phone, Address)
     - Emergency Contact
     - Insurance Information
     - GP Consent & Details (if applicable)
  3. Select department for the patient
  4. Upload patient photo (optional)
  5. Click "Create Patient" to save

**Important Notes**:
- Patients you create are automatically assigned to your primary department
- GP Consent checkbox: When checked, GP contact fields appear
- All required fields must be filled before submission

#### 3. View Patient Details
- **URL**: `/staff/patients/{patient_id}`
- **Steps**:
  1. Navigate to Patients list
  2. Click on a patient's name or "View" button
  3. View comprehensive patient information:
     - Personal Information
     - Contact Details
     - Medical History
     - Allergies
     - Appointments
     - Medical Records
     - Prescriptions
     - Lab Reports
     - Billing History
     - Patient Alerts
     - Documents

#### 4. Edit Patient Information
- **URL**: `/staff/patients/{patient_id}/edit`
- **Steps**:
  1. Navigate to patient details page
  2. Click "Edit Patient" button
  3. Modify any patient information
  4. Click "Update Patient" to save changes

**Note**: You can only edit patients that are visible to you (in your department or created by you)

#### 5. Contact Patient's GP
- **URL**: `/staff/patients/{patient_id}/gp-email`
- **Prerequisites**: Patient must have GP consent enabled and GP email address
- **Steps**:
  1. Navigate to patient details page
  2. Click "Contact GP" button (visible only if GP consent is enabled)
  3. Fill in the email form:
     - Subject
     - Message body
  4. Click "Send Email" to send the message to the patient's GP

#### 6. Download Patient Documents
- **URL**: `/staff/patients/{patient_id}/download-document/{type}`
- **Types**: ID, Insurance Card, Medical Certificate, etc.
- **Steps**:
  1. Navigate to patient details page
  2. Find the document section
  3. Click "Download" next to the document type

### Patient Search & Filters

#### Quick Search
- Use the search box at the top of the patients list
- Searches across: First Name, Last Name, Full Name, Email, Phone, Patient ID, Insurance Number
- Results update as you type

#### Advanced Filters
- **Status**: Active, Inactive, Archived
- **Department**: Filter by department
- **Date Created**: Filter by creation date range
- **Gender**: Male, Female, Other
- **Age Range**: Filter by patient age

### Patient Visibility Rules
- You can see:
  - Patients in your department(s)
  - Patients you created (regardless of department)
  - Patients in any department you belong to
- You cannot see:
  - Patients in departments you don't belong to (unless you created them)

---

## Appointments

### Accessing Appointments
- URL: `/staff/appointments`
- Access from: Main menu → Appointments icon

### Appointment Management Features

#### 1. View All Appointments
- **URL**: `/staff/appointments`
- **Features**:
  - List of all appointments (filtered to your appointments if you're a doctor)
  - Search functionality (appointment number, patient name, doctor name)
  - Multiple filters available
  - Calendar view option
  - Status indicators

#### 2. Calendar View
- **URL**: `/staff/appointments/calendar`
- **Steps**:
  1. Click "Calendar View" button
  2. View appointments in a calendar format
  3. Navigate between months
  4. Click on appointments to view details
  5. Different colors for different appointment statuses

#### 3. Create New Appointment
- **URL**: `/staff/appointments/create`
- **Steps**:
  1. Click "Create New Appointment" button
  2. Fill in appointment details:
     - **Patient**: Select from dropdown or search
     - **Doctor**: Select doctor (defaults to you if you're a doctor)
     - **Department**: Select department
     - **Appointment Date**: Select date
     - **Appointment Time**: Select time
     - **Type**: Consultation, Follow-up, Checkup, Emergency, etc.
     - **Status**: Pending, Confirmed, Completed, Cancelled
     - **Online Consultation**: Check if it's an online appointment
     - **Meeting Platform**: If online, select platform (Zoom, Teams, etc.)
     - **Meeting Link**: If online, enter meeting URL
     - **Notes**: Additional appointment notes
  3. Click "Create Appointment" to save

**Important Notes**:
- **Online Consultation Checkbox**: When checked, Meeting Platform and Meeting Link fields appear
- Meeting Platform and Meeting Link are displayed side-by-side
- Appointment time must be available (no conflicts)

#### 4. View Appointment Details
- **URL**: `/staff/appointments/{appointment_id}`
- **Steps**:
  1. Navigate to appointments list
  2. Click on an appointment or "View" button
  3. View complete appointment information:
     - Patient details
     - Doctor information
     - Date and time
     - Status and type
     - Online consultation details (if applicable)
     - Notes
     - Related medical records

#### 5. Edit Appointment
- **URL**: `/staff/appointments/{appointment_id}/edit`
- **Steps**:
  1. Navigate to appointment details
  2. Click "Edit Appointment" button
  3. Modify appointment details
  4. Click "Update Appointment" to save

**Note**: You can edit appointments you created or appointments assigned to you

#### 6. Confirm Appointment
- **URL**: `/staff/appointments/{appointment_id}/confirm`
- **Steps**:
  1. Navigate to appointment details
  2. Click "Confirm Appointment" button
  3. Appointment status changes to "Confirmed"
  4. Patient receives confirmation notification (if configured)

#### 7. Cancel Appointment
- **URL**: `/staff/appointments/{appointment_id}/cancel`
- **Steps**:
  1. Navigate to appointment details
  2. Click "Cancel Appointment" button
  3. Provide cancellation reason (optional)
  4. Appointment status changes to "Cancelled"
  5. Patient receives cancellation notification (if configured)

#### 8. Reschedule Appointment
- **URL**: `/staff/appointments/{appointment_id}/reschedule`
- **Steps**:
  1. Navigate to appointment details
  2. Click "Reschedule Appointment" button
  3. Select new date and time
  4. Provide reason for rescheduling (optional)
  5. Click "Reschedule" to save
  6. Patient receives rescheduling notification (if configured)

#### 9. Update Appointment Status
- **URL**: `/staff/appointments/{appointment_id}/update-status`
- **Method**: PATCH
- **Statuses**: Pending, Confirmed, Completed, Cancelled, No Show
- **Steps**:
  1. Navigate to appointment details
  2. Use status dropdown or status update button
  3. Select new status
  4. Status updates immediately

### Appointment Search & Filters

#### Quick Search
- Search by: Appointment Number, Patient Name, Patient Email, Patient Phone, Patient ID, Doctor Name

#### Advanced Filters
- **Patient**: Filter by specific patient
- **Doctor**: Filter by doctor
- **Department**: Filter by department
- **Status**: Pending, Confirmed, Completed, Cancelled, No Show
- **Type**: Consultation, Follow-up, Checkup, Emergency, etc.
- **Consultation Type**: Online, In-Person, Phone
- **Date Range**: Filter by appointment date range
- **Time Range**: Filter by appointment time range

### Appointment Visibility Rules
- **Doctors**: See only their own appointments
- **Other Staff**: See appointments in their department(s)

---

## Medical Records

### Accessing Medical Records
- URL: `/staff/medical-records`
- Access from: Main menu → Medical Records icon

### Medical Record Management Features

#### 1. View All Medical Records
- **URL**: `/staff/medical-records`
- **Features**:
  - List of all medical records visible to you (filtered by department)
  - Search functionality
  - Filter by patient, doctor, date, type
  - View attachments
  - Link to related appointments

#### 2. Create New Medical Record
- **URL**: `/staff/medical-records/create`
- **Steps**:
  1. Click "Create New Medical Record" button
  2. Fill in record details:
     - **Patient**: Select patient from dropdown
     - **Doctor**: Select doctor (defaults to you)
     - **Appointment**: Link to appointment (optional)
     - **Record Date**: Date of the medical record
     - **Type**: Consultation, Diagnosis, Treatment, Procedure, etc.
     - **Chief Complaint**: Patient's main complaint
     - **History of Present Illness**: Detailed history
     - **Physical Examination**: Examination findings
     - **Assessment**: Doctor's assessment
     - **Plan**: Treatment plan
     - **Diagnosis**: ICD-10 codes and descriptions
     - **Notes**: Additional notes
  3. Upload attachments (if any):
     - Click "Add Attachment"
     - Select file(s)
     - Add description for each attachment
  4. Click "Create Medical Record" to save

#### 3. Create Medical Record from Appointment
- **URL**: `/staff/medical-records/create-from-appointment/{appointment_id}`
- **Steps**:
  1. Navigate to an appointment
  2. Click "Create Medical Record" button
  3. Appointment details are pre-filled
  4. Complete the medical record form
  5. Click "Create Medical Record" to save

**Benefits**: Saves time by auto-filling patient and appointment information

#### 4. View Medical Record Details
- **URL**: `/staff/medical-records/{medical_record_id}`
- **Steps**:
  1. Navigate to medical records list
  2. Click on a record or "View" button
  3. View complete medical record:
     - Patient information
     - Doctor information
     - Record details
     - Attachments (view/download)
     - Related appointment (if linked)

#### 5. Edit Medical Record
- **URL**: `/staff/medical-records/{medical_record_id}/edit`
- **Steps**:
  1. Navigate to medical record details
  2. Click "Edit Medical Record" button
  3. Modify record information
  4. Add/remove attachments
  5. Click "Update Medical Record" to save

**Note**: You can edit records you created or records in your department

#### 6. Delete Medical Record
- **URL**: `/staff/medical-records/{medical_record_id}`
- **Method**: DELETE
- **Steps**:
  1. Navigate to medical record details
  2. Click "Delete Medical Record" button
  3. Confirm deletion
  4. Record is permanently deleted

**Note**: Only doctors can delete medical records they created

#### 7. View/Download Attachments
- **View**: `/staff/medical-record-attachments/{attachment_id}/view`
- **Download**: `/staff/medical-record-attachments/{attachment_id}/download`
- **Steps**:
  1. Navigate to medical record details
  2. Find the "Attachments" section
  3. Click "View" to open in browser or "Download" to save file

### Medical Record Search & Filters

#### Quick Search
- Search by: Patient Name, Doctor Name, Record Type, Diagnosis, Notes

#### Advanced Filters
- **Patient**: Filter by specific patient
- **Doctor**: Filter by doctor
- **Department**: Filter by department
- **Record Type**: Consultation, Diagnosis, Treatment, Procedure, etc.
- **Date Range**: Filter by record date range
- **Has Attachments**: Filter records with/without attachments

### Medical Record Visibility Rules
- You can see medical records from doctors in your department(s)
- You can create medical records for patients in your department(s)
- You can edit/delete medical records you created

---

## Billing

### Accessing Billing
- URL: `/staff/billing`
- Access from: Main menu → Billing icon

### Billing Management Features

#### 1. View All Bills
- **URL**: `/staff/billing`
- **Features**:
  - List of all bills in your department
  - Search functionality (bill number, patient name, description)
  - Filter by status, patient, doctor, date
  - View payment status
  - Quick actions (view, edit, send to patient)

#### 2. Create New Bill
- **URL**: `/staff/billing/create`
- **Steps**:
  1. Click "Create New Bill" button
  2. Fill in billing details:
     - **Patient**: Select patient from dropdown
     - **Doctor**: Select doctor (defaults to you)
     - **Appointment**: Link to appointment (optional)
     - **Department**: Select department
     - **Billing Date**: Date of the bill
     - **Due Date**: Payment due date
     - **Bill Type**: Consultation, Procedure, Medication, Lab Test, etc.
     - **Description**: Description of services
     - **Items**: Add billing items:
       - Item Name
       - Quantity
       - Unit Price
       - Total (auto-calculated)
     - **Subtotal**: Auto-calculated
     - **Tax Amount**: Enter tax (if applicable)
     - **Discount Amount**: Enter discount (if applicable)
     - **Total Amount**: Auto-calculated
     - **Payment Method**: Cash, Card, Bank Transfer, Online
     - **Payment Status**: Pending, Partially Paid, Paid, Overdue, Cancelled
     - **Paid Amount**: Amount already paid
     - **Notes**: Additional billing notes
  3. Click "Create Bill" to save

**Important Notes**:
- An invoice is automatically created when you create a bill
- Payment status automatically updates based on paid amount vs total amount
- Bills are filtered by your department

#### 3. View Bill Details
- **URL**: `/staff/billing/{billing_id}`
- **Steps**:
  1. Navigate to billing list
  2. Click on a bill or "View" button
  3. View complete bill information:
     - Patient information
     - Doctor information
     - Bill details
     - Invoice information
     - Payment history
     - Payment link (for patient)

#### 4. Edit Bill
- **URL**: `/staff/billing/{billing_id}/edit`
- **Steps**:
  1. Navigate to bill details
  2. Click "Edit Bill" button (available for Pending, Partially Paid, Overdue bills)
  3. Modify bill information
  4. Update items, amounts, dates
  5. Click "Update Bill" to save

**Note**: You can only edit bills with certain statuses (Pending, Partially Paid, Overdue)

#### 5. Send Bill to Patient
- **URL**: `/staff/billing/{billing_id}/send-to-patient`
- **Method**: POST
- **Steps**:
  1. Navigate to bill details
  2. Click "Send to Patient" button
  3. System validates patient email address
  4. Email is sent with:
     - Bill details
     - Invoice information
     - Public payment link (no login required)
     - Department/clinic name
     - Doctor name
  5. Confirmation message appears

**Email Features**:
- Mobile-optimized HTML email
- Includes doctor and department information
- Secure public payment link
- "Powered by ThanksDoc" branding

**Important**: 
- Patient must have a valid email address
- Payment link allows patients to pay without logging in
- Email includes all bill and invoice details

### Billing Search & Filters

#### Quick Search
- Search by: Bill Number, Patient Name, Description, Notes, Payment Reference

#### Advanced Filters
- **Patient**: Filter by specific patient
- **Doctor**: Filter by doctor
- **Department**: Filter by department
- **Status**: Pending, Partially Paid, Paid, Overdue, Cancelled
- **Bill Type**: Consultation, Procedure, Medication, Lab Test, etc.
- **Date Range**: Filter by billing date range
- **Payment Status**: Filter by payment status

### Billing Status Rules
- **Pending**: Bill created, no payment received
- **Partially Paid**: Some payment received, but not full amount
- **Paid**: Full amount paid
- **Overdue**: Due date passed, payment not received
- **Cancelled**: Bill cancelled

**Automatic Status Updates**:
- Status automatically changes to "Paid" when paid amount equals total amount
- Status automatically changes to "Partially Paid" when paid amount is greater than 0 but less than total
- Status automatically changes to "Overdue" when due date passes and status is still Pending or Partially Paid

### Billing Visibility Rules
- You can see bills in your department(s)
- You can create bills for patients in your department(s)
- Bills are filtered by your department automatically

---

## Patient Alerts

### Accessing Patient Alerts
- URL: `/staff/alerts` (All Alerts) or `/staff/patients/{patient_id}/alerts` (Patient-specific)
- Access from: Main menu → Patient Alerts icon

### Patient Alert Management Features

#### 1. View All Alerts
- **URL**: `/staff/alerts`
- **Features**:
  - List of all patient alerts in the system
  - Filter by severity, type, status
  - View alert details
  - Quick access to patient profile

#### 2. View Patient Alerts
- **URL**: `/staff/patients/{patient_id}/alerts`
- **Steps**:
  1. Navigate to patient details page
  2. Click "View Alerts" or navigate to Alerts tab
  3. View all alerts for that patient
  4. Filter by severity, type, active/inactive

#### 3. Create New Alert
- **URL**: `/staff/patients/{patient_id}/alerts/create`
- **Steps**:
  1. Navigate to patient details page
  2. Click "Create Alert" button
  3. Fill in alert details:
     - **Type**: Allergy, Medication, Medical Condition, Behavioral, Administrative, etc.
     - **Code**: Alert code (based on type)
     - **Severity**: Critical, High, Medium, Low, Info
     - **Title**: Alert title (optional, auto-generated if not provided)
     - **Description**: Detailed description (required)
     - **Active**: Check to make alert active
     - **Restricted**: Check if alert should be restricted to certain staff
     - **Expires At**: Optional expiration date
     - **Notes**: Additional notes
  4. Click "Create Alert" to save

**Alert Types**:
- **Allergy**: Patient allergies
- **Medication**: Medication-related alerts
- **Medical Condition**: Medical condition alerts
- **Behavioral**: Behavioral alerts
- **Administrative**: Administrative alerts

**Severity Levels**:
- **Critical**: Life-threatening, requires immediate attention
- **High**: Important, requires prompt attention
- **Medium**: Moderate importance
- **Low**: Minor importance
- **Info**: Informational only

#### 4. View Alert Details
- **URL**: `/staff/patients/{patient_id}/alerts/{alert_id}`
- **Steps**:
  1. Navigate to patient alerts list
  2. Click on an alert or "View" button
  3. View complete alert information:
     - Alert type and code
     - Severity
     - Description
     - Status (Active/Inactive)
     - Created by and date
     - Updated by and date
     - Expiration date (if set)
     - Notes

#### 5. Edit Alert
- **URL**: `/staff/patients/{patient_id}/alerts/{alert_id}/edit`
- **Steps**:
  1. Navigate to alert details
  2. Click "Edit Alert" button
  3. Modify alert information
  4. Update severity, description, status, expiration
  5. Click "Update Alert" to save

#### 6. Toggle Alert Active Status
- **URL**: `/staff/patients/{patient_id}/alerts/{alert_id}/toggle-active`
- **Method**: POST
- **Steps**:
  1. Navigate to alert details
  2. Click "Activate" or "Deactivate" button
  3. Alert status toggles immediately
  4. Confirmation message appears

**Note**: Inactive alerts or expired alerts are not shown in active alerts list

#### 7. Delete Alert
- **URL**: `/staff/patients/{patient_id}/alerts/{alert_id}`
- **Method**: DELETE
- **Steps**:
  1. Navigate to alert details
  2. Click "Delete Alert" button
  3. Confirm deletion
  4. Alert is permanently deleted

### Alert Search & Filters

#### Filters Available
- **Status**: Active, Inactive, All
- **Severity**: Critical, High, Medium, Low, Info
- **Type**: Allergy, Medication, Medical Condition, Behavioral, Administrative, etc.

#### Alert Display
- Alerts are sorted by severity (Critical → High → Medium → Low → Info)
- Then sorted by creation date (newest first)
- Color-coded by severity for quick identification

### Alert Visibility Rules
- You can see alerts for patients visible to you
- You can create alerts for patients in your department(s)
- You can edit/delete alerts you created
- Restricted alerts may have additional visibility limitations

### Alert Best Practices
1. **Use Appropriate Severity**: 
   - Critical: Life-threatening situations only
   - High: Important medical information
   - Medium: Moderate concerns
   - Low: Minor notes
   - Info: General information

2. **Set Expiration Dates**: 
   - For temporary alerts (e.g., post-surgery restrictions)
   - Alerts automatically become inactive after expiration

3. **Provide Clear Descriptions**: 
   - Include specific details
   - Mention any actions required
   - Reference relevant medical conditions

4. **Keep Alerts Updated**: 
   - Update alerts when conditions change
   - Deactivate alerts that are no longer relevant
   - Delete obsolete alerts

---

## General Tips & Best Practices

### Security
- Always log out when finished
- Never share your login credentials
- Use strong passwords
- Enable two-factor authentication if available

### Data Entry
- Fill in all required fields accurately
- Double-check patient information before saving
- Use consistent formatting for dates and times
- Add notes when necessary for context

### Navigation
- Use the search functionality to quickly find records
- Use filters to narrow down large lists
- Bookmark frequently used pages
- Use browser back button or menu to navigate

### Patient Privacy
- Only access patient records you need for your work
- Respect patient confidentiality
- Follow HIPAA/GDPR guidelines
- Log out when leaving your workstation

### Troubleshooting
- If a page doesn't load, refresh the browser
- Clear browser cache if experiencing issues
- Contact system administrator for technical issues
- Report bugs or errors immediately

### Getting Help
- Check this documentation first
- Contact your system administrator
- Review system notifications for updates
- Attend training sessions if available

---

## Quick Reference

### Common URLs
- Dashboard: `/staff/dashboard`
- Patients: `/staff/patients`
- Appointments: `/staff/appointments`
- Medical Records: `/staff/medical-records`
- Billing: `/staff/billing`
- Patient Alerts: `/staff/alerts`

### Keyboard Shortcuts
- `Ctrl + F`: Search on current page
- `Tab`: Navigate between form fields
- `Enter`: Submit forms
- `Esc`: Close modals/dialogs

### Status Indicators
- **Green**: Active/Completed/Paid
- **Yellow**: Pending/Partially Paid
- **Red**: Cancelled/Overdue/Critical
- **Blue**: Confirmed/In Progress
- **Gray**: Inactive/Archived

---

## Version Information
- **Document Version**: 1.0
- **Last Updated**: November 2025
- **System**: ThanksDoc EHR v1.0
- **User Role**: Doctor

---

## Support
For additional support or questions, please contact your system administrator or refer to the system help documentation.

**Powered by ThanksDoc**

