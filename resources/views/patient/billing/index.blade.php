@extends('patient.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Billing & Payments')
@section('page-title', 'Billing & Payments')

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary-gradient">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stat-number text-primary">{{ $stats['total_invoices'] }}</div>
                <div class="stat-label">Total Invoices</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-success-gradient">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number text-success">{{ $stats['paid_invoices'] }}</div>
                <div class="stat-label">Paid Invoices</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-warning-gradient">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number text-warning">{{ $stats['pending_invoices'] }}</div>
                <div class="stat-label">Pending Invoices</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-danger-gradient">
                    <i class="fas fa-pound-sign"></i>
                </div>
                <div class="stat-number text-danger">{{ CurrencyHelper::format($stats['outstanding_amount']) }}</div>
                <div class="stat-label">Outstanding Amount</div>
            </div>
        </div>
    </div>

    <!-- Outstanding Balance Alert -->
    @if($stats['outstanding_amount'] > 0)
        <div class="alert alert-warning border-0 shadow-sm mb-4 payment-alert">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="alert-heading mb-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Outstanding Balance
                    </h5>
                    <p class="mb-1">
                        You have an outstanding balance of <strong>{{ CurrencyHelper::format($stats['outstanding_amount']) }}</strong>.
                    </p>
                    @if($stats['overdue_invoices'] > 0)
                        <p class="mb-0 text-danger">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            {{ $stats['overdue_invoices'] }} invoice(s) are overdue.
                        </p>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-warning" onclick="payAllOutstanding()">
                        <i class="fas fa-credit-card me-1"></i>
                        Pay Now
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>
                Filter Invoices
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('patient.billing.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partially Paid</option>
                                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Invoice number..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('patient.billing.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice me-2"></i>
                    Invoices
                </h5>
                <div class="btn-group">
                    <a href="#" class="btn btn-outline-light">
                        <i class="fas fa-credit-card me-1"></i>
                        Payment History
                    </a>
                    <button type="button" class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-1"></i>
                        Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>Export PDF</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Export Excel</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $invoice->invoice_number }}</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $invoice->invoice_date->format('M d, Y') }}</strong>
                                            <small class="text-muted">{{ $invoice->invoice_date->format('g:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $invoice->description ?? 'Medical Services' }}</strong>
                                            @if($invoice->appointment)
                                                <small class="text-muted">Appointment: {{ $invoice->appointment->appointment_date->format('M d, Y') }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong class="text-dark">{{ CurrencyHelper::format($invoice->total_amount) }}</strong>
                                            @if($invoice->payments->count() > 0)
                                                <small class="text-success">
                                                    Paid: {{ CurrencyHelper::format($invoice->payments->where('status', 'completed')->sum('amount')) }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong class="{{ $invoice->due_date->lt(today()) && $invoice->status !== 'paid' ? 'text-danger' : '' }}">
                                                {{ $invoice->due_date->format('M d, Y') }}
                                            </strong>
                                            @if($invoice->due_date->lt(today()) && $invoice->status !== 'paid')
                                                <small class="text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Overdue
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($invoice->status) {
                                                'paid' => 'bg-success',
                                                'pending' => $invoice->due_date->lt(today()) ? 'bg-danger' : 'bg-warning',
                                                'partial' => 'bg-info',
                                                default => 'bg-secondary'
                                            };
                                            $statusText = match($invoice->status) {
                                                'paid' => 'Paid',
                                                'pending' => $invoice->due_date->lt(today()) ? 'Overdue' : 'Pending',
                                                'partial' => 'Partial',
                                                default => ucfirst($invoice->status)
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('patient.billing.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($invoice->status !== 'paid')
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="payInvoice({{ $invoice->id }})">
                                                    <i class="fas fa-credit-card"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="downloadInvoice({{ $invoice->id }})">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $invoices->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Invoices Found</h5>
                    <p class="text-muted">You don't have any invoices yet.</p>
                    <a href="{{ route('patient.appointments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Book Appointment
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="row mt-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Payment Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stat-icon-sm bg-success me-3">
                                    <i class="fas fa-check text-white"></i>
                                </div>
                                <div>
                                    <div class="text-muted">Total Paid</div>
                                    <div class="h5 mb-0">{{ CurrencyHelper::format($stats['paid_amount']) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stat-icon-sm bg-warning me-3">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                                <div>
                                    <div class="text-muted">Outstanding</div>
                                    <div class="h5 mb-0">{{ CurrencyHelper::format($stats['outstanding_amount']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        @php
                            $paidPercentage = $stats['total_amount'] > 0 ? ($stats['paid_amount'] / $stats['total_amount'] * 100) : 0;
                        @endphp
                        <div class="progress-bar bg-success" style="width: {{ $paidPercentage }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">{{ number_format($paidPercentage, 1) }}% Paid</small>
                        <small class="text-muted">Total: {{ CurrencyHelper::format($stats['total_amount']) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Payment Information
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">Accepted Payment Methods:</h6>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <i class="fas fa-credit-card text-primary me-2"></i>
                            Credit Cards
                        </div>
                        <div class="col-6 mb-2">
                            <i class="fas fa-university text-info me-2"></i>
                            Bank Transfer
                        </div>
                        <div class="col-6 mb-2">
                            <i class="fas fa-money-bill text-success me-2"></i>
                            Cash
                        </div>
                        <div class="col-6 mb-2">
                            <i class="fas fa-shield-alt text-warning me-2"></i>
                            Insurance
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            For payment assistance or questions about your bill, please contact our billing department at (555) 123-4567.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Pre-generate route templates on server side
    window.billingRoutes = {
        paymentTemplate: '{{ route("patient.billing.pay", "PLACEHOLDER_ID") }}',
        downloadTemplate: '{{ route("patient.billing.download", "PLACEHOLDER_ID") }}',
        unpaidInvoices: @json($invoices->where('status', '!=', 'paid')->pluck('id')->toArray())
    };
    
    function payInvoice(invoiceId) {
        try {
            console.log('payInvoice called with ID:', invoiceId);
            
            if (!invoiceId) {
                console.error('No invoice ID provided');
                alert('Error: Invalid invoice ID');
                return;
            }
            
            // Generate payment URL
            const paymentUrl = window.billingRoutes.paymentTemplate.replace('PLACEHOLDER_ID', invoiceId);
            console.log('Redirecting to:', paymentUrl);
            
            // Redirect to payment page
            window.location.href = paymentUrl;
        } catch (error) {
            console.error('Error in payInvoice:', error);
            alert('An error occurred while processing your request. Please try again.');
        }
    }

    function downloadInvoice(invoiceId) {
        try {
            console.log('downloadInvoice called with ID:', invoiceId);
            
            if (!invoiceId) {
                console.error('No invoice ID provided');
                alert('Error: Invalid invoice ID');
                return;
            }
            
            // Generate download URL
            const downloadUrl = window.billingRoutes.downloadTemplate.replace('PLACEHOLDER_ID', invoiceId);
            console.log('Redirecting to:', downloadUrl);
            
            // Redirect to download page
            window.location.href = downloadUrl;
        } catch (error) {
            console.error('Error in downloadInvoice:', error);
            alert('An error occurred while processing your request. Please try again.');
        }
    }
    
    function payAllOutstanding() {
        try {
            console.log('payAllOutstanding called');
            
            const unpaidInvoices = window.billingRoutes.unpaidInvoices;
            console.log('Unpaid invoices:', unpaidInvoices);
            
            if (unpaidInvoices && unpaidInvoices.length > 0) {
                console.log('Paying first unpaid invoice:', unpaidInvoices[0]);
                payInvoice(unpaidInvoices[0]);
            } else {
                console.log('No unpaid invoices found');
                alert('No outstanding invoices to pay.');
            }
        } catch (error) {
            console.error('Error in payAllOutstanding:', error);
            alert('An error occurred while processing your request. Please try again.');
        }
    }

    // Test function to verify JavaScript is working
    function testJavaScript() {
        console.log('JavaScript test function called');
        console.log('Routes available:', window.billingRoutes);
        alert('JavaScript is working! Check the console for route information.');
    }

    // Enhanced page load handler
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Patient billing page loaded');
        console.log('Available routes:', window.billingRoutes);
        
        // Add click event listeners to all payment buttons as backup
        const paymentButtons = document.querySelectorAll('button[onclick*="payInvoice"]');
        console.log('Found payment buttons:', paymentButtons.length);
        
        paymentButtons.forEach(function(button, index) {
            console.log('Payment button', index, ':', button);
            
            // Extract invoice ID from onclick attribute as fallback
            const onclickValue = button.getAttribute('onclick');
            if (onclickValue) {
                const match = onclickValue.match(/payInvoice\((\d+)\)/);
                if (match) {
                    const invoiceId = match[1];
                    console.log('Button', index, 'invoice ID:', invoiceId);
                    
                    // Add backup event listener
                    button.addEventListener('click', function(e) {
                        console.log('Backup click handler for invoice:', invoiceId);
                        e.preventDefault();
                        e.stopPropagation();
                        payInvoice(invoiceId);
                    });
                }
            }
        });
        
        // Add click event listeners to all download buttons as backup
        const downloadButtons = document.querySelectorAll('button[onclick*="downloadInvoice"]');
        console.log('Found download buttons:', downloadButtons.length);
        
        downloadButtons.forEach(function(button, index) {
            console.log('Download button', index, ':', button);
            
            // Extract invoice ID from onclick attribute as fallback
            const onclickValue = button.getAttribute('onclick');
            if (onclickValue) {
                const match = onclickValue.match(/downloadInvoice\((\d+)\)/);
                if (match) {
                    const invoiceId = match[1];
                    console.log('Button', index, 'invoice ID:', invoiceId);
                    
                    // Add backup event listener
                    button.addEventListener('click', function(e) {
                        console.log('Backup click handler for download invoice:', invoiceId);
                        e.preventDefault();
                        e.stopPropagation();
                        downloadInvoice(invoiceId);
                    });
                }
            }
        });
        
        // Add event listener for "Pay All Outstanding" button
        const payAllButton = document.querySelector('button[onclick="payAllOutstanding()"]');
        if (payAllButton) {
            console.log('Found Pay All Outstanding button');
            payAllButton.addEventListener('click', function(e) {
                console.log('Backup click handler for Pay All Outstanding');
                e.preventDefault();
                e.stopPropagation();
                payAllOutstanding();
            });
        }
        
        console.log('All event listeners attached successfully');
    });
    
    // Add a test button for debugging (temporary)
    console.log('JavaScript loaded successfully. Call testJavaScript() to test.');
</script>
@endpush

@push('styles')
<style>
    .stat-icon-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush
