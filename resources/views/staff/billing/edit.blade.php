@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Bill')
@section('page-title', 'Edit Bill')
@section('page-subtitle', 'Update billing invoice details')

@push('styles')
@include('admin.shared.modern-ui')
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ contextRoute('dashboard') }}">Dashboard</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ contextRoute('billing.index') }}">Billing</a>
    </li>
    <li class="breadcrumb-item active">Edit Bill #{{ $billing->bill_number }}</li>
@endsection

@section('content')
    <div class="fade-in-up">
        <div class="modern-page-header">
            <div class="modern-page-header-content">
                <h1 class="modern-page-title">
                    <i class="fas fa-edit me-2"></i>Edit Bill #{{ $billing->bill_number }}
                </h1>
                <p class="modern-page-subtitle">
                    Update billing record for {{ $billing->patient->full_name }}
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <form id="editBillForm" method="POST" action="{{ route('staff.billing.update', $billing) }}">
                    @csrf
                    @method('PUT')

                    <!-- Billing Information -->
                    <div class="doctor-card mb-4">
                        <div class="doctor-card-header">
                            <h5 class="doctor-card-title mb-0">
                                <i class="fas fa-info-circle me-2" style="color: #1a202c;"></i>Billing Information
                            </h5>
                        </div>
                        <div class="doctor-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="patient" class="form-label">
                                            <i class="fas fa-user me-1"></i>Patient *
                                        </label>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text"
                                                   class="form-control"
                                                   id="patientSearchInput"
                                                   placeholder="Search patient by name or phone…"
                                                   autocomplete="off">
                                            <button type="button" class="btn btn-outline-secondary" id="patientSearchClearBtn" style="display:none;">
                                                Clear
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mb-2" id="patientSearchMeta" style="display:none;"></small>
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

                                    <div class="mb-3">
                                        <label for="doctor" class="form-label">
                                            <i class="fas fa-user-md me-1"></i>Doctor
                                        </label>
                                        @if($currentDoctor)
                                            <input type="text" class="form-control" value="Dr. {{ $currentDoctor->full_name }}" disabled>
                                            <input type="hidden" name="doctor_id" value="{{ $currentDoctor->id }}">
                                        @else
                                            <select name="doctor_id" id="doctor" class="form-control @error('doctor_id') is-invalid @enderror">
                                                <option value="">Select a doctor</option>
                                                @foreach($doctors as $doctor)
                                                    <option value="{{ $doctor->id }}" 
                                                        {{ old('doctor_id', $billing->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                        {{ $doctor->full_name }} - {{ $doctor->specialization }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                        @error('doctor_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="billing_date" class="form-label">
                                            <i class="fas fa-calendar me-1"></i>Billing Date *
                                        </label>
                                        <input type="text" class="form-control uk-date @error('billing_date') is-invalid @enderror"
                                               id="billing_date" name="billing_date" 
                                               value="{{ old('billing_date') ? (old('billing_date') && preg_match('/^\d{4}-\d{2}-\d{2}$/', old('billing_date')) ? \Carbon\Carbon::parse(old('billing_date'))->format('d/m/Y') : old('billing_date')) : ($billing->billing_date ? $billing->billing_date->format('d/m/Y') : '') }}"
                                               placeholder="dd/mm/yyyy"
                                               pattern="\d{2}/\d{2}/\d{4}"
                                               maxlength="10"
                                               required>
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
                                        <input type="text" class="form-control uk-date @error('due_date') is-invalid @enderror" 
                                               id="due_date" name="due_date" 
                                               value="{{ old('due_date') ? (old('due_date') && preg_match('/^\d{4}-\d{2}-\d{2}$/', old('due_date')) ? \Carbon\Carbon::parse(old('due_date'))->format('d/m/Y') : old('due_date')) : ($billing->due_date ? $billing->due_date->format('d/m/Y') : '') }}"
                                               placeholder="dd/mm/yyyy"
                                               pattern="\d{2}/\d{2}/\d{4}"
                                               maxlength="10">
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
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

                                    <div class="mb-3">
                                        <label for="type" class="form-label">
                                            <i class="fas fa-tags me-1"></i>Bill Type *
                                        </label>
                                        <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
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
                                    <div class="mb-3">
                                        <label for="discount" class="form-label">
                                            <i class="fas fa-percent me-1"></i>Discount
                                        </label>
                                        <input type="number" class="form-control @error('discount') is-invalid @enderror"
                                               id="discount" name="discount" step="0.01" min="0"
                                               value="{{ old('discount', $billing->discount ?? 0) }}">
                                        @error('discount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tax" class="form-label">
                                            <i class="fas fa-receipt me-1"></i>Tax
                                        </label>
                                        <input type="number" class="form-control @error('tax') is-invalid @enderror"
                                               id="tax" name="tax" step="0.01" min="0"
                                               value="{{ old('tax', $billing->tax ?? 0) }}">
                                        @error('tax')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>Description *
                                </label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3" required>{{ old('description', $billing->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-1"></i>Notes
                                </label>
                                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $billing->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('staff.billing.show', $billing) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-doctor-primary">
                            <i class="fas fa-save me-2"></i>Update Bill
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <!-- Bill Summary -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h6 class="doctor-card-title mb-0">
                            <i class="fas fa-receipt me-2" style="color: #1a202c;"></i>Bill Summary
                        </h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted"><strong>Bill Number:</strong></label>
                            <div class="fw-bold">#{{ $billing->bill_number }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted"><strong>Patient:</strong></label>
                            <div class="fw-bold">{{ $billing->patient->full_name }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted"><strong>Total Amount:</strong></label>
                            <div class="fw-bold text-primary" id="displayTotalAmount">£{{ number_format($billing->total_amount, 2) }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted"><strong>Paid Amount:</strong></label>
                            <div class="fw-bold text-success">£{{ number_format($billing->paid_amount, 2) }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted"><strong>Balance:</strong></label>
                            <div class="fw-bold" id="displayBalance">£{{ number_format($billing->balance, 2) }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted"><strong>Status:</strong></label>
                            <div>
                                @php
                                    $statusColors = [
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'partially_paid' => 'info',
                                        'overdue' => 'danger',
                                        'cancelled' => 'secondary'
                                    ];
                                    $statusColor = $statusColors[$billing->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $billing->status)) }}</span>
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
    // ===== Patient search (alerts-style: live filter the select options) =====
    (function initPatientSelectSearch() {
        const select = document.getElementById('patient');
        const input = document.getElementById('patientSearchInput');
        const clearBtn = document.getElementById('patientSearchClearBtn');
        const meta = document.getElementById('patientSearchMeta');
        if (!select || !input || !clearBtn || !meta) return;

        const cachedOptions = Array.from(select.options).map(opt => ({
            value: opt.value,
            label: (opt.textContent || '').trim(),
            disabled: !!opt.disabled,
            dataset: { ...opt.dataset },
        }));

        const placeholder = cachedOptions[0] || { value: '', label: 'Select a patient', disabled: false, dataset: {} };
        const patients = cachedOptions.slice(1).filter(o => o.value);
        const totalPatients = patients.length;

        function rebuildOptions(optionsToShow, currentValue) {
            select.innerHTML = '';

            const ph = document.createElement('option');
            ph.value = placeholder.value;
            ph.textContent = placeholder.label;
            ph.disabled = placeholder.disabled;
            select.appendChild(ph);

            const selected = patients.find(p => p.value === currentValue);
            if (selected && !optionsToShow.some(p => p.value === currentValue)) {
                optionsToShow = [selected, ...optionsToShow];
            }

            for (const item of optionsToShow) {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.textContent = item.label;
                if (item.dataset) {
                    for (const [k, v] of Object.entries(item.dataset)) {
                        opt.dataset[k] = v;
                    }
                }
                select.appendChild(opt);
            }

            if (currentValue) {
                select.value = currentValue;
            }
        }

        function updateMeta(query, visibleCount) {
            if (!query) {
                meta.style.display = 'none';
                meta.textContent = '';
                return;
            }
            meta.style.display = 'block';
            meta.textContent = visibleCount > 0
                ? `Found ${visibleCount} of ${totalPatients} patients`
                : 'No patients found. Try a different search or clear.';
        }

        let t = null;
        function applyFilter() {
            const query = (input.value || '').toLowerCase().trim();
            clearBtn.style.display = query ? 'inline-block' : 'none';

            const currentValue = select.value;
            if (!query) {
                rebuildOptions(patients, currentValue);
                updateMeta('', 0);
                return;
            }

            const matches = patients.filter(p => (p.label || '').toLowerCase().includes(query));
            rebuildOptions(matches, currentValue);
            updateMeta(query, matches.length);
        }

        input.addEventListener('input', () => {
            if (t) window.clearTimeout(t);
            t = window.setTimeout(applyFilter, 80);
        });

        clearBtn.addEventListener('click', () => {
            input.value = '';
            applyFilter();
            input.focus();
        });

        applyFilter();
    })();

    // Calculate total amount dynamically
    function calculateTotal() {
        const subtotal = parseFloat($('#subtotal').val()) || 0;
        const discount = parseFloat($('#discount').val()) || 0;
        const tax = parseFloat($('#tax').val()) || 0;
        const total = subtotal - discount + tax;
        
        // Update display
        $('#displayTotalAmount').text('£' + total.toFixed(2));
        
        // Calculate balance (total - paid amount)
        const paidAmount = {{ $billing->paid_amount }};
        const balance = total - paidAmount;
        $('#displayBalance').text('£' + balance.toFixed(2));
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
        if (!$('#patient').val()) {
            isValid = false;
            errorMessage += 'Patient is required.\n';
        }
        
        if (!$('#billing_date').val()) {
            isValid = false;
            errorMessage += 'Billing Date is required.\n';
        }
        
        const subtotal = parseFloat($('#subtotal').val());
        if (isNaN(subtotal) || subtotal < 0) {
            isValid = false;
            errorMessage += 'Subtotal must be a valid positive number.\n';
        }
        
        const discount = parseFloat($('#discount').val()) || 0;
        if (discount < 0) {
            isValid = false;
            errorMessage += 'Discount must be a valid positive number.\n';
        }
        
        const tax = parseFloat($('#tax').val()) || 0;
        if (tax < 0) {
            isValid = false;
            errorMessage += 'Tax must be a valid positive number.\n';
        }
        
        // Validate dates
        const billingDate = new Date($('#billing_date').val());
        const dueDate = $('#due_date').val() ? new Date($('#due_date').val()) : null;
        
        if (dueDate && dueDate < billingDate) {
            isValid = false;
            errorMessage += 'Due date cannot be earlier than billing date.\n';
        }
        
        if (!$('#description').val().trim()) {
            isValid = false;
            errorMessage += 'Description is required.\n';
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please correct the following errors:\n\n' + errorMessage);
            return false;
        }
    });

    // Auto-format numeric inputs
    $('#subtotal, #discount, #tax').on('blur', function() {
        const value = parseFloat($(this).val());
        if (!isNaN(value)) {
            $(this).val(value.toFixed(2));
        }
    });
});
</script>
@endpush

