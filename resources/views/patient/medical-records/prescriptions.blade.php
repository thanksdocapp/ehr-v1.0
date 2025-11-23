@extends('patient.layouts.app')

@section('title', 'Prescriptions')
@section('page-title', 'Prescriptions')

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-success-gradient">
                    <i class="fas fa-prescription-bottle-alt"></i>
                </div>
                <div class="stat-number text-success">{{ $prescriptions->total() }}</div>
                <div class="stat-label">Prescriptions</div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>
                Filter Prescriptions
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('patient.prescriptions.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('patient.prescriptions.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Prescriptions List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-prescription-bottle me-2"></i>
                    Prescription Details
                </h5>
                @if($prescriptions->count() > 0)
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-print me-1"></i>
                            Print Options
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="printPrescriptionsTable(); return false;">
                                <i class="fas fa-table me-2"></i>Print All Prescriptions
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="window.print(); return false;">
                                <i class="fas fa-print me-2"></i>Print Current Page
                            </a></li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($prescriptions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
<thead>
                            <tr>
                                <th>Date</th>
                                <th>Doctor</th>
                                <th>Medication</th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prescriptions as $prescription)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $prescription->created_at->format('M d, Y') }}</strong>
                                            <small class="text-muted">{{ $prescription->created_at->format('g:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $prescription->doctor->full_name }}</strong>
                                            <small class="text-muted">{{ $prescription->doctor->specialization ?? 'Doctor' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $prescription->medication_name }}</strong>
                                        @if($prescription->instructions)
                                            <br><small class="text-muted">{{ Str::limit($prescription->instructions, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $prescription->dosage }}</td>
                                    <td>{{ $prescription->frequency }}</td>
                                    <td>{{ $prescription->duration }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($prescription->status) {
                                                'active' => 'success',
                                                'pending' => 'warning',
                                                'expired' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($prescription->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#prescriptionModal{{ $prescription->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($prescription->isActive())
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printPrescription({{ $prescription->id }})">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $prescriptions->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-prescription-bottle fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Prescriptions Found</h5>
                    <p class="text-muted">You do not have any prescriptions yet.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Prescription Detail Modals -->
    @foreach($prescriptions as $prescription)
        <div class="modal fade" id="prescriptionModal{{ $prescription->id }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-centered" style="z-index: 1055;">
                <div class="modal-content" style="position: relative; z-index: 1056;">
                    <div class="modal-header">
                        <h5 class="modal-title">Prescription Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Basic Information</h6>
                                <p><strong>Prescription #:</strong> {{ $prescription->prescription_number ?? 'N/A' }}</p>
                                <p><strong>Date:</strong> {{ $prescription->created_at->format('M d, Y g:i A') }}</p>
                                <p><strong>Doctor:</strong> {{ $prescription->doctor->full_name }}</p>
                                <p><strong>Status:</strong> 
                                    @php
                                        $badgeClass = match($prescription->status) {
                                            'active' => 'success',
                                            'pending' => 'warning',
                                            'expired' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($prescription->status) }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Medication Details</h6>
                                <p><strong>Medication:</strong> {{ $prescription->medication_name }}</p>
                                <p><strong>Dosage:</strong> {{ $prescription->dosage }}</p>
                                <p><strong>Frequency:</strong> {{ $prescription->frequency }}</p>
                                <p><strong>Duration:</strong> {{ $prescription->duration }}</p>
                                @if($prescription->quantity)
                                    <p><strong>Quantity:</strong> {{ $prescription->quantity }}</p>
                                @endif
                                @if($prescription->refills_allowed)
                                    <p><strong>Refills Allowed:</strong> {{ $prescription->refills_allowed }}</p>
                                @endif
                            </div>
                        </div>
                        
                        @if($prescription->instructions)
                            <div class="mt-3">
                                <h6 class="fw-bold">Instructions</h6>
                                <p class="text-muted">{{ $prescription->instructions }}</p>
                            </div>
                        @endif
                        
                        @if($prescription->side_effects)
                            <div class="mt-3">
                                <h6 class="fw-bold text-warning">Side Effects</h6>
                                <p class="text-muted">{{ $prescription->side_effects }}</p>
                            </div>
                        @endif
                        
                        @if($prescription->precautions)
                            <div class="mt-3">
                                <h6 class="fw-bold text-danger">Precautions</h6>
                                <p class="text-muted">{{ $prescription->precautions }}</p>
                            </div>
                        @endif
                        
                        @if($prescription->notes)
                            <div class="mt-3">
                                <h6 class="fw-bold">Notes</h6>
                                <p class="text-muted">{{ $prescription->notes }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer" style="position: relative; z-index: 1050;">
                        @if($prescription->isActive())
                            <button type="button" class="btn btn-secondary" onclick="printPrescription({{ $prescription->id }})" style="position: relative; z-index: 1051;">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        @endif
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="position: relative; z-index: 1051;">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('styles')
<style>
@media print {
    /* Hide non-essential elements when printing */
    .sidebar,
    .top-navbar,
    .btn,
    .filter-card,
    .pagination,
    .modal-backdrop,
    .modal {
        display: none !important;
    }
    
    /* Adjust main content for printing */
    .main-content {
        margin-left: 0 !important;
    }
    
    .content-area {
        padding: 20px !important;
    }
    
    /* Ensure table fits on page */
    .table {
        font-size: 12px;
    }
    
    .table th,
    .table td {
        padding: 8px 4px !important;
        border: 1px solid #000 !important;
    }
    
    .table thead th {
        background: #f8f9fa !important;
        color: #000 !important;
    }
    
    /* Page breaks */
    .card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
    
    .prescription-print-header {
        display: block !important;
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }
    
    .prescription-print-footer {
        display: block !important;
        margin-top: 20px;
        text-align: center;
        font-size: 10px;
        border-top: 1px solid #000;
        padding-top: 10px;
    }
}

/* Print header and footer - hidden by default */
.prescription-print-header,
.prescription-print-footer {
    display: none;
}
</style>
@endpush

@push('scripts')
<script>
// Print individual prescription
function printPrescription(prescriptionId) {
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    
    // Get prescription data from the modal
    const modal = document.getElementById('prescriptionModal' + prescriptionId);
    const modalBody = modal.querySelector('.modal-body').innerHTML;
    const modalTitle = modal.querySelector('.modal-title').textContent;
    
    // Get hospital info
    const hospitalName = '{{ $siteSettings["hospital_name"] ?? config("app.name", "Hospital Management") }}';
    const patientName = '{{ Auth::guard("patient")->user()->full_name }}';
    const patientId = '{{ Auth::guard("patient")->user()->patient_id }}';
    
    // Create print content
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Prescription - ${modalTitle}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    line-height: 1.6;
                }
                .prescription-header {
                    text-align: center;
                    border-bottom: 2px solid #000;
                    padding-bottom: 15px;
                    margin-bottom: 20px;
                }
                .hospital-name {
                    font-size: 24px;
                    font-weight: bold;
                    color: #1a1a2e;
                }
                .prescription-title {
                    font-size: 18px;
                    margin: 10px 0;
                }
                .patient-info {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 20px;
                }
                .prescription-content {
                    margin-bottom: 20px;
                }
                .prescription-footer {
                    border-top: 1px solid #ccc;
                    padding-top: 15px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                }
                h6 {
                    color: #1a1a2e;
                    border-bottom: 1px solid #e9ecef;
                    padding-bottom: 5px;
                }
                .badge {
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 12px;
                }
                .bg-success { background: #28a745; color: white; }
                .bg-warning { background: #ffc107; color: black; }
                .bg-danger { background: #dc3545; color: white; }
                .bg-secondary { background: #6c757d; color: white; }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="prescription-header">
                <div class="hospital-name">${hospitalName}</div>
                <div class="prescription-title">Prescription Details</div>
            </div>
            
            <div class="patient-info">
                <strong>Patient:</strong> ${patientName} (ID: ${patientId})<br>
                <strong>Print Date:</strong> ${new Date().toLocaleDateString()}
            </div>
            
            <div class="prescription-content">
                ${modalBody}
            </div>
            
            <div class="prescription-footer">
                <p>This is a computer-generated prescription printout.</p>
                <p>Printed on: ${new Date().toLocaleString()}</p>
            </div>
            
            <div class="no-print" style="text-align: center; margin-top: 20px;">
                <button onclick="window.print(); return false;" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Print Now</button>
                <button onclick="window.close(); return false;" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">Close</button>
            </div>
        </body>
        </html>
    `;
    
    // Write content and print
    printWindow.document.write(printContent);
    printWindow.document.close();
    
    // Auto-print after content loads
    printWindow.onload = function() {
        setTimeout(() => {
            printWindow.print();
        }, 500);
    };
}

// Print entire prescriptions table
function printPrescriptionsTable() {
    // Add print header and footer
    const printHeader = document.createElement('div');
    printHeader.className = 'prescription-print-header';
    printHeader.innerHTML = `
        <h1>{{ $siteSettings["hospital_name"] ?? config("app.name", "Hospital Management") }}</h1>
        <h2>Patient Prescriptions Report</h2>
        <p><strong>Patient:</strong> {{ Auth::guard('patient')->user()->full_name }} (ID: {{ Auth::guard('patient')->user()->patient_id }})</p>
        <p><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
    `;
    
    const printFooter = document.createElement('div');
    printFooter.className = 'prescription-print-footer';
    printFooter.innerHTML = `
        <p>This is a computer-generated prescriptions report.</p>
        <p>Total Prescriptions: {{ $prescriptions->total() }} | Printed: ${new Date().toLocaleString()}</p>
    `;
    
    // Insert print elements
    const contentArea = document.querySelector('.content-area');
    contentArea.insertBefore(printHeader, contentArea.firstChild);
    contentArea.appendChild(printFooter);
    
    // Hide filter card temporarily
    const filterCard = document.querySelector('.card:nth-child(2)');
    filterCard.classList.add('filter-card');
    
    // Print
    window.print();
    
    // Clean up after printing
    setTimeout(() => {
        printHeader.remove();
        printFooter.remove();
        filterCard.classList.remove('filter-card');
    }, 1000);
}

// Print selected prescriptions
function printSelected() {
    const checkboxes = document.querySelectorAll('.prescription-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one prescription to print.');
        return;
    }
    
    const selectedIds = Array.from(checkboxes).map(cb => cb.value);
    
    // Create print content for selected prescriptions
    let printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Selected Prescriptions</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
                .prescription-item { margin-bottom: 30px; border: 1px solid #ccc; padding: 15px; }
                h6 { color: #1a1a2e; }
                .badge { padding: 4px 8px; border-radius: 4px; }
                .bg-success { background: #28a745; color: white; }
                .bg-warning { background: #ffc107; }
                .bg-danger { background: #dc3545; color: white; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>{{ $siteSettings["hospital_name"] ?? config("app.name", "Hospital Management") }}</h1>
                <h2>Selected Prescriptions</h2>
                <p>Patient: {{ Auth::guard('patient')->user()->full_name }} (ID: {{ Auth::guard('patient')->user()->patient_id }})</p>
            </div>
    `;
    
    selectedIds.forEach(id => {
        const modal = document.getElementById('prescriptionModal' + id);
        const modalBody = modal.querySelector('.modal-body').innerHTML;
        printContent += `<div class="prescription-item">${modalBody}</div>`;
    });
    
    printContent += `
            <div class="no-print" style="text-align: center; margin-top: 20px;">
                <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px;">Print</button>
                <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; margin-left: 10px;">Close</button>
            </div>
        </body>
        </html>
    `;
    
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.onload = () => setTimeout(() => printWindow.print(), 500);
}
</script>
@endpush

