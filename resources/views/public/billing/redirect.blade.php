<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Redirecting to Payment...</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: white;
        }
        .redirect-container {
            text-align: center;
            padding: 2rem;
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1.5rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            margin: 0 0 1rem;
            font-size: 1.5rem;
        }
        p {
            margin: 0.5rem 0;
            opacity: 0.9;
        }
        .redirect-link {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .redirect-link:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="redirect-container">
        <div class="spinner"></div>
        <h2>Redirecting to Secure Payment...</h2>
        <p>Please wait while we redirect you to the payment gateway.</p>
        <p>If you are not redirected automatically, click the button below.</p>
        <a href="{{ $redirect_url }}" class="redirect-link" id="redirect-link">
            Continue to Payment
        </a>
    </div>

    <script>
        // Immediate redirect
        const redirectUrl = @json($redirect_url);
        
        console.log('Redirecting to:', redirectUrl);
        
        // Try multiple redirect methods
        if (redirectUrl) {
            // Method 1: window.location (most reliable)
            window.location.href = redirectUrl;
            
            // Method 2: window.location.replace (fallback)
            setTimeout(function() {
                if (document.hasFocus()) {
                    window.location.replace(redirectUrl);
                }
            }, 100);
            
            // Method 3: Click the link programmatically (fallback)
            setTimeout(function() {
                const link = document.getElementById('redirect-link');
                if (link) {
                    link.click();
                }
            }, 500);
        } else {
            console.error('No redirect URL provided');
            document.querySelector('.redirect-container').innerHTML = 
                '<h2>Error</h2><p>No payment URL available. Please contact support.</p>';
        }
    </script>
</body>
</html>

