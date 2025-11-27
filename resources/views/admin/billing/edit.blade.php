@extends('admin.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Edit Bill')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ contextRoute('dashboard') }}">Dashboard</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ contextRoute('billing.index') }}">Billing Management</a>
    </li>
    <li class="breadcrumb-item active">Edit Bill #{{ $billing->bill_number }}</li>
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
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Bill #{{ $billing->bill_number }}</h5>
            <small class="text-muted">Update billing invoice details</small>
            <p class="page-subtitle text-muted">
                Update billing record for {{ $billing->patient->full_name }}
            </p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <form id="editBillForm" method="POST" action="{{ contextRoute('billing.update', $billing) }}">
                    @csrf
                    @method('PUT')

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
                                                <option value="{{ $patient->id }}" 
                                                    {{ old('patient_id', $billing->patient_id) == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->full_name }} - {{ $patient->phone }}
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
                                                <option value="{{ $doctor->id }}" 
                                                    {{ old('doctor_id', $billing->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->full_name }} - {{ $doctor->specialization }}
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
                                               value="{{ old('billing_date', formatDate($billing->billing_date)) }}" 
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
                                               value="{{ old('due_date', $billing->due_date ? formatDate($billing->due_date) : '') }}"
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
                                        <input type="number" class="form-control @error('subtotal') is-invalid @enderror"
                                               id="subtotal" name="subtotal" step="0.01" min="0"
                                               value="{{ old('subtotal', $billing->subtotal) }}" required>
                                        @error('subtotal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="type" class="form-label">
                                            <i class="fas fa-tags me-1"></i>Bill Type
                                        </label>
                                        <select name="type" id="type" class="form-control @error('type') is-invalid @enderror">
                                            <option value="consultation" {{ old('type', $billing->type) == 'consultation' ? 'selected' : '' }}>Consultation</option>
                                            <option value="procedure" {{ old('type', $billing->type) == 'procedure' ? 'selected' : '' }}>Procedure</option>
                                            <option value="medication" {{ old('type', $billing->type) == 'medication' ? 'selected' : '' }}>Medication</option>
                                            <option value="lab_test" {{ old('type', $billing->type) == 'lab_test' ? 'selected' : '' }}>Lab Test</option>
                                            <option value="other" {{ old('type', $billing->type) == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discount" class="form-label">
                                            <i class="fas fa-percent me-1"></i>Discount
                                        </label>
                                        <input type="number" class="form-control @error('discount') is-invalid @enderror"
                                               id="discount" name="discount" step="0.01" min="0"
                                               value="{{ old('discount', $billing->discount) }}">
                                        @error('discount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tax" class="form-label">
                                            <i class="fas fa-receipt me-1"></i>Tax
                                        </label>
                                        <input type="number" class="form-control @error('tax') is-invalid @enderror"
                                               id="tax" name="tax" step="0.01" min="0"
                                               value="{{ old('tax', $billing->tax) }}">
                                        @error('tax')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="description" class="form-label">
                                            <i class="fas fa-file-alt me-1"></i>Description *
                                        </label>
                                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                                  rows="3" required>{{ old('description', $billing->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-help">Detailed description of the billing service</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status" class="form-label">
                                            <i class="fas fa-info-circle me-1"></i>Status
                                        </label>
                                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                            <option value="pending" {{ old('status', $billing->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="paid" {{ old('status', $billing->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="partially_paid" {{ old('status', $billing->status) == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                                            <option value="overdue" {{ old('status', $billing->status) == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                            <option value="cancelled" {{ old('status', $billing->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paid_amount" class="form-label">
                                            <i class="fas fa-money-check me-1"></i>Paid Amount
                                        </label>
                                        <input type="text" class="form-control @error('paid_amount') is-invalid @enderror"
                                               id="paid_amount" name="paid_amount" 
                                               value="{{ old('paid_amount', $billing->paid_amount) }}">
                                        @error('paid_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-1"></i>Notes
                                </label>
                                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $billing->notes) }}</textarea>
                                <div class="form-help">Additional details about the billing transaction</div>
                            </div>

                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group text-end">
                        <a href="{{ contextRoute('billing.show', $billing) }}" class="btn btn-secondary me-2">
                            <i class="fas fa-eye me-2"></i>View Bill
                        </a>
                        <button type="submit" class="btn btn-doctor-primary">
                            <i class="fas fa-save me-2"></i>Update Bill
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <!-- Bill Summary -->
                <div class="info-card">
                    <h6><i class="fas fa-receipt me-2"></i>Bill Summary</h6>
                    <ul class="list-unstyled">
                        <li><strong>Bill Number:</strong> #{{ $billing->bill_number }}</li>
                        <li><strong>Patient:</strong> {{ $billing->patient->full_name }}</li>
                        <li><strong>Total Amount:</strong> {{ CurrencyHelper::format($billing->total_amount) }}</li>
                        <li><strong>Paid Amount:</strong> {{ CurrencyHelper::format($billing->paid_amount) }}</li>
                        <li><strong>Balance:</strong> {{ CurrencyHelper::format($billing->balance) }}</li>
                        <li><strong>Status:</strong> 
                            <span class="badge bg-{{ $billing->status == 'paid' ? 'success' : ($billing->status == 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst(str_replace('_', ' ', $billing->status)) }}
                            </span>
                        </li>
                    </ul>
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

    // Calculate total amount dynamically
    function calculateTotal() {
        const subtotal = parseFloat($('#subtotal').val()) || 0;
        const discount = parseFloat($('#discount').val()) || 0;
        const tax = parseFloat($('#tax').val()) || 0;
        const total = subtotal - discount + tax;
        
        // Update bill summary if it exists
        if ($('.info-card').length) {
            $('.info-card ul li:nth-child(3)').html('<strong>Total Amount:</strong> {{ CurrencyHelper::format(0) }}'.replace('0.00', total.toFixed(2)));
        }
    }

    // Bind calculation to input changes
    $('#subtotal, #discount, #tax').on('input change', calculateTotal);
    
    // Initial calculation on page load
    calculateTotal();

    // Form validation
    $('#editBillForm').on('submit', function(e) {
        let isValid = true;
        let errorMessage = '';
        
        // Check required fields
        const requiredFields = {
            'patient_id': 'Patient',
            'billing_date': 'Billing Date',
            'subtotal': 'Subtotal',
            'type': 'Bill Type',
            'description': 'Description'
        };
        
        $.each(requiredFields, function(field, label) {
            const value = $('[name="' + field + '"]').val();
            if (!value || value.trim() === '') {
                isValid = false;
                errorMessage += label + ' is required.\n';
            }
        });
        
        // Validate numeric fields
        const subtotal = parseFloat($('#subtotal').val());
        if (isNaN(subtotal) || subtotal < 0) {
            isValid = false;
            errorMessage += 'Subtotal must be a valid positive number.\n';
        }
        
        const discount = parseFloat($('#discount').val());
        if (discount && (isNaN(discount) || discount < 0)) {
            isValid = false;
            errorMessage += 'Discount must be a valid positive number.\n';
        }
        
        const tax = parseFloat($('#tax').val());
        if (tax && (isNaN(tax) || tax < 0)) {
            isValid = false;
            errorMessage += 'Tax must be a valid positive number.\n';
        }
        
        // Validate dates
        const billingDate = new Date($('#billing_date').val());
        const dueDate = new Date($('#due_date').val());
        
        if ($('#due_date').val() && dueDate < billingDate) {
            isValid = false;
            errorMessage += 'Due date cannot be earlier than billing date.\n';
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please correct the following errors:\n\n' + errorMessage);
            return false;
        }
    });

    // Auto-format numeric inputs
    $('#subtotal, #discount, #tax, #paid_amount').on('blur', function() {
        const value = parseFloat($(this).val());
        if (!isNaN(value)) {
            $(this).val(value.toFixed(2));
        }
    });
});
</script>
@endpush
