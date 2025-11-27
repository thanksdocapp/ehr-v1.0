@extends('admin.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Create New Bill')

@section('breadcrumb')
    	<li class="breadcrumb-item">
        <a href="{{ contextRoute('dashboard') }}">Dashboard</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ contextRoute('billing.index') }}">Billing Management</a>
    </li>
    <li class="breadcrumb-item active">Create New Bill</li>
@endsection

@push('styles')
    <style>
        .form-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            border: 1px solid #e3e6f0;
        }

        .form-section-header {
            background: #f8f9fc;
            color: #2d3748;
            padding: 1.5rem 2rem;
            border-radius: 12px 12px 0 0;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .form-section-header h4,
        .form-section-header h5 {
            color: #1a202c;
            font-weight: 700;
        }
        
        .form-section-header i {
            color: #1a202c;
        }
        
        .form-section-header small {
            color: #4a5568;
        }

        .form-section-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #1cc88a;
            box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
        }

        .btn {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
        }

        .form-help {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.5rem;
            font-style: italic;
        }

        .info-card {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-card h6 {
            color: #5a5c69;
            margin-bottom: 1rem;
        }

        .info-card ul {
            margin: 0;
            padding-left: 1.5rem;
        }

        .info-card li {
            margin-bottom: 0.5rem;
            color: #858796;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="page-title mb-4">
            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Create New Bill</h5>
            <small class="text-muted">Create a new billing invoice</small>
            <p class="page-subtitle text-muted">
                Generate a billing record for a patient's medical services
            </p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <form id="createBillForm" method="POST" action="{{ contextRoute('billing.store') }}">
                    @csrf

                    <!-- Billing Information -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h4 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Billing Information
                            </h4>
                            <small class="opacity-75">Details of the billing transaction</small>
                        </div>
                        <div class="form-section-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="patient" class="form-label">
                                            <i class="fas fa-user me-1"></i>Patient *
                                        </label>
                                        <select name="patient_id" id="patient" class="form-control @error('patient_id') is-invalid @enderror" required>
                                            <option value="">Select a patient</option>
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->first_name }} {{ $patient->last_name }} - {{ $patient->email }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('patient_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="doctor" class="form-label">
                                            <i class="fas fa-user-md me-1"></i>Doctor
                                        </label>
                                        <select name="doctor_id" id="doctor" class="form-control @error('doctor_id') is-invalid @enderror">
                                            <option value="">Select a doctor</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                    Dr. {{ $doctor->first_name }} {{ $doctor->last_name }} - {{ $doctor->specialization ?? 'General' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="billing_date" class="form-label">
                                            <i class="fas fa-calendar me-1"></i>Billing Date *
                                        </label>
                                        <input type="text" class="form-control @error('billing_date') is-invalid @enderror"
                                               id="billing_date" name="billing_date" 
                                               value="{{ old('billing_date') ? formatDate(old('billing_date')) : '' }}" 
                                               placeholder="dd-mm-yyyy" 
                                               pattern="\d{2}-\d{2}-\d{4}" 
                                               maxlength="10" required>
                                        <small class="form-text text-muted">Format: dd-mm-yyyy (e.g., 15-01-2025)</small>
                                        @error('billing_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="due_date" class="form-label">
                                            <i class="fas fa-calendar-alt me-1"></i>Due Date
                                        </label>
                                        <input type="text" class="form-control @error('due_date') is-invalid @enderror" 
                                               id="due_date" name="due_date" 
                                               value="{{ old('due_date') ? formatDate(old('due_date')) : '' }}"
                                               placeholder="dd-mm-yyyy" 
                                               pattern="\d{2}-\d{2}-\d{4}" 
                                               maxlength="10">
                                        <small class="form-text text-muted">Format: dd-mm-yyyy (e.g., 15-01-2025)</small>
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="subtotal" class="form-label">
                                            <i class="fas fa-coins me-1"></i>Subtotal *
                                        </label>
                                        <input type="text" class="form-control @error('subtotal') is-invalid @enderror"
                                               id="subtotal" name="subtotal" value="{{ old('subtotal') }}" required>
                                        @error('subtotal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="type" class="form-label">
                                            <i class="fas fa-tags me-1"></i>Bill Type
                                        </label>
                                        <select name="type" id="type" class="form-control @error('type') is-invalid @enderror">
                                            <option value="consultation" {{ old('type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                                            <option value="procedure" {{ old('type') == 'procedure' ? 'selected' : '' }}>Procedure</option>
                                            <option value="medication" {{ old('type') == 'medication' ? 'selected' : '' }}>Medication</option>
                                            <option value="lab_test" {{ old('type') == 'lab_test' ? 'selected' : '' }}>Lab Test</option>
                                            <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="description" class="form-label">
                                            <i class="fas fa-info-circle me-1"></i>Description *
                                        </label>
                                        <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                               id="description" name="description" value="{{ old('description') }}" required>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discount" class="form-label">
                                            <i class="fas fa-percentage me-1"></i>Discount
                                        </label>
                                        <input type="text" class="form-control @error('discount') is-invalid @enderror" 
                                               id="discount" name="discount" value="{{ old('discount') }}">
                                        @error('discount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tax" class="form-label">
                                            <i class="fas fa-coins me-1"></i>Tax
                                        </label>
                                        <input type="text" class="form-control @error('tax') is-invalid @enderror" 
                                               id="tax" name="tax" value="{{ old('tax') }}">
                                        @error('tax')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-1"></i>Notes
                                </label>
                                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                                <div class="form-help">Additional details about the billing transaction</div>
                            </div>

                            <!-- Send to Patient Option -->
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="send_to_patient" id="send_to_patient" value="1" {{ old('send_to_patient') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_to_patient">
                                        <i class="fas fa-envelope me-1"></i>
                                        <strong>Send billing notification to patient</strong>
                                    </label>
                                    <div class="form-help">
                                        Patient will receive an email with a secure payment link to pay online without logging in.
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group text-end">
                        <button type="submit" class="btn btn-doctor-primary">
                            <i class="fas fa-save me-2"></i>Save Bill
                        </button>
                        <a href="{{ contextRoute('billing.index') }}" class="btn btn-secondary ms-2">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Helper Information -->
            <div class="col-lg-4">
                <div class="info-card">
                    <h6><i class="fas fa-info-circle me-2"></i>Billing Guidelines</h6>
                    <ul>
                        <li>All fields marked with * are required</li>
                        <li>Select the correct patient for accurate billing</li>
                        <li>Choose appropriate doctor if applicable</li>
                        <li>Set realistic due dates for payment</li>
                        <li>Double-check amounts before saving</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-coins me-2"></i>Billing Types</h6>
                    <ul>
                        <li><strong>Consultation:</strong> Doctor visit fees</li>
                        <li><strong>Procedure:</strong> Medical procedures</li>
                        <li><strong>Medication:</strong> Prescribed medicines</li>
                        <li><strong>Lab Test:</strong> Laboratory examinations</li>
                        <li><strong>Other:</strong> Additional services</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-lightbulb me-2"></i>Best Practices</h6>
                    <ul>
                        <li>Verify patient insurance information</li>
                        <li>Include detailed service descriptions</li>
                        <li>Set appropriate payment terms</li>
                        <li>Document all billing communications</li>
                        <li>Follow up on overdue payments</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-shield-alt me-2"></i>Compliance & Security</h6>
                    <ul>
                        <li><strong>GDPR Compliant:</strong> Patient data protected</li>
                        <li><strong>Audit Trail:</strong> All changes logged</li>
                        <li><strong>Payment Security:</strong> Secure processing</li>
                        <li><strong>Data Backup:</strong> Regular automated backups</li>
                        <li><strong>Access Control:</strong> Role-based permissions</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ contextRoute('billing.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i>View All Bills
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="calculate-total">
                            <i class="fas fa-calculator me-1"></i>Calculate Total
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" id="validate-form">
                            <i class="fas fa-check me-1"></i>Validate Form
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Date input mask for dd-mm-yyyy format
    $('#billing_date, #due_date').on('input', function() {
        let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        if (value.length >= 2) {
            value = value.substring(0, 2) + '-' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 9);
        }
        $(this).val(value);
    });

    // Convert date format from dd-mm-yyyy to yyyy-mm-dd before form submission
    $('form').on('submit', function() {
        $('#billing_date, #due_date').each(function() {
            const dateStr = $(this).val();
            if (dateStr && dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
                const parts = dateStr.split('-');
                const yyyyMmDd = parts[2] + '-' + parts[1] + '-' + parts[0];
                $(this).val(yyyyMmDd);
            }
        });
    });
    // Set today's date as default for billing date (in dd-mm-yyyy format)
    if (!$('#billing_date').val()) {
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        $('#billing_date').val(dd + '-' + mm + '-' + yyyy);
    }

    // Set due date 30 days from billing date when billing date changes
    $('#billing_date').change(function() {
        if ($(this).val() && !$('#due_date').val()) {
            const dateStr = $(this).val();
            if (dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
                const parts = dateStr.split('-');
                const billingDate = new Date(parts[2], parts[1] - 1, parts[0]);
                billingDate.setDate(billingDate.getDate() + 30);
                const dd = String(billingDate.getDate()).padStart(2, '0');
                const mm = String(billingDate.getMonth() + 1).padStart(2, '0');
                const yyyy = billingDate.getFullYear();
                $('#due_date').val(dd + '-' + mm + '-' + yyyy);
            }
        }
    });

    // Calculate total functionality
    $('#calculate-total').click(function() {
        const subtotal = parseFloat($('#subtotal').val().replace(/[^0-9.-]+/g, '')) || 0;
        const discount = parseFloat($('#discount').val().replace(/[^0-9.-]+/g, '')) || 0;
        const tax = parseFloat($('#tax').val().replace(/[^0-9.-]+/g, '')) || 0;
        
        const totalAmount = subtotal - discount + tax;
        
        if (subtotal > 0) {
            alert(`Calculation:\nSubtotal: {{ CurrencyHelper::format(0) }}`.replace('0.00', subtotal.toFixed(2)) + `\nDiscount: {{ CurrencyHelper::format(0) }}`.replace('0.00', discount.toFixed(2)) + `\nTax: {{ CurrencyHelper::format(0) }}`.replace('0.00', tax.toFixed(2)) + `\nTotal: {{ CurrencyHelper::format(0) }}`.replace('0.00', totalAmount.toFixed(2)));
        } else {
            alert('Please enter a subtotal amount first');
        }
    });

    // Form validation functionality
    $('#validate-form').click(function() {
        let errors = [];
        
        if (!$('#patient').val()) errors.push('Patient is required');
        if (!$('#billing_date').val()) errors.push('Billing date is required');
        if (!$('#subtotal').val()) errors.push('Subtotal is required');
        if (!$('#description').val()) errors.push('Description is required');
        
        // Validate subtotal is numeric
        const subtotal = $('#subtotal').val();
        if (subtotal) {
            const numericSubtotal = parseFloat(subtotal.replace(/[^0-9.-]+/g, ''));
            if (isNaN(numericSubtotal) || numericSubtotal <= 0) {
                errors.push('Subtotal must be a valid positive number');
            }
        }
        
        // Validate due date is after billing date
        const billingDate = new Date($('#billing_date').val());
        const dueDate = new Date($('#due_date').val());
        if ($('#due_date').val() && dueDate < billingDate) {
            errors.push('Due date cannot be before billing date');
        }
        
        if (errors.length > 0) {
            alert('Please fix the following errors:\n' + errors.join('\n'));
        } else {
            alert('Form validation passed! Ready to save.');
        }
    });

    // Auto-format amount inputs
    $('#subtotal, #discount, #tax').on('blur', function() {
        const value = $(this).val();
        if (value) {
            const numericValue = parseFloat(value.replace(/[^0-9.-]+/g, ''));
            if (!isNaN(numericValue)) {
                $(this).val(numericValue.toFixed(2));
            }
        }
    });

    // Form submission validation
    $('#createBillForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        if (!$('#patient').val()) {
            $('#patient').addClass('is-invalid');
            isValid = false;
        }
        if (!$('#billing_date').val()) {
            $('#billing_date').addClass('is-invalid');
            isValid = false;
        }
        if (!$('#subtotal').val()) {
            $('#subtotal').addClass('is-invalid');
            isValid = false;
        }
        if (!$('#description').val()) {
            $('#description').addClass('is-invalid');
            isValid = false;
        }
        
        // Validate subtotal is numeric and positive
        const subtotal = $('#subtotal').val();
        if (subtotal) {
            const numericSubtotal = parseFloat(subtotal.replace(/[^0-9.-]+/g, ''));
            if (isNaN(numericSubtotal) || numericSubtotal <= 0) {
                $('#subtotal').addClass('is-invalid');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);
    });
});
</script>
@endpush
