<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Billing;
use App\Services\PostBookingRedirectService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PublicBillingController extends Controller
{
    /**
     * Show invoice and payment form using payment token (no authentication required)
     * Supports both routes: /pay/{token} and /{clinic}/{service}/pay/{token}
     */
    public function showInvoice(Request $request, ...$args): View|RedirectResponse
    {
        // Get token from route parameter (handles both route patterns)
        // For /{clinic}/{service}/pay/{token}: route params will be ['clinic' => ..., 'service' => ..., 'token' => ...]
        // For /pay/{token}: route params will be ['token' => ...]
        $token = $request->route('token');
        
        // If not found in route, try the last argument (for backward compatibility)
        if (!$token && !empty($args)) {
            $token = end($args);
        }
        
        // Get clinic and service from route if present
        $clinic = $request->route('clinic');
        $service = $request->route('service');
        
        Log::info('Public billing showInvoice called', [
            'token' => $token,
            'clinic' => $clinic,
            'service' => $service,
            'route_name' => $request->route() ? $request->route()->getName() : 'NO ROUTE',
            'all_route_params' => $request->route() ? $request->route()->parameters() : [],
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'method' => $request->method(),
        ]);
        
        // Debug: Check if this is the clinic/service route
        if ($clinic && $service) {
            Log::info('Clinic/Service route detected', [
                'clinic' => $clinic,
                'service' => $service,
                'token' => $token,
            ]);
        }
        
        if (!$token) {
            Log::error('Payment token missing from route', [
                'route_params' => $request->route()->parameters(),
                'args' => $args,
            ]);
            return redirect(route('public.billing.invalid', [], false))
                ->with('error', 'Invalid payment link: token missing.');
        }
        
        $invoice = Invoice::where('payment_token', $token)->first();

        if (!$invoice) {
            Log::warning('Invoice not found for token', [
                'token' => $token,
                'token_length' => strlen($token),
                'recent_tokens' => Invoice::latest()->take(3)->pluck('payment_token')->toArray(),
            ]);
            return redirect(route('public.billing.invalid', [], false))
                ->with('error', 'Invalid or expired payment link.');
        }
        
        Log::info('Invoice found', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
        ]);

        // Check if token is expired (only if expiration is set)
        if ($invoice->payment_token_expires_at !== null && $invoice->payment_token_expires_at->isPast()) {
            return redirect(route('public.billing.invalid', [], false))
                ->with('error', 'This payment link has expired. Please contact the hospital for a new link.');
        }

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return view('public.billing.paid', array_merge(compact('invoice'), ['logo_only' => true]));
        }

        $invoice->load(['patient', 'appointment', 'billing.doctor']);

        // Get available payment gateways
        $gateways = PaymentGateway::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Pass route information to view so it can use the correct route pattern
        // Always ensure we use the token from the route, not from anywhere else
        $routeType = ($clinic && $service) ? 'service' : 'billing';
        $routeParams = ($clinic && $service) ? [
            'clinic' => $clinic,
            'service' => $service,
            'token' => $token // Use the token extracted from the route
        ] : ['token' => $token]; // Use the token extracted from the route

        Log::info('Passing route info to view', [
            'routeType' => $routeType,
            'routeParams' => $routeParams,
            'token' => $token,
            'invoice_id' => $invoice->id,
        ]);

        return view('public.billing.show', array_merge(compact('invoice', 'gateways', 'token', 'routeType', 'routeParams'), ['logo_only' => true]));
    }

    /**
     * Show payment form with selected gateway
     */
    public function showPaymentForm(Request $request, ...$args): View|RedirectResponse
    {
        // IMPORTANT: Laravel passes route params positionally, so for
        // /{clinic}/{service}/pay/{token}/... the first argument would otherwise be "clinic".
        // Always read the token from the route parameters.
        $token = $request->route('token');
        if (!$token && !empty($args)) {
            $token = end($args);
        }
        
        if (!$token) {
            \Log::error('ShowPaymentForm: Token not found', [
                'route_params' => $request->route()->parameters(),
                'all_params' => $request->all(),
            ]);
            return redirect(route('public.billing.invalid', [], false))
                ->with('error', 'Invalid payment link: token missing.');
        }
        
        $invoice = Invoice::where('payment_token', $token)->first();

        if (!$invoice || $invoice->payment_token !== $token) {
            return redirect(route('public.billing.invalid', [], false))
                ->with('error', 'Invalid payment link.');
        }
        
        // Check expiration only if set
        if ($invoice->payment_token_expires_at !== null && $invoice->payment_token_expires_at->isPast()) {
            return redirect(route('public.billing.invalid', [], false))
                ->with('error', 'This payment link has expired.');
        }

        if ($invoice->status === 'paid') {
            // Use the same route pattern as the current request
            $clinic = $request->route('clinic');
            $service = $request->route('service');
            
            if ($clinic && $service) {
                $redirectRoute = route('public.service.payment', [
                    'clinic' => $clinic,
                    'service' => $service,
                    'token' => $token
                ], false);
            } else {
                $redirectRoute = route('public.billing.pay', ['token' => $token], false);
            }
            
            return redirect($redirectRoute)
                ->with('info', 'This invoice has already been paid.');
        }

        $request->validate([
            'payment_gateway' => 'required|string|exists:payment_gateways,provider'
        ]);

        $selectedGateway = PaymentGateway::where('provider', $request->payment_gateway)
            ->where('is_active', true)
            ->firstOrFail();

        $invoice->load(['invoiceItems', 'patient']);

        // Pass route information to view so it can use the correct route pattern
        $clinic = $request->route('clinic');
        $service = $request->route('service');
        $routeType = ($clinic && $service) ? 'service' : 'billing';
        $routeParams = ($clinic && $service) ? [
            'clinic' => $clinic,
            'service' => $service,
            'token' => $token
        ] : ['token' => $token];

        return view('public.billing.payment-form', array_merge(compact('invoice', 'selectedGateway', 'token', 'routeType', 'routeParams'), ['logo_only' => true]));
    }

    /**
     * Process payment for invoice
     */
    public function processPayment(Request $request, ...$args)
    {
        // Always read token from route params (see note in showPaymentForm)
        $token = $request->route('token');
        if (!$token && !empty($args)) {
            $token = end($args);
        }
        
        if (!$token) {
            \Log::error('ProcessPayment: Token not found', [
                'route_params' => $request->route()->parameters(),
                'all_params' => $request->all(),
            ]);
            return back()->withErrors(['error' => 'Invalid payment link: token missing.']);
        }
        
        $invoice = Invoice::where('payment_token', $token)->first();

        if (!$invoice || $invoice->payment_token !== $token) {
            return back()->withErrors(['error' => 'Invalid payment link.']);
        }
        
        // Check expiration only if set
        if ($invoice->payment_token_expires_at !== null && $invoice->payment_token_expires_at->isPast()) {
            return back()->withErrors(['error' => 'This payment link has expired.']);
        }

        if ($invoice->status === 'paid') {
            return back()->withErrors(['error' => 'This invoice has already been paid.']);
        }

        $request->validate([
            'payment_gateway' => 'required|string|exists:payment_gateways,provider',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->outstanding_amount,
        ]);

        $gateway = PaymentGateway::where('provider', $request->payment_gateway)
            ->where('is_active', true)
            ->firstOrFail();

        // Create payment record
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'payment_date' => now(),
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_gateway' => $request->payment_gateway,
            'gateway_transaction_id' => null,
            'status' => 'pending',
            'transaction_id' => $this->generateTransactionId(),
            'notes' => 'Payment via public link',
        ]);

        // Handle different gateway types
        switch ($request->payment_gateway) {
            case 'stripe':
                return $this->processStripePayment($payment, $request, $invoice, $gateway, $token);
            case 'paypal':
                return $this->processPayPalPayment($payment, $request, $invoice, $gateway, $token);
            default:
                // For other gateways, redirect to gateway-specific handler
                return back()->withErrors(['error' => 'Payment gateway not fully implemented yet.']);
        }
    }

    /**
     * Process Stripe payment
     */
    private function processStripePayment($payment, $request, $invoice, $gateway, $token)
    {
        try {
            \Log::info('Processing Stripe payment for public billing', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $payment->amount,
                'gateway_id' => $gateway->id,
                'has_credentials' => !empty($gateway->credentials)
            ]);

            $stripeGateway = new \App\Services\PaymentGateway\StripeGateway();
            $credentials = array_merge($gateway->credentials ?? [], [
                'test_mode' => $gateway->test_mode
            ]);
            
            if (empty($credentials['secret_key'])) {
                \Log::error('Stripe credentials missing secret_key', [
                    'gateway_id' => $gateway->id,
                    'has_credentials' => !empty($gateway->credentials)
                ]);
                return back()->withErrors(['error' => 'Stripe payment gateway is not properly configured. Please contact support.']);
            }
            
            $stripeGateway->initialize($credentials);

            // Build success and cancel URLs.
            // Stripe requires ABSOLUTE URLs. Use HTTPS when site is served over HTTPS (APP_URL or proxy).
            $clinic = $request->route('clinic');
            $service = $request->route('service');
            $scheme = ($request->secure() || ($request->header('X-Forwarded-Proto') === 'https'))
                ? 'https'
                : (str_starts_with(config('app.url', ''), 'https://') ? 'https' : 'http');
            $baseUrl = $scheme . '://' . $request->getHost();
            
            if ($clinic && $service) {
                $successUrl = $baseUrl . route('public.service.success', [
                    'clinic' => $clinic,
                    'service' => $service,
                    'token' => $token
                ], false);
                $cancelUrl = $baseUrl . route('public.service.payment', [
                    'clinic' => $clinic,
                    'service' => $service,
                    'token' => $token
                ], false);
            } else {
                $successUrl = $baseUrl . route('public.billing.success', ['token' => $token], false);
                $cancelUrl = $baseUrl . route('public.billing.pay', ['token' => $token], false);
            }
            
            // Get currency - check invoice/billing currency, then system settings, then default to GBP for UK
            $currency = $this->getCurrencyForPayment($invoice, $gateway);
            
            $checkoutData = [
                'amount' => $payment->amount,
                'currency' => strtolower($currency),
                'order_id' => $payment->transaction_id,
                'customer_email' => $invoice->patient->email ?? 'customer@example.com',
                'description' => 'Invoice #' . $invoice->invoice_number,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl . '?payment_cancelled=1',
                'is_public_payment' => true,
            ];

            \Log::info('Creating Stripe checkout session', $checkoutData);

            $response = $stripeGateway->createCheckoutSession($checkoutData);

            \Log::info('Stripe checkout session response', [
                'success' => $response['success'] ?? false,
                'has_payment_url' => !empty($response['payment_url'] ?? null),
                'error' => $response['error'] ?? null
            ]);

            if (!$response['success']) {
                $errorMessage = $response['error'] ?? 'Unknown error occurred';
                \Log::error('Failed to create Stripe checkout session', [
                    'error' => $errorMessage,
                    'response' => $response
                ]);
                return back()->withErrors(['error' => 'Failed to create Stripe checkout session: ' . $errorMessage]);
            }

            if (empty($response['payment_url'])) {
                \Log::error('Stripe checkout session created but no payment URL returned', [
                    'response' => $response
                ]);
                return back()->withErrors(['error' => 'Payment session created but redirect URL is missing. Please try again.']);
            }

            $payment->update([
                'gateway_transaction_id' => $response['payment_id'] ?? null,
                'status' => 'pending',
                'gateway_response' => $response['gateway_response'] ?? [],
            ]);

            \Log::info('Redirecting to Stripe checkout', [
                'payment_url' => $response['payment_url'],
                'payment_url_length' => strlen($response['payment_url'])
            ]);

            // Use JavaScript redirect as primary method for external URLs
            // This ensures the redirect works even if server-side redirect is blocked
            return response()->view('public.billing.redirect', [
                'redirect_url' => $response['payment_url'],
                'payment_id' => $response['payment_id'] ?? null,
                'logo_only' => true
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Exception in processStripePayment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'An error occurred while processing your payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Process PayPal payment
     */
    private function processPayPalPayment($payment, $request, $invoice, $gateway, $token)
    {
        // Similar implementation for PayPal
        // This would integrate with PayPal SDK
        return back()->withErrors(['error' => 'PayPal payment processing not fully implemented yet.']);
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess(Request $request, ...$args): View|RedirectResponse
    {
        // Always read token from route params (see note in showPaymentForm)
        $token = $request->route('token');
        if (!$token && !empty($args)) {
            $token = end($args);
        }
        
        if (!$token) {
            \Log::error('PaymentSuccess: Token not found', [
                'route_params' => $request->route()->parameters(),
                'all_params' => $request->all(),
            ]);
            return redirect(route('public.billing.invalid', [], false))
                ->with('error', 'Invalid payment link: token missing.');
        }
        
        $invoice = Invoice::where('payment_token', $token)->first();

        if (!$invoice) {
            return redirect(route('public.billing.invalid', [], false))
                ->with('error', 'Invalid payment link.');
        }

        $invoice->load(['patient', 'payments' => function($query) {
            $query->where('status', 'completed')->latest();
        }]);

        // Refresh invoice to get latest paid_amount
        $invoice->refresh();

        // If session_id is provided (from Stripe), try to verify and send receipt
        if ($request->has('session_id')) {
            $this->handleStripeSuccessCallback($invoice, $request->session_id);
        } else {
            // For other payment gateways or direct payments, check if there's a completed payment
            // and send receipt email if not already sent
            $latestPayment = $invoice->payments()
                ->where('status', 'completed')
                ->latest()
                ->first();

            if ($latestPayment) {
                // Ensure billing is updated even if callback wasn't called
                $this->updateInvoiceAndBilling($invoice);

                // Check if receipt already sent (to avoid duplicates)
                $receiptSent = Cache::get('receipt_sent_' . $latestPayment->id, false);

                if (!$receiptSent) {
                    try {
                        $emailService = app(\App\Services\HospitalEmailNotificationService::class);
                        $emailService->sendPaymentReceipt($invoice, $latestPayment);

                        // Mark receipt as sent (cache for 1 hour)
                        Cache::put('receipt_sent_' . $latestPayment->id, true, 3600);

                        \Log::info('Payment receipt email sent from success page', [
                            'invoice_id' => $invoice->id,
                            'payment_id' => $latestPayment->id
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error sending receipt email on success page', [
                            'invoice_id' => $invoice->id,
                            'payment_id' => $latestPayment->id ?? null,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        // Always ensure billing is updated when viewing success page (fallback)
        $this->updateInvoiceAndBilling($invoice);

        // Check if this is a pending clinic booking that needs to be finalized after payment
        $pendingClinicToken = session('pending_clinic_booking_token');
        if ($pendingClinicToken) {
            try {
                $clinicService = app(\App\Services\ClinicBookingService::class);
                $pending = \App\Models\PendingClinicBooking::where('booking_token', $pendingClinicToken)
                    ->where('invoice_id', $invoice->id)
                    ->where('status', 'pending_payment')
                    ->first();

                if ($pending) {
                    $hasPaid = $invoice->payments()->where('status', 'completed')->exists() || $invoice->status === 'paid';
                    if ($hasPaid) {
                        $clinicRequest = $clinicService->finalizeClinicBookingAfterPayment($pending);
                        session()->forget('pending_clinic_booking_token');

                        $external = app(PostBookingRedirectService::class)->buildRedirectUrlForClinicBookingRequest($clinicRequest);
                        if ($external !== null) {
                            session()->forget('booking_utm_params');

                            return redirect()->away($external);
                        }

                        return redirect()->route('public.booking.clinic-success', [
                            'requestNumber' => $clinicRequest->request_number,
                        ])->with('payment_success', true);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to finalize pending clinic booking after payment', [
                    'invoice_id' => $invoice->id,
                    'pending_clinic_token' => $pendingClinicToken,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Check if this is a pending booking (doctor-selected) that needs to be finalized after payment
        $pendingBookingToken = session('pending_booking_token');
        if ($pendingBookingToken) {
            try {
                $result = $this->finalizePendingBookingAfterPayment($invoice, $pendingBookingToken);
                if ($result && isset($result['appointment'])) {
                    session()->forget('pending_booking_token');
                    $appointment = $result['appointment'];
                    $appointment->loadMissing('doctor');

                    $external = app(PostBookingRedirectService::class)->buildRedirectUrlForAppointment($appointment);
                    if ($external !== null) {
                        session()->forget('booking_utm_params');

                        return redirect()->away($external);
                    }

                    return redirect()->route('public.booking.success', [
                        'appointmentNumber' => $appointment->appointment_number,
                    ])->with('payment_success', true);
                }
            } catch (\Exception $e) {
                Log::error('Failed to finalize pending booking after payment', [
                    'invoice_id' => $invoice->id,
                    'pending_booking_token' => $pendingBookingToken,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Check if this payment was from a booking flow (legacy - for free bookings)
        $bookingAppointmentNumber = session('booking_appointment_number');
        if ($bookingAppointmentNumber) {
            // Clear the session
            session()->forget('booking_appointment_number');
            // Redirect to booking success page
            return redirect()->route('public.booking.success', [
                'appointmentNumber' => $bookingAppointmentNumber,
            ])->with('payment_success', true);
        }

        return view('public.billing.success', array_merge(compact('invoice', 'token'), ['logo_only' => true]));
    }

    /**
     * Finalize pending booking after payment is completed.
     * Creates patient, appointment, and billing records.
     */
    private function finalizePendingBookingAfterPayment(Invoice $invoice, string $pendingBookingToken): ?array
    {
        $bookingService = app(\App\Services\PublicBookingService::class);
        $pendingBooking = \App\Models\PendingBooking::where('booking_token', $pendingBookingToken)
            ->where('invoice_id', $invoice->id)
            ->where('status', 'pending_payment')
            ->first();

        if (!$pendingBooking) {
            Log::warning('Pending booking not found for finalization', [
                'invoice_id' => $invoice->id,
                'pending_booking_token' => $pendingBookingToken
            ]);
            return null;
        }

        // Verify payment is completed
        $hasCompletedPayment = $invoice->payments()
            ->where('status', 'completed')
            ->exists();

        if (!$hasCompletedPayment && $invoice->status !== 'paid') {
            Log::warning('Payment not completed for pending booking', [
                'invoice_id' => $invoice->id,
                'pending_booking_id' => $pendingBooking->id
            ]);
            return null;
        }

        Log::info('Finalizing pending booking after payment', [
            'pending_booking_id' => $pendingBooking->id,
            'invoice_id' => $invoice->id
        ]);

        return $bookingService->finalizeBookingAfterPayment($pendingBooking);
    }
    
    /**
     * Handle Stripe success callback, verify payment, update records, and send receipt email
     */
    private function handleStripeSuccessCallback($invoice, $sessionId)
    {
        try {
            // Find the payment record for this session
            $payment = \App\Models\Payment::where('invoice_id', $invoice->id)
                ->where(function($query) use ($sessionId) {
                    $query->where('gateway_transaction_id', $sessionId)
                          ->orWhere('gateway_response', 'like', '%' . $sessionId . '%');
                })
                ->first();
            
            if (!$payment) {
                \Log::warning('Payment record not found for Stripe session', [
                    'invoice_id' => $invoice->id,
                    'session_id' => $sessionId
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
                if ($payment->status !== 'completed') {
                    $payment->update([
                        'status' => 'completed',
                        'payment_date' => now()
                    ]);
                    $this->updateInvoiceAndBilling($invoice);
                }
            } else {
                // Verify the session with Stripe
                try {
                    $session = \Stripe\Checkout\Session::retrieve($sessionId);
                    
                    if ($session->payment_status === 'paid') {
                        // Get the actual amount paid from Stripe (convert from cents)
                        $amountPaid = $session->amount_total ? ($session->amount_total / 100) : $payment->amount;
                        
                        // Update payment record
                        if ($payment->status !== 'completed') {
                            $payment->update([
                                'status' => 'completed',
                                'amount' => $amountPaid, // Update with actual amount from Stripe
                                'payment_date' => now(),
                                'gateway_transaction_id' => $sessionId,
                                'gateway_response' => array_merge(
                                    $payment->gateway_response ?? [],
                                    [
                                        'session_status' => $session->status,
                                        'payment_status' => $session->payment_status,
                                        'amount_total' => $session->amount_total,
                                        'currency' => $session->currency
                                    ]
                                )
                            ]);
                            
                            // Update invoice and billing
                            $this->updateInvoiceAndBilling($invoice);
                            
                            \Log::info('Payment verified and updated from Stripe session', [
                                'payment_id' => $payment->id,
                                'invoice_id' => $invoice->id,
                                'amount_paid' => $amountPaid,
                                'session_id' => $sessionId
                            ]);
                        }
                    } else {
                        \Log::warning('Stripe session payment not completed', [
                            'session_id' => $sessionId,
                            'payment_status' => $session->payment_status
                        ]);
                        return;
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to verify Stripe session', [
                        'session_id' => $sessionId,
                        'error' => $e->getMessage()
                    ]);
                    return;
                }
            }
            
            // Send receipt email if payment is completed
            if ($payment->status === 'completed') {
                // Check if receipt already sent (to avoid duplicates)
                $receiptSent = Cache::get('receipt_sent_' . $payment->id, false);
                
                if (!$receiptSent) {
                    // Send receipt email
                    $emailService = app(\App\Services\HospitalEmailNotificationService::class);
                    $emailService->sendPaymentReceipt($invoice, $payment);
                    
                    // Mark receipt as sent (cache for 1 hour)
                    Cache::put('receipt_sent_' . $payment->id, true, 3600);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error handling Stripe success callback', [
                'invoice_id' => $invoice->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Update invoice and billing status after payment
     */
    private function updateInvoiceAndBilling($invoice)
    {
        try {
            // Refresh invoice to get latest payments
            $invoice->refresh();
            $invoice->load('payments');
            
            // Calculate total paid from all completed payments
            $totalPaid = $invoice->payments()
                ->where('status', 'completed')
                ->sum('amount');
            
            // Update invoice status
            if ($totalPaid >= $invoice->total_amount) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_date' => now(),
                ]);
            } elseif ($totalPaid > 0) {
                $invoice->update(['status' => 'partial']);
            }
            
            // Update billing if connected
            if ($invoice->billing_id) {
                $billing = \App\Models\Billing::find($invoice->billing_id);
                if ($billing) {
                    // Cap at total_amount to avoid overpayment from duplicate payment records
                    $paidAmount = min((float) $totalPaid, (float) $billing->total_amount);
                    if ($totalPaid > $billing->total_amount) {
                        \Log::warning('Payment sum exceeded billing total; capping paid_amount', [
                            'billing_id' => $billing->id,
                            'invoice_id' => $invoice->id,
                            'total_paid_sum' => $totalPaid,
                            'billing_total' => $billing->total_amount,
                        ]);
                    }
                    // Calculate balance
                    $balance = $billing->total_amount - $paidAmount;
                    // Determine status based on payment
                    $status = 'pending';
                    if ($paidAmount >= $billing->total_amount) {
                        $status = 'paid';
                    } elseif ($paidAmount > 0) {
                        $status = 'partially_paid';
                    }
                    // Update paid_amount, balance, and status
                    $billing->update([
                        'paid_amount' => $paidAmount,
                        'balance' => $balance,
                        'status' => $status,
                        'payment_method' => $billing->payment_method ?: 'card',
                        'payment_reference' => $billing->payment_reference ?: 'ONLINE_PAYMENT',
                        'paid_at' => $paidAmount >= $billing->total_amount ? now() : $billing->paid_at,
                    ]);
                    
                    \Log::info('Billing updated after payment', [
                        'billing_id' => $billing->id,
                        'invoice_id' => $invoice->id,
                        'total_paid' => $paidAmount,
                        'balance' => $balance,
                        'billing_status' => $billing->fresh()->status,
                        'billing_total' => $billing->total_amount
                    ]);
                } else {
                    \Log::warning('Billing record not found for invoice', [
                        'invoice_id' => $invoice->id,
                        'billing_id' => $invoice->billing_id
                    ]);
                }
            } else {
                \Log::warning('Invoice has no billing_id to update', [
                    'invoice_id' => $invoice->id
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error updating invoice and billing', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show invalid/expired link page
     */
    public function invalid(): View
    {
        return view('public.billing.invalid', ['logo_only' => true]);
    }

    /**
     * Get currency for payment
     */
    private function getCurrencyForPayment($invoice, $gateway): string
    {
        // 1. Check if invoice/billing has currency field
        if (isset($invoice->currency) && !empty($invoice->currency)) {
            return strtoupper($invoice->currency);
        }
        
        if ($invoice->billing && isset($invoice->billing->currency) && !empty($invoice->billing->currency)) {
            return strtoupper($invoice->billing->currency);
        }
        
        // 2. Check system settings
        $systemCurrency = $this->getSystemCurrency();
        if ($systemCurrency) {
            // Verify gateway supports this currency
            if ($gateway && method_exists($gateway, 'getSupportedCurrencies')) {
                $supportedCurrencies = $gateway->getSupportedCurrencies();
                if (in_array(strtoupper($systemCurrency), array_map('strtoupper', $supportedCurrencies))) {
                    return strtoupper($systemCurrency);
                }
            } else {
                return strtoupper($systemCurrency);
            }
        }
        
        // 3. Check app config
        $appCurrency = config('app.currency');
        if ($appCurrency) {
            return strtoupper($appCurrency);
        }
        
        // 4. Detect from timezone (UK = GBP, US = USD, etc.)
        $detectedCurrency = $this->detectCurrencyFromTimezone();
        if ($detectedCurrency) {
            return strtoupper($detectedCurrency);
        }
        
        // 5. Default based on domain (thanksdoc.co.uk = GBP)
        if (str_contains(config('app.url', ''), '.co.uk') || str_contains(config('app.url', ''), 'thanksdoc')) {
            return 'GBP';
        }
        
        // 6. Final fallback to GBP (UK-based system)
        return 'GBP';
    }
    
    /**
     * Get system currency from settings
     */
    private function getSystemCurrency(): ?string
    {
        try {
            if (class_exists('\App\Models\Setting')) {
                $setting = \App\Models\Setting::where('key', 'default_currency')->first();
                if ($setting && !empty($setting->value)) {
                    return $setting->value;
                }
            }
            
            if (\Schema::hasTable('site_settings')) {
                $currency = \DB::table('site_settings')
                    ->where('key', 'currency')
                    ->where('is_active', true)
                    ->value('value');
                if ($currency) {
                    return $currency;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Error getting system currency', ['error' => $e->getMessage()]);
        }
        
        return null;
    }
    
    /**
     * Detect currency from timezone
     */
    private function detectCurrencyFromTimezone(): ?string
    {
        $timezone = config('app.timezone', 'UTC');
        
        $timezoneCurrencyMap = [
            'Europe/London' => 'GBP',
            'America/New_York' => 'USD',
            'America/Chicago' => 'USD',
            'America/Denver' => 'USD',
            'America/Los_Angeles' => 'USD',
            'Europe/Paris' => 'EUR',
            'Europe/Berlin' => 'EUR',
            'Asia/Tokyo' => 'JPY',
            'Asia/Hong_Kong' => 'HKD',
            'Australia/Sydney' => 'AUD',
            'Africa/Lagos' => 'NGN',
            'Africa/Accra' => 'GHS',
        ];
        
        return $timezoneCurrencyMap[$timezone] ?? null;
    }
    
    /**
     * Generate transaction ID
     */
    private function generateTransactionId(): string
    {
        return 'TXN' . date('Ymd') . time() . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
}
