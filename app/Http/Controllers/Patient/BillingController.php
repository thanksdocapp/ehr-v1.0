<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Carbon\Carbon;

class BillingController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the patient's invoices.
     */
    public function index(Request $request): View
    {
        $patient = Auth::guard('patient')->user();
        
        $query = Invoice::where('patient_id', $patient->id)
            ->with(['appointment', 'payments']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        // Search by invoice number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(10);

        // Calculate statistics
        $stats = [
            'total_invoices' => Invoice::where('patient_id', $patient->id)->count(),
            'paid_invoices' => Invoice::where('patient_id', $patient->id)->where('status', 'paid')->count(),
            'pending_invoices' => Invoice::where('patient_id', $patient->id)->where('status', 'pending')->count(),
            'overdue_invoices' => Invoice::where('patient_id', $patient->id)
                ->where('status', 'pending')
                ->where('due_date', '<', today())
                ->count(),
            'total_amount' => Invoice::where('patient_id', $patient->id)->sum('total_amount'),
            'paid_amount' => Invoice::where('patient_id', $patient->id)->where('status', 'paid')->sum('total_amount'),
            'outstanding_amount' => Invoice::where('patient_id', $patient->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->sum('total_amount'),
        ];

        return view('patient.billing.index', compact('invoices', 'stats'));
    }

    /**
     * Display the specified invoice.
     */
    public function show(Request $request, Invoice $invoice): View
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the invoice belongs to the authenticated patient
        if ($invoice->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to invoice.');
        }

        // Handle Stripe payment success callback
        if ($request->has('payment_success') && $request->has('session_id')) {
            $this->handleStripePaymentSuccess($request->session_id, $invoice);
        }

        $invoice->load(['appointment', 'payments', 'invoiceItems']);

        return view('patient.billing.show', compact('invoice'));
    }

    /**
     * Display payment history.
     */
    public function payments(Request $request): View
    {
        $patient = Auth::guard('patient')->user();
        
        $query = Payment::whereHas('invoice', function ($q) use ($patient) {
            $q->where('patient_id', $patient->id);
        })->with(['invoice']);

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(10);

        $paymentStats = [
            'total_payments' => Payment::whereHas('invoice', function ($q) use ($patient) {
                $q->where('patient_id', $patient->id);
            })->count(),
            'successful_payments' => Payment::whereHas('invoice', function ($q) use ($patient) {
                $q->where('patient_id', $patient->id);
            })->where('status', 'completed')->count(),
            'total_paid' => Payment::whereHas('invoice', function ($q) use ($patient) {
                $q->where('patient_id', $patient->id);
            })->where('status', 'completed')->sum('amount'),
        ];

        return view('patient.billing.payments', compact('payments', 'paymentStats'));
    }

    /**
     * Show gateway selection for an invoice payment.
     */
    public function selectGateway(Invoice $invoice): View
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the invoice belongs to the authenticated patient
        if ($invoice->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to invoice.');
        }

        // Check if invoice is payable
        if ($invoice->status === 'paid') {
            return redirect()->route('patient.billing.show', $invoice)
                ->with('error', 'This invoice has already been paid.');
        }

        // Get available payment gateways
        $gateways = \App\Models\PaymentGateway::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('payment.select_gateway', compact('invoice', 'gateways'));
    }

    /**
     * Show payment form with selected gateway.
     */
    public function showPaymentForm(Request $request, Invoice $invoice): View
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the invoice belongs to the authenticated patient
        if ($invoice->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to invoice.');
        }

        // Validate selected gateway
        $request->validate([
            'payment_gateway' => 'required|string|exists:payment_gateways,provider'
        ]);

        // Get the selected gateway
        $selectedGateway = \App\Models\PaymentGateway::where('provider', $request->payment_gateway)
            ->where('is_active', true)
            ->firstOrFail();

        $invoice->load(['invoiceItems']);

        return view('patient.billing.payment-form', compact('invoice', 'selectedGateway'));
    }

    /**
     * Process payment for an invoice.
     */
    public function processPayment(Request $request, Invoice $invoice)
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the invoice belongs to the authenticated patient
        if ($invoice->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $request->validate([
            'payment_gateway' => 'required|string|exists:payment_gateways,provider',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->outstanding_amount,
        ]);

        // Get the selected gateway
        $gateway = \App\Models\PaymentGateway::where('provider', $request->payment_gateway)
            ->where('is_active', true)
            ->firstOrFail();

        // For now, we'll create a basic payment record
        // In a real implementation, you would integrate with the actual payment gateway APIs
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'payment_date' => now(),
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_gateway' => $request->payment_gateway,
            'gateway_transaction_id' => null, // Will be populated by actual gateway
            'status' => 'pending', // Start as pending, will be updated by gateway callback
            'transaction_id' => $this->generateTransactionId(),
            'notes' => $request->notes,
        ]);

        // Handle different gateway types
        switch ($request->payment_gateway) {
            case 'stripe':
                return $this->processStripePayment($payment, $request, $invoice, $gateway);
            case 'paypal':
                return $this->processPayPalPayment($payment, $request, $invoice, $gateway);
            case 'paystack':
                return $this->processPaystackPayment($payment, $request, $invoice, $gateway);
            case 'flutterwave':
                return $this->processFlutterwavePayment($payment, $request, $invoice, $gateway);
            case 'btcpay':
            case 'coingate':
                return $this->processCryptoPayment($payment, $request, $invoice, $gateway);
            default:
                // For basic gateways, mark as completed for now
                $payment->update(['status' => 'completed']);
                $this->updateInvoiceStatus($invoice);
                return redirect()->route('patient.billing.show', $invoice)
                    ->with('success', 'Payment processed successfully!');
        }
    }

    /**
     * Process Stripe payment.
     */
    private function processStripePayment($payment, $request, $invoice, $gateway)
    {
        $patient = Auth::guard('patient')->user();
        
        // Utilize the StripeGateway to create a checkout session and redirect to Stripe
        $stripeGateway = new \App\Services\PaymentGateway\StripeGateway();
        $credentials = array_merge($gateway->credentials ?? [], [
            'test_mode' => $gateway->test_mode
        ]);
        $stripeGateway->initialize($credentials);
        
        $response = $stripeGateway->createCheckoutSession([
            'amount' => $payment->amount,
            'currency' => 'usd',
            'order_id' => $payment->transaction_id,
            'customer_email' => $patient->email,
            'description' => 'Invoice #' . $invoice->id,
            'success_url' => route('patient.billing.show', $invoice) . '?payment_success=1',
            'cancel_url' => route('patient.billing.show', $invoice) . '?payment_cancelled=1',
            'is_patient_payment' => true, // Mark as patient payment to use correct route
        ]);

        if (!$response['success']) {
            return back()->withErrors(['error' => 'Failed to create Stripe checkout session: ' . $response['error']]);
        }

        // Update payment with transaction details
        \Log::info('Updating payment with gateway response', [
            'payment_id' => $payment->id,
            'gateway_transaction_id' => $response['payment_id'],
            'gateway_response' => $response['gateway_response'] ?? [],
            'full_response' => $response
        ]);
        
        $payment->update([
            'gateway_transaction_id' => $response['payment_id'],
            'status' => 'pending',
            'gateway_response' => $response['gateway_response'] ?? [],
        ]);
        
        // Verify the update worked
        $payment->refresh();
        \Log::info('Payment after update', [
            'gateway_response' => $payment->gateway_response,
            'gateway_transaction_id' => $payment->gateway_transaction_id
        ]);

        return redirect()->to($response['payment_url']);
    }

    /**
     * Process PayPal payment.
     */
    private function processPayPalPayment($payment, $request, $invoice, $gateway)
    {
        $patient = Auth::guard('patient')->user();
        
        // Initialize PayPal Gateway
        $paypalGateway = new \App\Services\PaymentGateway\PayPalGateway();
        $credentials = array_merge($gateway->credentials ?? [], [
            'test_mode' => $gateway->test_mode
        ]);
        $paypalGateway->initialize($credentials);
        
        // Create PayPal payment
        $response = $paypalGateway->createPayment([
            'amount' => $payment->amount,
            'currency' => 'usd',
            'order_id' => $payment->transaction_id,
            'description' => 'Invoice #' . $invoice->id . ' - Medical Payment',
            'success_url' => route('patient.billing.paypal.success', ['payment' => $payment->id]),
            'cancel_url' => route('patient.billing.paypal.cancel', ['payment' => $payment->id]),
        ]);
        
        if (!$response['success']) {
            return back()->withErrors(['error' => 'Failed to create PayPal payment: ' . $response['error']]);
        }
        
        // Update payment with PayPal details
        $payment->update([
            'gateway_transaction_id' => $response['payment_id'],
            'status' => 'pending',
            'gateway_response' => $response['gateway_response'] ?? [],
        ]);
        
        // Redirect to PayPal for payment approval
        return redirect()->to($response['payment_url']);
    }

    /**
     * Process Paystack payment.
     */
    private function processPaystackPayment($payment, $request, $invoice, $gateway)
    {
        $patient = Auth::guard('patient')->user();
        
        // Initialize Paystack Gateway
        $paystackGateway = new \App\Services\PaymentGateway\PaystackGateway();
        $credentials = array_merge($gateway->credentials ?? [], [
            'test_mode' => $gateway->test_mode
        ]);
        $paystackGateway->initialize($credentials);
        
        // Determine the appropriate currency for Paystack
        $currency = $this->getPaystackCurrency($paystackGateway);
        
        // Log currency detection results
        \Log::info('BillingController Paystack currency detection', [
            'detected_currency' => $currency,
            'app_timezone' => config('app.timezone'),
            'php_timezone' => date_default_timezone_get(),
            'supported_currencies' => $paystackGateway->getSupportedCurrencies()
        ]);
        
        // Create Paystack payment
        $response = $paystackGateway->createPayment([
            'amount' => $payment->amount,
            'currency' => $currency,
            'order_id' => $payment->transaction_id,
            'customer_email' => $patient->email,
            'description' => 'Invoice #' . $invoice->invoice_number . ' - Medical Payment',
            'callback_url' => route('payment.webhook', ['provider' => 'paystack']),
            'success_url' => route('patient.billing.show', $invoice) . '?payment_success=1',
            'title' => 'Payment for ' . $invoice->invoice_number, // Adding missing title field
            'cancel_url' => route('patient.billing.show', $invoice) . '?payment_cancelled=1',
        ]);
        
        if (!$response['success']) {
            return back()->withErrors(['error' => 'Failed to create Paystack payment: ' . $response['error']]);
        }
        
        // Update payment with Paystack details
        $payment->update([
            'gateway_transaction_id' => $response['payment_id'],
            'status' => 'pending',
            'gateway_response' => $response['gateway_response'] ?? [],
        ]);
        
        // Redirect to Paystack for payment approval
        return redirect()->to($response['payment_url']);
    }

    /**
     * Process Flutterwave payment.
     */
    private function processFlutterwavePayment($payment, $request, $invoice, $gateway)
    {
        $patient = Auth::guard('patient')->user();
        
        // Initialize Flutterwave Gateway
        $flutterwaveGateway = new \App\Services\PaymentGateway\FlutterwaveGateway();
        $credentials = array_merge($gateway->credentials ?? [], [
            'test_mode' => $gateway->test_mode
        ]);
        $flutterwaveGateway->initialize($credentials);
        
        // Determine the appropriate currency for Flutterwave
        $currency = $this->getFlutterwaveCurrency($flutterwaveGateway);
        
        // Log currency detection results
        \Log::info('BillingController Flutterwave currency detection', [
            'detected_currency' => $currency,
            'app_timezone' => config('app.timezone'),
            'php_timezone' => date_default_timezone_get(),
            'supported_currencies' => $flutterwaveGateway->getSupportedCurrencies()
        ]);
        
        // Create Flutterwave payment
        $response = $flutterwaveGateway->createPayment([
            'amount' => $payment->amount,
            'currency' => $currency,
            'order_id' => $payment->transaction_id,
            'customer_email' => $patient->email,
            'customer_name' => $patient->full_name ?? ($patient->first_name . ' ' . $patient->last_name),
            'description' => 'Invoice #' . $invoice->invoice_number . ' - Medical Payment',
            'title' => 'Payment for ' . $invoice->invoice_number,
            'success_url' => route('patient.billing.show', $invoice) . '?payment_success=1',
            'cancel_url' => route('patient.billing.show', $invoice) . '?payment_cancelled=1',
        ]);
        
        if (!$response['success']) {
            \Log::error('Flutterwave payment creation failed in BillingController', [
                'invoice_id' => $invoice->id,
                'patient_id' => $patient->id,
                'error' => $response['error'] ?? 'Unknown error',
                'gateway_response' => $response
            ]);
            
            // Try to redirect back, but fallback to invoice page if that fails
            return redirect()->route('patient.billing.show', $invoice)
                ->withErrors(['error' => 'Failed to create Flutterwave payment: ' . ($response['error'] ?? 'Unknown error')]);
        }
        
        // Update payment with Flutterwave details
        $payment->update([
            'gateway_transaction_id' => $response['payment_id'],
            'status' => 'pending',
            'gateway_response' => $response['gateway_response'] ?? [],
        ]);
        
        // Redirect to Flutterwave for payment approval
        return redirect()->to($response['payment_url']);
    }

    /**
     * Process cryptocurrency payment.
     */
    private function processCryptoPayment($payment, $request, $invoice, $gateway)
    {
        $patient = Auth::guard('patient')->user();
        
        try {
            // Handle different crypto gateways
            switch ($gateway->provider) {
                case 'coingate':
                    return $this->processCoinGatePayment($payment, $request, $invoice, $gateway);
                case 'btcpay':
                    return $this->processBTCPayPayment($payment, $request, $invoice, $gateway);
                default:
                    // For unknown crypto gateways, mark as pending for manual processing
                    $payment->update([
                        'status' => 'pending',
                        'gateway_transaction_id' => 'crypto_' . uniqid(),
                    ]);
                    
                    return redirect()->route('patient.billing.show', $invoice)
                        ->with('info', 'Payment initiated via ' . $gateway->display_name . '. Please complete the payment process.');
            }
        } catch (Exception $e) {
            \Log::error('Crypto payment processing failed', [
                'gateway' => $gateway->provider,
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('patient.billing.show', $invoice)
                ->withErrors(['error' => 'Failed to process ' . $gateway->display_name . ' payment: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Process CoinGate payment.
     */
    private function processCoinGatePayment($payment, $request, $invoice, $gateway)
    {
        $patient = Auth::guard('patient')->user();
        
        // Initialize CoinGate Gateway
        $coinGateGateway = new \App\Services\PaymentGateway\CoinGateGateway();
        $credentials = array_merge($gateway->credentials ?? [], [
            'test_mode' => $gateway->test_mode
        ]);
        $coinGateGateway->initialize($credentials);
        
        // Create CoinGate payment
        $response = $coinGateGateway->createPayment([
            'amount' => $payment->amount,
            'currency' => 'USD', // Base currency for the invoice
            'receive_currency' => $request->input('crypto_currency', 'BTC'), // Crypto currency to receive
            'order_id' => $payment->transaction_id,
            'customer_email' => $patient->email,
            'description' => 'Invoice #' . $invoice->invoice_number . ' - Medical Payment',
            'title' => 'Payment for ' . $invoice->invoice_number,
            'success_url' => route('payment.coingate.callback') . '?payment_id=' . $payment->id . '&invoice_id=' . $invoice->id,
            'cancel_url' => route('patient.billing.show', $invoice) . '?payment_cancelled=1',
            'callback_url' => route('payment.webhook', ['provider' => 'coingate']),
        ]);
        
        if (!$response['success']) {
            \Log::error('CoinGate payment creation failed in BillingController', [
                'invoice_id' => $invoice->id,
                'patient_id' => $patient->id,
                'error' => $response['error'] ?? 'Unknown error',
                'gateway_response' => $response
            ]);
            
            return redirect()->route('patient.billing.show', $invoice)
                ->withErrors(['error' => 'Failed to create CoinGate payment: ' . ($response['error'] ?? 'Unknown error')]);
        }
        
        // Update payment with CoinGate details
        $payment->update([
            'gateway_transaction_id' => $response['payment_id'],
            'status' => 'pending',
            'gateway_response' => $response['gateway_response'] ?? [],
        ]);
        
        // Redirect to CoinGate for payment approval
        return redirect()->to($response['payment_url']);
    }
    
    /**
     * Process BTCPay payment.
     */
    private function processBTCPayPayment($payment, $request, $invoice, $gateway)
    {
        $patient = Auth::guard('patient')->user();
        
        // Initialize BTCPay Gateway
        $btcpayGateway = new \App\Services\PaymentGateway\BTCPayGateway();
        $credentials = array_merge($gateway->credentials ?? [], [
            'test_mode' => $gateway->test_mode
        ]);
        $btcpayGateway->initialize($credentials);
        
        // Create BTCPay payment
        $response = $btcpayGateway->createPayment([
            'amount' => $payment->amount,
            'currency' => 'USD', // Base currency for the invoice
            'order_id' => $payment->transaction_id,
            'customer_email' => $patient->email,
            'description' => 'Invoice #' . $invoice->invoice_number . ' - Medical Payment',
            'title' => 'Payment for ' . $invoice->invoice_number,
            'success_url' => route('payment.btcpay.callback') . '?payment_id=' . $payment->id . '&invoice_id=' . $invoice->id,
            'cancel_url' => route('patient.billing.show', $invoice) . '?payment_cancelled=1',
            'callback_url' => route('payment.webhook', ['provider' => 'btcpay']),
        ]);
        
        if (!$response['success']) {
            \Log::error('BTCPay payment creation failed in BillingController', [
                'invoice_id' => $invoice->id,
                'patient_id' => $patient->id,
                'error' => $response['error'] ?? 'Unknown error',
                'gateway_response' => $response
            ]);
            
            return redirect()->route('patient.billing.show', $invoice)
                ->withErrors(['error' => 'Failed to create BTCPay payment: ' . ($response['error'] ?? 'Unknown error')]);
        }
        
        // Update payment with BTCPay details
        $payment->update([
            'gateway_transaction_id' => $response['payment_id'],
            'status' => 'pending',
            'gateway_response' => $response['gateway_response'] ?? [],
        ]);
        
        // For BTCPay, we need to open in a new tab and show a waiting page
        return view('patient.billing.btcpay-redirect', [
            'payment_url' => $response['payment_url'],
            'invoice' => $invoice,
            'payment' => $payment,
            'amount' => $payment->amount
        ]);
    }

    /**
     * Update invoice status based on payments.
     */
    private function updateInvoiceStatus($invoice)
    {
        $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
        
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);
        } else {
            $invoice->update(['status' => 'partial']);
        }
        
        // Sync back to admin billing system if this invoice is linked to a billing record
        $this->syncToAdminBilling($invoice, $totalPaid);
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadInvoice(Invoice $invoice)
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the invoice belongs to the authenticated patient
        if ($invoice->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $invoice->load(['appointment', 'invoiceItems', 'payments']);

        // You can implement PDF generation here using packages like dompdf or tcpdf
        // For now, we'll return a simple response
        return response()->json([
            'message' => 'PDF download functionality will be implemented',
            'invoice_id' => $invoice->id
        ]);
    }

    /**
     * Download payment receipt as PDF.
     */
    public function downloadReceipt(Payment $payment)
    {
        $patient = Auth::guard('patient')->user();
        $invoice = $payment->invoice;

        // Ensure the payment's invoice belongs to the authenticated patient
        if ($invoice->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to payment receipt.');
        }

        $payment->load(['invoice']);

        // You can implement PDF generation here
        return response()->json([
            'message' => 'PDF receipt download functionality will be implemented',
            'payment_id' => $payment->id
        ]);
    }

    /**
     * Get billing statistics for dashboard.
     */
    public function getStats()
    {
        $patient = Auth::guard('patient')->user();

        $stats = [
            'invoices' => [
                'total' => Invoice::where('patient_id', $patient->id)->count(),
                'pending' => Invoice::where('patient_id', $patient->id)->where('status', 'pending')->count(),
                'overdue' => Invoice::where('patient_id', $patient->id)
                    ->where('status', 'pending')
                    ->where('due_date', '<', today())
                    ->count(),
            ],
            'amounts' => [
                'total' => Invoice::where('patient_id', $patient->id)->sum('total_amount'),
                'outstanding' => Invoice::where('patient_id', $patient->id)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->sum('total_amount'),
                'paid_this_month' => Payment::whereHas('invoice', function ($q) use ($patient) {
                    $q->where('patient_id', $patient->id);
                })
                ->where('status', 'completed')
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Get payment status for AJAX requests (used by BTCPay status checking)
     */
    public function getPaymentStatus(Payment $payment)
    {
        $patient = Auth::guard('patient')->user();
        
        // Ensure the payment belongs to the authenticated patient
        if ($payment->invoice->patient_id !== $patient->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'id' => $payment->id,
            'status' => $payment->status,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'created_at' => $payment->created_at,
            'updated_at' => $payment->updated_at
        ]);
    }

    /**
     * Handle Stripe payment success callback
     */
    private function handleStripePaymentSuccess($sessionId, $invoice)
    {
        try {
            // Find the payment record by session ID
            $payment = Payment::where('gateway_transaction_id', $sessionId)
                ->where('invoice_id', $invoice->id)
                ->first();

            if (!$payment) {
                \Log::warning('Payment not found for Stripe session', [
                    'session_id' => $sessionId,
                    'invoice_id' => $invoice->id
                ]);
                return;
            }

            // Get the gateway to verify the session
            $gateway = \App\Models\PaymentGateway::where('provider', 'stripe')
                ->where('is_active', true)
                ->first();

            if (!$gateway) {
                \Log::error('Stripe gateway not found or not active');
                return;
            }

            // Initialize Stripe gateway
            $stripeGateway = new \App\Services\PaymentGateway\StripeGateway();
            $credentials = array_merge($gateway->credentials ?? [], [
                'test_mode' => $gateway->test_mode
            ]);
            $stripeGateway->initialize($credentials);

            // For mock sessions, mark as completed
            if (str_starts_with($sessionId, 'cs_mock_')) {
                $payment->update([
                    'status' => 'completed',
                    'payment_date' => now()
                ]);
                $this->updateInvoiceStatus($invoice);
                session()->flash('success', 'Payment completed successfully!');
                return;
            }

            // Verify the session with Stripe
            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);
                
                if ($session->payment_status === 'paid') {
                    $payment->update([
                        'status' => 'completed',
                        'payment_date' => now(),
                        'gateway_response' => array_merge(
                            $payment->gateway_response ?? [],
                            ['session_status' => $session->status, 'payment_status' => $session->payment_status]
                        )
                    ]);
                    $this->updateInvoiceStatus($invoice);
                    session()->flash('success', 'Payment completed successfully!');
                } else {
                    \Log::warning('Stripe session payment not completed', [
                        'session_id' => $sessionId,
                        'payment_status' => $session->payment_status
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to verify Stripe session', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage()
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error handling Stripe payment success', [
                'session_id' => $sessionId,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync patient payment back to admin billing system
     */
    private function syncToAdminBilling($invoice, $totalPaid)
    {
        try {
            // Check if this invoice is linked to a billing record
            if (!$invoice->billing_id) {
                return; // No billing record to sync to
            }

            $billing = \App\Models\Billing::find($invoice->billing_id);
            if (!$billing) {
                \Log::warning('Billing record not found for invoice', [
                    'invoice_id' => $invoice->id,
                    'billing_id' => $invoice->billing_id
                ]);
                return;
            }

            // Update billing record with payment information
            $billing->update([
                'paid_amount' => $totalPaid,
                'payment_method' => 'card', // Most patient payments are via card/stripe
                'payment_reference' => 'PATIENT_PORTAL_PAYMENT',
                'paid_at' => $totalPaid >= $invoice->total_amount ? now() : $billing->paid_at,
                'updated_by' => null, // System update, not by specific user
            ]);

            \Log::info('Successfully synced patient payment to admin billing', [
                'invoice_id' => $invoice->id,
                'billing_id' => $billing->id,
                'total_paid' => $totalPaid,
                'billing_status' => $billing->fresh()->status
            ]);

            // Send payment completion notification if billing is fully paid
            if ($totalPaid >= $invoice->total_amount) {
                try {
                    $this->notificationService->sendBillingNotification(
                        $billing,
                        'payment_received',
                        $totalPaid
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send billing sync notification', [
                        'billing_id' => $billing->id,
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Failed to sync patient payment to admin billing', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle PayPal payment success callback
     */
    public function handlePayPalSuccess(Request $request, Payment $payment)
    {
        $patient = Auth::guard('patient')->user();
        
        // Ensure the payment belongs to the authenticated patient
        if ($payment->invoice->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to payment.');
        }
        
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');
        
        if (!$paymentId || !$payerId) {
            return redirect()->route('patient.billing.show', $payment->invoice)
                ->with('error', 'Invalid PayPal payment parameters.');
        }
        
        // Get the PayPal gateway
        $gateway = \App\Models\PaymentGateway::where('provider', 'paypal')
            ->where('is_active', true)
            ->first();
            
        if (!$gateway) {
            return redirect()->route('patient.billing.show', $payment->invoice)
                ->with('error', 'PayPal gateway not available.');
        }
        
        try {
            // Initialize PayPal Gateway
            $paypalGateway = new \App\Services\PaymentGateway\PayPalGateway();
            $credentials = array_merge($gateway->credentials ?? [], [
                'test_mode' => $gateway->test_mode
            ]);
            $paypalGateway->initialize($credentials);
            
            // Execute the payment
            $result = $paypalGateway->executePayment($paymentId, $payerId);
            
            if ($result['success']) {
                // Update payment status
                $payment->update([
                    'status' => 'completed',
                    'payment_date' => now(),
                    'gateway_response' => array_merge(
                        $payment->gateway_response ?? [],
                        $result['gateway_response'] ?? []
                    )
                ]);
                
                // Update invoice status
                $this->updateInvoiceStatus($payment->invoice);
                
                return redirect()->route('patient.billing.show', $payment->invoice)
                    ->with('success', 'Payment completed successfully via PayPal!');
            } else {
                // Payment execution failed
                $payment->update(['status' => 'failed']);
                
                return redirect()->route('patient.billing.show', $payment->invoice)
                    ->with('error', 'PayPal payment failed: ' . $result['error']);
            }
            
        } catch (\Exception $e) {
            \Log::error('PayPal payment execution failed', [
                'payment_id' => $payment->id,
                'paypal_payment_id' => $paymentId,
                'payer_id' => $payerId,
                'error' => $e->getMessage()
            ]);
            
            $payment->update(['status' => 'failed']);
            
            return redirect()->route('patient.billing.show', $payment->invoice)
                ->with('error', 'PayPal payment processing failed. Please try again.');
        }
    }
    
    /**
     * Handle PayPal payment cancellation
     */
    public function handlePayPalCancel(Payment $payment)
    {
        $patient = Auth::guard('patient')->user();
        
        // Ensure the payment belongs to the authenticated patient
        if ($payment->invoice->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to payment.');
        }
        
        // Update payment status to cancelled
        $payment->update(['status' => 'cancelled']);
        
        return redirect()->route('patient.billing.show', $payment->invoice)
            ->with('info', 'PayPal payment was cancelled. You can try again or choose a different payment method.');
    }

    /**
     * Determine the appropriate currency for Paystack payments.
     * Uses the system's default currency from admin general settings.
     */
    private function getPaystackCurrency($paystackGateway): string
    {
        // Get supported currencies from Paystack
        $supportedCurrencies = $paystackGateway->getSupportedCurrencies();
        
        // Priority order for currency selection
        // 1. Use the system's default currency from admin settings
        $systemCurrency = $this->getSystemCurrency();
        if ($systemCurrency && in_array($systemCurrency, $supportedCurrencies)) {
            return $systemCurrency;
        }
        
        // 2. Check app configuration as fallback
        $appCurrency = config('app.currency', null);
        if ($appCurrency && in_array($appCurrency, $supportedCurrencies)) {
            return $appCurrency;
        }
        
        // 3. Try to detect based on system timezone (if admin hasn't set currency)
        $detectedCurrency = $this->detectCurrencyFromSystemTimezone();
        if ($detectedCurrency && in_array($detectedCurrency, $supportedCurrencies)) {
            return $detectedCurrency;
        }
        
        // 4. Final fallback to GHS (since this system is Ghana-focused)
        return 'GHS';
    }
    
    /**
     * Get system-wide currency setting.
     */
    private function getSystemCurrency(): ?string
    {
        // Try to get currency from settings table if it exists
        try {
            // Check if there's a settings model/table
            if (class_exists('\App\Models\Setting')) {
                $setting = \App\Models\Setting::where('key', 'default_currency')->first();
                return $setting?->value;
            }
            
            // Check if there's a system config in database
            if (\Schema::hasTable('system_settings')) {
                $currency = \DB::table('system_settings')
                    ->where('setting_key', 'currency')
                    ->value('setting_value');
                return $currency;
            }
        } catch (\Exception $e) {
            // Ignore errors and continue with other detection methods
        }
        
        return null;
    }
    
    /**
     * Comprehensive currency detection based on timezone/location.
     */
    private function detectCurrencyFromLocation(): ?string
    {
        // Get the application timezone
        $timezone = config('app.timezone', 'UTC');
        
        // Comprehensive mapping of timezones to currencies
        $timezoneMap = [
            // Africa
            'Africa/Lagos' => 'NGN',           // Nigeria
            'Africa/Accra' => 'GHS',           // Ghana
            'Africa/Johannesburg' => 'ZAR',    // South Africa
            'Africa/Nairobi' => 'KES',         // Kenya
            'Africa/Cairo' => 'EGP',           // Egypt
            'Africa/Casablanca' => 'MAD',      // Morocco
            'Africa/Tunis' => 'TND',           // Tunisia
            'Africa/Algiers' => 'DZD',         // Algeria
            'Africa/Kampala' => 'UGX',         // Uganda
            'Africa/Dar_es_Salaam' => 'TZS',   // Tanzania
            'Africa/Addis_Ababa' => 'ETB',     // Ethiopia
            'Africa/Kigali' => 'RWF',          // Rwanda
            'Africa/Lusaka' => 'ZMW',          // Zambia
            'Africa/Harare' => 'ZWL',          // Zimbabwe
            'Africa/Maputo' => 'MZN',          // Mozambique
            'Africa/Windhoek' => 'NAD',        // Namibia
            'Africa/Gaborone' => 'BWP',        // Botswana
            
            // North America
            'America/New_York' => 'USD',       // USA (Eastern)
            'America/Chicago' => 'USD',        // USA (Central)
            'America/Denver' => 'USD',         // USA (Mountain)
            'America/Los_Angeles' => 'USD',    // USA (Pacific)
            'America/Anchorage' => 'USD',      // USA (Alaska)
            'America/Toronto' => 'CAD',        // Canada (Eastern)
            'America/Vancouver' => 'CAD',      // Canada (Pacific)
            'America/Mexico_City' => 'MXN',    // Mexico
            
            // South America
            'America/Sao_Paulo' => 'BRL',      // Brazil
            'America/Buenos_Aires' => 'ARS',   // Argentina
            'America/Santiago' => 'CLP',       // Chile
            'America/Lima' => 'PEN',           // Peru
            'America/Bogota' => 'COP',         // Colombia
            'America/Caracas' => 'VES',        // Venezuela
            'America/La_Paz' => 'BOB',         // Bolivia
            'America/Asuncion' => 'PYG',       // Paraguay
            'America/Montevideo' => 'UYU',     // Uruguay
            
            // Europe
            'Europe/London' => 'GBP',          // United Kingdom
            'Europe/Paris' => 'EUR',           // France
            'Europe/Berlin' => 'EUR',          // Germany
            'Europe/Rome' => 'EUR',            // Italy
            'Europe/Madrid' => 'EUR',          // Spain
            'Europe/Amsterdam' => 'EUR',       // Netherlands
            'Europe/Brussels' => 'EUR',        // Belgium
            'Europe/Vienna' => 'EUR',          // Austria
            'Europe/Zurich' => 'CHF',          // Switzerland
            'Europe/Stockholm' => 'SEK',       // Sweden
            'Europe/Oslo' => 'NOK',            // Norway
            'Europe/Copenhagen' => 'DKK',      // Denmark
            'Europe/Helsinki' => 'EUR',        // Finland
            'Europe/Warsaw' => 'PLN',          // Poland
            'Europe/Prague' => 'CZK',          // Czech Republic
            'Europe/Budapest' => 'HUF',        // Hungary
            'Europe/Bucharest' => 'RON',       // Romania
            'Europe/Sofia' => 'BGN',           // Bulgaria
            'Europe/Athens' => 'EUR',          // Greece
            'Europe/Istanbul' => 'TRY',        // Turkey
            'Europe/Moscow' => 'RUB',          // Russia
            'Europe/Kiev' => 'UAH',            // Ukraine
            
            // Asia
            'Asia/Tokyo' => 'JPY',             // Japan
            'Asia/Seoul' => 'KRW',             // South Korea
            'Asia/Shanghai' => 'CNY',          // China
            'Asia/Hong_Kong' => 'HKD',         // Hong Kong
            'Asia/Singapore' => 'SGD',         // Singapore
            'Asia/Kuala_Lumpur' => 'MYR',      // Malaysia
            'Asia/Bangkok' => 'THB',           // Thailand
            'Asia/Jakarta' => 'IDR',           // Indonesia
            'Asia/Manila' => 'PHP',            // Philippines
            'Asia/Ho_Chi_Minh' => 'VND',       // Vietnam
            'Asia/Yangon' => 'MMK',            // Myanmar
            'Asia/Phnom_Penh' => 'KHR',        // Cambodia
            'Asia/Vientiane' => 'LAK',         // Laos
            'Asia/Dhaka' => 'BDT',             // Bangladesh
            'Asia/Kolkata' => 'INR',           // India
            'Asia/Colombo' => 'LKR',           // Sri Lanka
            'Asia/Kathmandu' => 'NPR',         // Nepal
            'Asia/Islamabad' => 'PKR',         // Pakistan
            'Asia/Kabul' => 'AFN',             // Afghanistan
            'Asia/Tehran' => 'IRR',            // Iran
            'Asia/Baghdad' => 'IQD',           // Iraq
            'Asia/Kuwait' => 'KWD',            // Kuwait
            'Asia/Riyadh' => 'SAR',            // Saudi Arabia
            'Asia/Dubai' => 'AED',             // UAE
            'Asia/Doha' => 'QAR',              // Qatar
            'Asia/Bahrain' => 'BHD',           // Bahrain
            'Asia/Muscat' => 'OMR',            // Oman
            'Asia/Jerusalem' => 'ILS',         // Israel
            'Asia/Beirut' => 'LBP',            // Lebanon
            'Asia/Damascus' => 'SYP',          // Syria
            'Asia/Amman' => 'JOD',             // Jordan
            
            // Oceania
            'Australia/Sydney' => 'AUD',       // Australia (Eastern)
            'Australia/Melbourne' => 'AUD',    // Australia (Eastern)
            'Australia/Brisbane' => 'AUD',     // Australia (Eastern)
            'Australia/Perth' => 'AUD',        // Australia (Western)
            'Australia/Adelaide' => 'AUD',     // Australia (Central)
            'Australia/Darwin' => 'AUD',       // Australia (Central)
            'Pacific/Auckland' => 'NZD',       // New Zealand
            'Pacific/Fiji' => 'FJD',           // Fiji
            'Pacific/Port_Moresby' => 'PGK',   // Papua New Guinea
            
            // Central Asia
            'Asia/Almaty' => 'KZT',            // Kazakhstan
            'Asia/Tashkent' => 'UZS',          // Uzbekistan
            'Asia/Bishkek' => 'KGS',           // Kyrgyzstan
            'Asia/Dushanbe' => 'TJS',          // Tajikistan
            'Asia/Ashgabat' => 'TMT',          // Turkmenistan
        ];
        
        // Check if we have a mapping for the current timezone
        if (isset($timezoneMap[$timezone])) {
            return $timezoneMap[$timezone];
        }
        
        // Try to detect from PHP's default timezone if different
        $phpTimezone = date_default_timezone_get();
        if (isset($timezoneMap[$phpTimezone])) {
            return $timezoneMap[$phpTimezone];
        }
        
        return null;
    }

    /**
     * Detect currency from system timezone (uses admin settings).
     */
    private function detectCurrencyFromSystemTimezone(): ?string
    {
        // Get the system timezone from admin settings
        $systemTimezone = null;
        try {
            if (class_exists('\App\Models\Setting')) {
                $timezoneSetting = \App\Models\Setting::where('key', 'app_timezone')->first();
                $systemTimezone = $timezoneSetting?->value;
            }
        } catch (\Exception $e) {
            // Ignore errors
        }
        
        // Fall back to app config timezone if not set in admin
        if (!$systemTimezone) {
            $systemTimezone = config('app.timezone', 'UTC');
        }
        
        // Use the same comprehensive mapping as detectCurrencyFromLocation
        $timezoneMap = [
            // Africa
            'Africa/Lagos' => 'NGN',           // Nigeria
            'Africa/Accra' => 'GHS',           // Ghana
            'Africa/Johannesburg' => 'ZAR',    // South Africa
            'Africa/Nairobi' => 'KES',         // Kenya
            'Africa/Cairo' => 'EGP',           // Egypt
            'Africa/Casablanca' => 'MAD',      // Morocco
            'Africa/Tunis' => 'TND',           // Tunisia
            'Africa/Algiers' => 'DZD',         // Algeria
            'Africa/Kampala' => 'UGX',         // Uganda
            'Africa/Dar_es_Salaam' => 'TZS',   // Tanzania
            'Africa/Addis_Ababa' => 'ETB',     // Ethiopia
            'Africa/Kigali' => 'RWF',          // Rwanda
            'Africa/Lusaka' => 'ZMW',          // Zambia
            'Africa/Harare' => 'ZWL',          // Zimbabwe
            'Africa/Maputo' => 'MZN',          // Mozambique
            'Africa/Windhoek' => 'NAD',        // Namibia
            'Africa/Gaborone' => 'BWP',        // Botswana
            
            // North America
            'America/New_York' => 'USD',       // USA (Eastern)
            'America/Chicago' => 'USD',        // USA (Central)
            'America/Denver' => 'USD',         // USA (Mountain)
            'America/Los_Angeles' => 'USD',    // USA (Pacific)
            'America/Anchorage' => 'USD',      // USA (Alaska)
            'America/Toronto' => 'CAD',        // Canada (Eastern)
            'America/Vancouver' => 'CAD',      // Canada (Pacific)
            'America/Mexico_City' => 'MXN',    // Mexico
            
            // South America
            'America/Sao_Paulo' => 'BRL',      // Brazil
            'America/Argentina/Buenos_Aires' => 'ARS', // Argentina
            'America/Santiago' => 'CLP',       // Chile
            'America/Lima' => 'PEN',           // Peru
            'America/Bogota' => 'COP',         // Colombia
            'America/Caracas' => 'VES',        // Venezuela
            'America/La_Paz' => 'BOB',         // Bolivia
            'America/Asuncion' => 'PYG',       // Paraguay
            'America/Montevideo' => 'UYU',     // Uruguay
            
            // Europe
            'Europe/London' => 'GBP',          // United Kingdom
            'Europe/Paris' => 'EUR',           // France
            'Europe/Berlin' => 'EUR',          // Germany
            'Europe/Rome' => 'EUR',            // Italy
            'Europe/Madrid' => 'EUR',          // Spain
            'Europe/Amsterdam' => 'EUR',       // Netherlands
            'Europe/Brussels' => 'EUR',        // Belgium
            'Europe/Vienna' => 'EUR',          // Austria
            'Europe/Zurich' => 'CHF',          // Switzerland
            'Europe/Stockholm' => 'SEK',       // Sweden
            'Europe/Oslo' => 'NOK',            // Norway
            'Europe/Copenhagen' => 'DKK',      // Denmark
            'Europe/Helsinki' => 'EUR',        // Finland
            'Europe/Warsaw' => 'PLN',          // Poland
            'Europe/Prague' => 'CZK',          // Czech Republic
            'Europe/Budapest' => 'HUF',        // Hungary
            'Europe/Bucharest' => 'RON',       // Romania
            'Europe/Sofia' => 'BGN',           // Bulgaria
            'Europe/Athens' => 'EUR',          // Greece
            'Europe/Istanbul' => 'TRY',        // Turkey
            'Europe/Moscow' => 'RUB',          // Russia
            'Europe/Kiev' => 'UAH',            // Ukraine
            
            // Asia
            'Asia/Tokyo' => 'JPY',             // Japan
            'Asia/Seoul' => 'KRW',             // South Korea
            'Asia/Shanghai' => 'CNY',          // China
            'Asia/Hong_Kong' => 'HKD',         // Hong Kong
            'Asia/Singapore' => 'SGD',         // Singapore
            'Asia/Kuala_Lumpur' => 'MYR',      // Malaysia
            'Asia/Bangkok' => 'THB',           // Thailand
            'Asia/Jakarta' => 'IDR',           // Indonesia
            'Asia/Manila' => 'PHP',            // Philippines
            'Asia/Ho_Chi_Minh' => 'VND',       // Vietnam
            'Asia/Yangon' => 'MMK',            // Myanmar
            'Asia/Phnom_Penh' => 'KHR',        // Cambodia
            'Asia/Vientiane' => 'LAK',         // Laos
            'Asia/Dhaka' => 'BDT',             // Bangladesh
            'Asia/Kolkata' => 'INR',           // India
            'Asia/Colombo' => 'LKR',           // Sri Lanka
            'Asia/Kathmandu' => 'NPR',         // Nepal
            'Asia/Islamabad' => 'PKR',         // Pakistan
            'Asia/Kabul' => 'AFN',             // Afghanistan
            'Asia/Tehran' => 'IRR',            // Iran
            'Asia/Baghdad' => 'IQD',           // Iraq
            'Asia/Kuwait' => 'KWD',            // Kuwait
            'Asia/Riyadh' => 'SAR',            // Saudi Arabia
            'Asia/Dubai' => 'AED',             // UAE
            'Asia/Doha' => 'QAR',              // Qatar
            'Asia/Bahrain' => 'BHD',           // Bahrain
            'Asia/Muscat' => 'OMR',            // Oman
            'Asia/Jerusalem' => 'ILS',         // Israel
            'Asia/Beirut' => 'LBP',            // Lebanon
            'Asia/Damascus' => 'SYP',          // Syria
            'Asia/Amman' => 'JOD',             // Jordan
            
            // Oceania
            'Australia/Sydney' => 'AUD',       // Australia (Eastern)
            'Australia/Melbourne' => 'AUD',    // Australia (Eastern)
            'Australia/Brisbane' => 'AUD',     // Australia (Eastern)
            'Australia/Perth' => 'AUD',        // Australia (Western)
            'Australia/Adelaide' => 'AUD',     // Australia (Central)
            'Australia/Darwin' => 'AUD',       // Australia (Central)
            'Pacific/Auckland' => 'NZD',       // New Zealand
            'Pacific/Fiji' => 'FJD',           // Fiji
            'Pacific/Port_Moresby' => 'PGK',   // Papua New Guinea
            
            // Central Asia
            'Asia/Almaty' => 'KZT',            // Kazakhstan
            'Asia/Tashkent' => 'UZS',          // Uzbekistan
            'Asia/Bishkek' => 'KGS',           // Kyrgyzstan
            'Asia/Dushanbe' => 'TJS',          // Tajikistan
            'Asia/Ashgabat' => 'TMT',          // Turkmenistan
        ];
        
        return $timezoneMap[$systemTimezone] ?? null;
    }

    /**
     * Determine the appropriate currency for Flutterwave payments.
     * Uses the system's default currency from admin general settings.
     */
    private function getFlutterwaveCurrency($flutterwaveGateway): string
    {
        // Get supported currencies from Flutterwave
        $supportedCurrencies = $flutterwaveGateway->getSupportedCurrencies();
        
        // Priority order for currency selection
        // 1. Use the system's default currency from admin settings
        $systemCurrency = $this->getSystemCurrency();
        if ($systemCurrency && in_array($systemCurrency, $supportedCurrencies)) {
            return $systemCurrency;
        }
        
        // 2. Check app configuration as fallback
        $appCurrency = config('app.currency', null);
        if ($appCurrency && in_array($appCurrency, $supportedCurrencies)) {
            return $appCurrency;
        }
        
        // 3. Try to detect based on system timezone (if admin hasn't set currency)
        $detectedCurrency = $this->detectCurrencyFromSystemTimezone();
        if ($detectedCurrency && in_array($detectedCurrency, $supportedCurrencies)) {
            return $detectedCurrency;
        }
        
        // 4. Final fallback to GHS (since this system is Ghana-focused)
        return 'GHS';
    }

    /**
     * Generate a unique transaction ID.
     */
    private function generateTransactionId(): string
    {
        do {
            $id = 'TXN' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Payment::where('transaction_id', $id)->exists());

        return $id;
    }
}
