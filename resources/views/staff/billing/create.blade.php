@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Create New Bill')
@section('page-title', 'Create New Bill')
@section('page-subtitle', 'Create a new bill for patient services')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ contextRoute('dashboard') }}">Dashboard</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ contextRoute('billing.index') }}">Billing</a>
    </li>
    <li class="breadcrumb-item active">Create New Bill</li>
@endsection

@section('content')
    <div class="fade-in-up">
        <div class="page-title mb-4">
            <h1>
                <i class="fas fa-receipt me-2 text-primary"></i>Create New Bill
            </h1>
            <p class="page-subtitle text-muted">
                Generate a billing record for a patient's medical services
            </p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <form id="createBillForm" method="POST" action="{{ contextRoute('billing.store') }}">
                    @csrf

                    <!-- Billing Information -->
                    <div class="card mb-4">
                        <div class="doctor-card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Billing Information
                            </h5>
                        </div>
                        <div class="doctor-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="patient" class="form-label">
                                            <i class="fas fa-user me-1"></i>Patient *
                                        </label>
                                        <select name="patient_id" id="patient" class="form-select @error('patient_id') is-invalid @enderror" required>
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

                                    <div class="mb-3">
                                        <label for="doctor" class="form-label">
                                            <i class="fas fa-user-md me-1"></i>Doctor
                                        </label>
                                        @if(auth()->user()->role === 'doctor' && isset($currentDoctor) && $currentDoctor)
                                            {{-- For doctors: Show read-only field with hidden input --}}
                                            <input type="text" class="form-control" value="Dr. {{ $currentDoctor->first_name }} {{ $currentDoctor->last_name }} - {{ $currentDoctor->specialization ?? 'General' }}" readonly>
                                            <input type="hidden" name="doctor_id" value="{{ $currentDoctor->id }}">
                                            <div class="form-text">This bill will be assigned to you</div>
                                        @else
                                            {{-- For other staff: Show dropdown --}}
                                            <select name="doctor_id" id="doctor" class="form-select @error('doctor_id') is-invalid @enderror">
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
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label for="billing_date" class="form-label">
                                            <i class="fas fa-calendar me-1"></i>Billing Date *
                                        </label>
                                        <input type="date" class="form-control @error('billing_date') is-invalid @enderror"
                                               id="billing_date" name="billing_date" value="{{ old('billing_date') }}" required>
                                        @error('billing_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="due_date" class="form-label">
                                            <i class="fas fa-calendar-alt me-1"></i>Due Date
                                        </label>
                                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                               id="due_date" name="due_date" value="{{ old('due_date') }}">
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="subtotal" class="form-label">
                                            <i class="fas fa-pound-sign me-1"></i>Subtotal *
                                        </label>
                                        <input type="text" class="form-control @error('subtotal') is-invalid @enderror"
                                               id="subtotal" name="subtotal" value="{{ old('subtotal') }}" required>
                                        @error('subtotal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="type" class="form-label">
                                            <i class="fas fa-tags me-1"></i>Bill Type
                                        </label>
                                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
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
                                    <div class="mb-3">
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
                                    <div class="mb-3">
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
                                    <div class="mb-3">
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

                            <div class="mb-3">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-1"></i>Notes
                                </label>
                                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                                <div class="form-text">Additional details about the billing transaction</div>
                            </div>

                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-doctor-primary">
                            <i class="fas fa-save me-2"></i>Save Bill
                        </button>
                        <a href="{{ contextRoute('billing.index') }}" class="btn btn-secondary ms-2">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="doctor-card mb-4">
                    <div class="doctor-doctor-card-header">
                        <h5 class="doctor-doctor-card-title mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" form="createBillForm" class="btn btn-doctor-primary">
                                <i class="fas fa-receipt me-1"></i>Create Bill
                            </button>
                            <a href="{{ contextRoute('billing.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Staff Information -->
                @if(auth()->user())
                    <div class="doctor-card mb-4">
                        <div class="doctor-doctor-card-header">
                            <h5 class="doctor-doctor-card-title mb-0"><i class="fas fa-user me-2"></i>Staff Information</h5>
                        </div>
                        <div class="doctor-card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-lg me-3">
                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ auth()->user()->name }}</div>
                                    <div class="text-muted">{{ ucfirst(auth()->user()->role) }}</div>
                                    <small class="text-muted">{{ auth()->user()->email }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Billing Guidelines -->
                <div class="card border-info mb-4">
                    <div class="doctor-card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-info-circle text-info fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-info">Billing Guidelines</h6>
                                <ul class="mb-0 text-muted small">
                                    <li class="mb-1"><strong>Patient Selection:</strong> Ensure correct patient is selected for accurate billing</li>
                                    <li class="mb-1"><strong>Amount Verification:</strong> Double-check all monetary amounts before saving</li>
                                    <li class="mb-1"><strong>Due Dates:</strong> Set realistic payment due dates based on hospital policy</li>
                                    <li class="mb-1"><strong>Documentation:</strong> Include detailed notes for billing transparency</li>
                                    <li class="mb-1"><strong>Privacy:</strong> All billing information is confidential and GDPR compliant</li>
                                    <li><strong>Follow-up:</strong> Track payment status and follow up on overdue bills</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Types Info -->
                <div class="card border-warning mb-4">
                    <div class="doctor-card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-tags text-warning fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-warning">Billing Types</h6>
                                <ul class="mb-0 text-muted small">
                                    <li class="mb-1"><strong>Consultation:</strong> Doctor visit and examination fees</li>
                                    <li class="mb-1"><strong>Procedure:</strong> Medical procedures and treatments</li>
                                    <li class="mb-1"><strong>Medication:</strong> Prescribed medicines and pharmacy items</li>
                                    <li class="mb-1"><strong>Lab Test:</strong> Laboratory examinations and diagnostics</li>
                                    <li><strong>Other:</strong> Additional hospital services and fees</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="card border-success">
                    <div class="doctor-card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-lightbulb text-success fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-success">Quick Tips</h6>
                                <ul class="mb-0 text-muted small">
                                    <li class="mb-1">Use clear, descriptive notes for billing details</li>
                                    <li class="mb-1">Verify patient insurance information when available</li>
                                    <li class="mb-1">Set due dates according to hospital payment policies</li>
                                    <li class="mb-1">Keep records of all billing communications</li>
                                    <li>Contact patients promptly for payment issues</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Set today's date as default for billing date
    if (!$('#billing_date').val()) {
        $('#billing_date').val(new Date().toISOString().split('T')[0]);
    }

    // Set due date 30 days from billing date when billing date changes
    $('#billing_date').change(function() {
        if ($(this).val() && !$('#due_date').val()) {
            const billingDate = new Date($(this).val());
            billingDate.setDate(billingDate.getDate() + 30);
            $('#due_date').val(billingDate.toISOString().split('T')[0]);
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

    // Form validation
    $('#createBillForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        if (!$('#patient').val()) {
            $('#patient').addClass('is-invalid');
            isValid = false;
        } else {
            $('#patient').removeClass('is-invalid');
        }
        
        if (!$('#billing_date').val()) {
            $('#billing_date').addClass('is-invalid');
            isValid = false;
        } else {
            $('#billing_date').removeClass('is-invalid');
        }
        
        if (!$('#subtotal').val()) {
            $('#subtotal').addClass('is-invalid');
            isValid = false;
        } else {
            $('#subtotal').removeClass('is-invalid');
        }
        
        if (!$('#description').val()) {
            $('#description').addClass('is-invalid');
            isValid = false;
        } else {
            $('#description').removeClass('is-invalid');
        }
        
        // Validate subtotal is numeric and positive
        const subtotal = $('#subtotal').val();
        if (subtotal) {
            const numericSubtotal = parseFloat(subtotal.replace(/[^0-9.-]+/g, ''));
            if (isNaN(numericSubtotal) || numericSubtotal <= 0) {
                $('#subtotal').addClass('is-invalid');
                $('#subtotal').next('.invalid-feedback').remove();
                $('#subtotal').after('<div class="invalid-feedback">Please enter a valid positive amount</div>');
                isValid = false;
            }
        }
        
        // Validate due date is after billing date
        const billingDate = new Date($('#billing_date').val());
        const dueDate = new Date($('#due_date').val());
        if ($('#due_date').val() && dueDate < billingDate) {
            $('#due_date').addClass('is-invalid');
            $('#due_date').next('.invalid-feedback').remove();
            $('#due_date').after('<div class="invalid-feedback">Due date cannot be before billing date</div>');
            isValid = false;
        } else {
            $('#due_date').removeClass('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.is-invalid:first').offset().top - 100
            }, 500);
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);
        $('.btn[form="createBillForm"]').html('<i class="fas fa-spinner fa-spin me-1"></i>Creating...').prop('disabled', true);
    });

    // Real-time validation
    $('input, select, textarea').on('blur change', function() {
        if ($(this).hasClass('is-invalid')) {
            if ($(this).prop('required') && $(this).val().trim()) {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            } else if (!$(this).prop('required')) {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        }
    });

    // Amount input formatting on type
    $('#subtotal, #discount, #tax').on('input', function() {
        let value = $(this).val();
        // Remove any non-numeric characters except decimal point
        value = value.replace(/[^0-9.]/g, '');
        // Ensure only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        $(this).val(value);
    });
});
</script>
@endpush
