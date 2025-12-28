# Guest Patient Booking Flow

## Overview
The guest patient booking flow allows non-registered users to book appointments through public booking links without creating a full patient account. This flow handles both **free** and **paid** services differently.

---

## Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│  User accesses public booking link                          │
│  /book/{doctorSlug} or /book/clinic/{clinicSlug}           │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│  Step 1: Service Selection                                 │
│  - User selects a service                                  │
│  - System checks if service is free or paid                │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│  Step 2: Date & Time Selection                              │
│  - User selects appointment date and time                  │
│  - System checks slot availability                         │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│  Step 3: Patient Information Collection                     │
│  - First Name, Last Name                                    │
│  - Email (unique identifier)                               │
│  - Phone                                                    │
│  - Date of Birth (optional)                                 │
│  - Gender (optional)                                        │
│  - Address (optional)                                       │
│  - Consultation Type (in-person/online)                    │
│  - GP Consent & Details (optional)                         │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
            ┌──────────┴──────────┐
            │                     │
            ▼                     ▼
    ┌──────────────┐      ┌──────────────┐
    │ FREE SERVICE │      │ PAID SERVICE │
    │ (Fee = 0)    │      │ (Fee > 0)   │
    └──────┬───────┘      └──────┬───────┘
           │                     │
           ▼                     ▼
    ┌──────────────┐      ┌──────────────┐
    │ IMMEDIATE    │      │ PENDING      │
    │ BOOKING      │      │ BOOKING      │
    └──────┬───────┘      └──────┬───────┘
           │                     │
           │                     ▼
           │              ┌──────────────┐
           │              │ Create Guest │
           │              │ Patient      │
           │              └──────┬───────┘
           │                     │
           │                     ▼
           │              ┌──────────────┐
           │              │ Create       │
           │              │ PendingBooking│
           │              └──────┬───────┘
           │                     │
           │                     ▼
           │              ┌──────────────┐
           │              │ Create       │
           │              │ Invoice      │
           │              └──────┬───────┘
           │                     │
           │                     ▼
           │              ┌──────────────┐
           │              │ Redirect to  │
           │              │ Payment Page│
           │              └──────┬───────┘
           │                     │
           │                     ▼
           │              ┌──────────────┐
           │              │ User Pays    │
           │              └──────┬───────┘
           │                     │
           │                     ▼
           │              ┌──────────────┐
           │              │ Payment      │
           │              │ Callback     │
           │              └──────┬───────┘
           │                     │
           │                     ▼
           │              ┌──────────────┐
           │              │ Finalize     │
           │              │ Booking      │
           │              └──────┬───────┘
           │                     │
           └─────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│  Create Appointment                                         │
│  - Link to guest patient                                    │
│  - Set status (pending for free, confirmed for paid)       │
│  - Create Whereby meeting if online consultation           │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│  Create Billing                                             │
│  - Free: Zero amount, marked as paid                       │
│  - Paid: Full amount, marked as paid                       │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│  Send Notifications                                         │
│  - Email confirmation to patient                           │
│  - Email notification to doctor                            │
│  - In-app notifications to admin/staff                    │
└─────────────────────────────────────────────────────────────┘
```

---

## Detailed Flow Steps

### 1. **Service Selection** (`PublicBookingController::showDoctorBooking` or `showClinicBooking`)

- User accesses booking link: `/book/{doctorSlug}` or `/book/clinic/{clinicSlug}`
- System displays available services for the doctor/clinic
- User selects a service

### 2. **Date & Time Selection** (`PublicBookingController::selectDateTime`)

- User selects appointment date and time
- System checks slot availability using `SlotAvailabilityService`
- If booking through clinic, user may need to select a doctor

### 3. **Patient Information Collection** (`PublicBookingController::review`)

- User fills in:
  - **Required**: First Name, Last Name, Email, Phone
  - **Optional**: Date of Birth, Gender, Address
  - **Optional**: Consultation Type (in-person/online)
  - **Optional**: GP Consent & Details

### 4. **Booking Creation** (`PublicBookingService::createFromPublicBooking`)

The system branches based on service fee:

#### **A. FREE SERVICE (Fee = 0)** → Immediate Booking

**Process:**
1. **Create/Find Guest Patient** (`GuestPatientService::findOrCreateGuest`)
   - Checks if patient exists by email
   - If exists: Updates phone/name if different
   - If new: Creates guest patient with:
     - `is_guest = true`
     - Placeholder `date_of_birth = '1900-01-01'` if not provided
     - Placeholder `gender = 'other'` if not provided
     - Unique `patient_id` generated

2. **Create Appointment Immediately**
   - Status: `pending`
   - Links to guest patient
   - Creates Whereby meeting if online consultation

3. **Create Billing**
   - Amount: 0
   - Status: `paid`
   - Marked as completed

4. **Send Notifications**
   - Email confirmation to patient
   - Email notification to doctor
   - In-app notifications

#### **B. PAID SERVICE (Fee > 0)** → Pending Booking

**Process:**

1. **Create/Find Guest Patient** (Same as free service)
   - Patient is created **upfront** (required for invoice `patient_id`)

2. **Create PendingBooking Record**
   - Stores all booking data
   - Status: `pending_payment`
   - Expires in 24 hours
   - Contains `patient_data` JSON

3. **Create Invoice**
   - Links to guest patient (required)
   - Status: `pending`
   - Contains payment token for payment link

4. **Redirect to Payment**
   - User is redirected to payment page
   - Payment link: `/book/pay/{paymentToken}`

5. **After Payment Success** (`PublicBookingService::finalizeBookingAfterPayment`)
   - **Create Appointment**
     - Status: `confirmed` (auto-confirmed since paid)
     - Links to existing guest patient
     - Creates Whereby meeting if online
   
   - **Create Billing**
     - Amount: Full fee
     - Status: `paid`
     - Payment method: `card`
   
   - **Update Invoice**
     - Status: `paid`
     - Links to appointment and billing
   
   - **Mark PendingBooking as Completed**
   
   - **Send Notifications**
     - Same as free service

---

## Guest Patient Characteristics

### What Makes a Guest Patient?

1. **`is_guest = true`** flag in `patients` table
2. **Placeholder Information**:
   - Name: "Guest Patient" (if not provided)
   - Email: `guest.{timestamp}.{id}@payment-link.temp` (for payment links)
   - Date of Birth: `1900-01-01` (placeholder if not provided)
   - Gender: `other` (placeholder if not provided)

### Guest Patient Limitations

Guest patients have restrictions enforced by `CheckGuestRestrictions` middleware:

- ❌ **Cannot create/edit medical records**
- ❌ **Cannot create/edit prescriptions**
- ✅ **Can view appointments**
- ✅ **Can be converted to full patient**

### Converting Guest to Full Patient

**When:** Before creating medical records or prescriptions

**Process:**
1. Admin/Staff navigates to patient profile
2. Clicks "Convert to Full Patient"
3. Fills in required fields:
   - Date of Birth (real date)
   - Gender (real value)
   - Emergency Contact
   - Emergency Phone
   - Other missing required fields
4. System updates `is_guest = false`
5. Patient can now have medical records/prescriptions

---

## Key Files

### Services
- **`app/Services/PublicBookingService.php`**
  - Main booking orchestration
  - Handles both free and paid flows
  - Creates appointments, billing, notifications

- **`app/Services/GuestPatientService.php`**
  - Creates/finds guest patients
  - Converts guest to full patient

### Controllers
- **`app/Http/Controllers/PublicBookingController.php`**
  - Public-facing booking pages
  - Handles booking form submission

### Models
- **`app/Models/Patient.php`**
  - Guest patient detection methods
  - `hasIncompleteInformation()` - checks for placeholder data
  - `isVisibleTo()` - visibility rules

- **`app/Models/PendingBooking.php`**
  - Stores pending bookings awaiting payment
  - Expires after 24 hours

---

## Important Notes

1. **Patient Creation Timing**
   - **Free Service**: Patient created immediately
   - **Paid Service**: Patient created **before** invoice (required for `patient_id` NOT NULL constraint)

2. **Email Uniqueness**
   - Email is used as the unique identifier
   - If same email books again, existing patient is updated
   - Prevents duplicate guest patients

3. **Placeholder Data**
   - System uses placeholders for required fields
   - These must be updated before creating medical records
   - Alerts shown in UI when placeholder data detected

4. **Payment Token**
   - Generated for each invoice
   - Used in payment link: `/book/pay/{token}`
   - Expires after 90 days

5. **Booking Expiration**
   - Pending bookings expire after 24 hours
   - Expired bookings cannot be finalized
   - User must start over

6. **Whereby Integration**
   - Online consultations automatically create Whereby meetings
   - Meeting link added to appointment
   - Only if Whereby is enabled in settings

---

## Alerts & Warnings

The system shows alerts when:

1. **Incomplete Patient Information**
   - Missing: DOB, Gender, Phone, Address, Emergency Contact
   - Shown on appointment and patient pages

2. **Placeholder Information Detected**
   - Name = "Guest Patient"
   - Email contains "@payment-link.temp"
   - **URGENT** - Must be fixed before consultation

3. **Guest Patient Restrictions**
   - Cannot create medical records
   - Cannot create prescriptions
   - Must convert to full patient first

---

## Example Flow: Paid Service

1. User clicks doctor booking link
2. Selects "Consultation" service (£50)
3. Selects date: 2025-01-15, time: 10:00 AM
4. Fills form:
   - Name: "John Doe"
   - Email: "john@example.com"
   - Phone: "1234567890"
5. System creates:
   - Guest Patient (John Doe, is_guest=true)
   - PendingBooking (status=pending_payment)
   - Invoice (status=pending, amount=£50)
6. User redirected to payment page
7. User pays £50 via card
8. Payment callback triggers `finalizeBookingAfterPayment()`
9. System creates:
   - Appointment (status=confirmed, linked to John Doe)
   - Billing (status=paid, amount=£50)
   - Updates Invoice (status=paid)
   - Marks PendingBooking as completed
10. Notifications sent to patient, doctor, admin
11. Appointment is ready for consultation

---

## Example Flow: Free Service

1. User clicks doctor booking link
2. Selects "Free Consultation" service (£0)
3. Selects date: 2025-01-15, time: 10:00 AM
4. Fills form (same as above)
5. System creates **immediately**:
   - Guest Patient (John Doe, is_guest=true)
   - Appointment (status=pending, linked to John Doe)
   - Billing (status=paid, amount=£0)
6. Notifications sent
7. Appointment is ready (pending doctor confirmation)

---

This flow ensures that guest patients can book appointments without full registration, while maintaining data integrity and allowing conversion to full patients when needed.

