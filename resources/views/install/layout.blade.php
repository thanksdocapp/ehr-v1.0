<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ThanksDoc EHR Installation')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #16a34a;
            --warning-color: #ca8a04;
            --danger-color: #dc2626;
            --light-bg: #f8fafc;
            --dark-text: #1e293b;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Figtree', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .install-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .install-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
        }
        
        .install-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .install-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.5rem;
            font-weight: 600;
        }
        
        .install-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .install-progress {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .progress-step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .progress-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
            z-index: 1;
        }
        
        .progress-step.active:not(:last-child)::after,
        .progress-step.completed:not(:last-child)::after {
            background: rgba(255, 255, 255, 0.8);
        }
        
        .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            position: relative;
            z-index: 2;
        }
        
        .progress-step.active .step-circle {
            background: white;
            color: var(--primary-color);
        }
        
        .progress-step.completed .step-circle {
            background: var(--success-color);
            color: white;
        }
        
        .step-label {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        .progress-step.active .step-label {
            opacity: 1;
            font-weight: 600;
        }
        
        .install-content {
            padding: 2.5rem;
        }
        
        .step-title {
            color: var(--dark-text);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
            outline: none;
        }
        
        .btn {
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-warning {
            background: var(--warning-color);
            border-color: var(--warning-color);
        }
        
        .btn-danger {
            background: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .requirement-item,
        .permission-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        
        .requirement-name {
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .requirement-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-badge.success {
            background: #dcfce7;
            color: var(--success-color);
        }
        
        .status-badge.danger {
            background: #fef2f2;
            color: var(--danger-color);
        }
        
        .install-footer {
            background: #f8fafc;
            padding: 1.5rem 2.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .loading-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            max-width: 400px;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .text-muted {
            color: #6b7280 !important;
        }
        
        .fw-bold {
            font-weight: 600 !important;
        }
        
        @media (max-width: 768px) {
            .install-header h1 {
                font-size: 2rem;
            }
            
            .install-content {
                padding: 1.5rem;
            }
            
            .install-footer {
                padding: 1rem 1.5rem;
                flex-direction: column;
                gap: 1rem;
            }
            
            .progress-steps {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .progress-step {
                min-width: calc(50% - 0.25rem);
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <!-- Header -->
            <div class="install-header">
                <h1>
                    <i class="fas fa-hospital me-3"></i>
                    ThanksDoc EHR
                </h1>
                <p>Professional Healthcare Solution Installation Wizard</p>
                
                @if(isset($steps) && isset($step))
                <div class="install-progress">
                    <div class="progress-steps">
                        @php
                            $stepKeys = array_keys($steps);
                            $currentIndex = array_search($step, $stepKeys);
                        @endphp
                        
                        @foreach($steps as $stepKey => $stepName)
                            @php
                                $stepIndex = array_search($stepKey, $stepKeys);
                                $isCompleted = $stepIndex < $currentIndex;
                                $isCurrent = $stepKey === $step;
                            @endphp
                            
                            <div class="progress-step {{ $isCurrent ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }}">
                                <div class="step-circle">
                                    @if($isCompleted)
                                        <i class="fas fa-check"></i>
                                    @else
                                        {{ $stepIndex + 1 }}
                                    @endif
                                </div>
                                <div class="step-label">{{ $stepName }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Content -->
            <div class="install-content">
                @yield('content')
            </div>
            
            <!-- Footer -->
            @hasSection('footer')
                <div class="install-footer">
                    @yield('footer')
                </div>
            @endif
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h5 class="mb-2">Processing...</h5>
            <p class="text-muted mb-0">Please wait while we configure your system.</p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Loading overlay functions
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }
        
        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }
        
        // Form submission with loading
        function submitForm(form, callback) {
            showLoading();
            
            const formData = new FormData(form);
            const url = form.action;
            
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (callback) callback(data);
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
        
        // Alert helpers
        function showAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const container = document.querySelector('.install-content');
            container.insertAdjacentHTML('afterbegin', alertHtml);
        }
        
        // Remove existing alerts
        function clearAlerts() {
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
        }
    </script>
    
    @stack('scripts')
</body>
</html>
