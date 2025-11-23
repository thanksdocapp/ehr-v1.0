<!-- Default Dashboard Content -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Welcome to ThanksDoc EPR</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="fas fa-hospital fa-5x text-gray-300 mb-3"></i>
                    <h4 class="text-gray-800">{{ $data['message'] ?? 'Welcome to the ThanksDoc EPR!' }}</h4>
                    <p class="text-gray-500">Your dashboard is being prepared. Please contact your administrator if you need specific access permissions.</p>
                </div>
                
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Note:</strong> Your role may not have a specific dashboard configured yet. Please check with your system administrator.
                </div>
            </div>
        </div>
    </div>
</div>
