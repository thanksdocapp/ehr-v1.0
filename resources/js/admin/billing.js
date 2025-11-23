function showSyncDetails(billingId) {
    fetch(`/admin/billing/${billingId}/sync-details`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('syncDetailsContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Billing Information</h6>
                        <p><strong>Bill Number:</strong> ${data.bill_number}</p>
                        <p><strong>Patient:</strong> ${data.patient_name}</p>
                        <p><strong>Total Amount:</strong> $${data.total_amount}</p>
                        <p><strong>Status:</strong> <span class="badge bg-info">${data.status}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success">Patient Portal Sync</h6>
                        <p><strong>Invoice Number:</strong> ${data.invoice_number}</p>
                        <p><strong>Invoice Status:</strong> <span class="badge bg-success">${data.invoice_status}</span></p>
                        <p><strong>Payments Made:</strong> $${data.payments_made}</p>
                        <p><strong>Last Payment:</strong> ${data.last_payment_date || 'N/A'}</p>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <small class="text-muted">Sync Status: <i class="fas fa-check-circle text-success"></i> Synchronized</small>
                </div>
            `;
            const modal = new bootstrap.Modal(document.getElementById('syncDetailsModal'));
            modal.show();
        })
        .catch(error => console.error('Error fetching sync details:', error));
}

function manualSync(billingId) {
    if (confirm('Manually sync this bill with the patient portal?')) {
        fetch(`/admin/billing/${billingId}/manual-sync`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Manual sync successful for billingId:', billingId);
                refreshTable();
            } else {
                alert('Sync failed: ' + data.message);
            }
        })
        .catch(error => console.error('Error during manual sync:', error));
    }
}

function forceResync() {
    const billingId = document.querySelector('.modal-title').dataset.billingId;
    manualSync(billingId);
}
