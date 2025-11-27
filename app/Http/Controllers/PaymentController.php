<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentGateway\PaymentGatewayFactory;
use App\Models\PaymentTransaction;
use App\Models\PaymentGateway;
use App\Models\Billing;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Show payment gateway selection
     */
    public function selectGateway(Request $request)
    {
        $billing = null;
        $amount = $request->input('amount');
        
        if ($request->has('billing_id')) {
            $billing = Billing::findOrFail($request->input('billing_id'));
            $amount = $billing->subtotal + $billing->tax - $billing->discount;
        }
        
        $gateways = PaymentGateway::active()->orderBySort()->get();
        
        return view('payment.select-gateway', compact('gateways', 'billing', 'amount'));
    }

    /**
     * Create and initialize payment
     */
    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:10',
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'billing_id' => 'nullable|exists:billings,id',
            'description' => 'nullable|string',
            'crypto_currency' => 'nullable|string'
        ]);

        try {
            $paymentGateway = PaymentGateway::findOrFail($validated['payment_gateway_id']);
            $gateway = PaymentGatewayFactory::createFromModel($paymentGateway);

            // Calculate gateway fee
            $gatewayFee = $paymentGateway->calculateFee($validated['amount']);
            
            $paymentData = [
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'order_id' => 'HOS_' . time() . '_' . $request->user()->id,
                'title' => 'Hospital Services Payment',
                'description' => $validated['description'] ?? 'Payment for medical services',
                'customer_email' => $request->user()->email,
                'callback_url' => route('payment.webhook', ['provider' => $paymentGateway->provider]),
                'success_url' => route('payment.success'),
                'cancel_url' => route('payment.cancelled'),
                'receive_currency' => $validated['crypto_currency'] ?? 'BTC'
            ];

            $response = $gateway->createPayment($paymentData);

            if (!$response['success']) {
                throw new Exception($response['error']);
            }

            // Create transaction record
            $transaction = PaymentTransaction::create([
                'billing_id' => $validated['billing_id'],
                'user_id' => $request->user()->id,
                'payment_gateway_id' => $validated['payment_gateway_id'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'gateway_fee' => $gatewayFee,
                'net_amount' => $validated['amount'] - $gatewayFee,
                'gateway_transaction_id' => $response['payment_id'],
                'gateway_payment_url' => $response['payment_url'],
                'status' => 'pending',
                'gateway_status' => $response['status'] ?? 'new',
                'crypto_currency' => $validated['crypto_currency'],
                'expires_at' => isset($response['expires_at']) ? $response['expires_at'] : now()->addHours(1),
                'gateway_response' => $response['gateway_response']
            ]);

            Log::info('Payment created', [
                'transaction_id' => $transaction->transaction_id,
                'gateway' => $paymentGateway->provider,
                'amount' => $validated['amount']
            ]);

            return redirect()->to($response['payment_url']);

        } catch (Exception $e) {
            Log::error('Payment creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
                'data' => $validated
            ]);
            
            return back()->withErrors(['error' => 'Payment initiation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle payment success
     */
    public function paymentSuccess(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $transaction = null;
        
        if ($transactionId) {
            $transaction = PaymentTransaction::where('gateway_transaction_id', $transactionId)
                ->orWhere('transaction_id', $transactionId)
                ->first();
        }
        
        return view('payment.success', compact('transaction'));
    }

    /**
     * Handle payment cancellation
     */
    public function paymentCancelled(Request $request)
    {
        return view('payment.cancelled');
    }

    /**
     * Handle Paystack callback (redirect after payment)
     */
    public function paystackCallback(Request $request)
    {
        try {
            $reference = $request->get('reference');
            $trxref = $request->get('trxref'); // Also check for trxref parameter
            
            if (!$reference && !$trxref) {
                Log::warning('Paystack callback missing reference', $request->all());
                return redirect()->route('payment.cancelled')
                    ->with('error', 'Payment reference not found.');
            }
            
            // Use reference or trxref as the transaction reference
            $transactionRef = $reference ?: $trxref;
            
            // Find transaction by reference (order_id)
            $transaction = PaymentTransaction::where('gateway_transaction_id', $transactionRef)
                ->orWhereJsonContains('gateway_response->reference', $transactionRef)
                ->first();
            
            // Also check patient payments
            $patientPayment = \App\Models\Payment::where('gateway_transaction_id', $transactionRef)
                ->orWhereJsonContains('gateway_response->reference', $transactionRef)
                ->first();
            
            Log::info('Paystack callback received', [
                'reference' => $transactionRef,
                'transaction_found' => !!$transaction,
                'patient_payment_found' => !!$patientPayment,
                'all_params' => $request->all()
            ]);
            
            if ($transaction || $patientPayment) {
                // Check payment status directly with Paystack
                try {
                    $paymentGateway = PaymentGateway::where('provider', 'paystack')->active()->first();
                    if ($paymentGateway) {
                        $gateway = PaymentGatewayFactory::createFromModel($paymentGateway);
                        $statusResponse = $gateway->getPaymentStatus($transactionRef);
                        
                        if ($statusResponse['success']) {
                            // Update transaction status
                            if ($transaction) {
                                $transaction->update([
                                    'status' => $statusResponse['status'],
                                    'gateway_status' => $statusResponse['original_status'],
                                    'paid_at' => $statusResponse['status'] === 'completed' ? now() : $transaction->paid_at
                                ]);
                                
                // Update billing if completed
                if ($statusResponse['status'] === 'completed' && $transaction->billing) {
                    $transaction->billing->update(['status' => 'paid']);
                    
                    // Send payment completion notification
                    try {
                        $this->notificationService->sendBillingNotification(
                            $transaction->billing,
                            'payment_received',
                            $transaction->amount
                        );
                    } catch (Exception $e) {
                        Log::error('Failed to send payment completion notification', [
                            'billing_id' => $transaction->billing->id,
                            'transaction_id' => $transaction->transaction_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                            }
                            
                            // Update patient payment
                            if ($patientPayment) {
                                $patientPayment->update([
                                    'status' => $statusResponse['status'],
                                    'gateway_response' => $statusResponse,
                                ]);
                                
                                // Update invoice status if payment completed
                                if ($statusResponse['status'] === 'completed' && $patientPayment->invoice) {
                                    $this->updateInvoiceStatus($patientPayment->invoice);
                                    
                                    // Also update the admin billing if connected
                                    if ($patientPayment->invoice->billing_id) {
                                        $adminBilling = Billing::find($patientPayment->invoice->billing_id);
                                        if ($adminBilling) {
                                            $adminBilling->update(['status' => 'paid']);
                                            Log::info('Admin billing updated from patient payment', [
                                                'billing_id' => $adminBilling->id,
                                                'invoice_id' => $patientPayment->invoice->id,
                                                'payment_id' => $patientPayment->id
                                            ]);
                                        }
                                    }
                                }
                            }
                            
                            // Determine where to redirect based on payment type
                            if ($statusResponse['status'] === 'completed') {
                                if ($patientPayment && $patientPayment->invoice) {
                                    // Patient payment - authenticate the patient and redirect to patient billing
                                    $patient = $patientPayment->invoice->patient;
                                    if ($patient) {
                                        \Auth::guard('patient')->login($patient);
                                        Log::info('Patient re-authenticated after Paystack payment', [
                                            'patient_id' => $patient->id,
                                            'payment_id' => $patientPayment->id
                                        ]);
                                    }
                                    return redirect()->route('patient.billing.show', $patientPayment->invoice)
                                        ->with('success', 'Payment completed successfully!');
                                } else {
                                    // Staff/general payment - redirect to general success
                                    return redirect()->route('payment.success', ['transaction_id' => $transactionRef])
                                        ->with('success', 'Payment completed successfully!');
                                }
                            } else {
                                // Payment not completed
                                if ($patientPayment && $patientPayment->invoice) {
                                    // Patient payment - authenticate the patient
                                    $patient = $patientPayment->invoice->patient;
                                    if ($patient) {
                                        \Auth::guard('patient')->login($patient);
                                    }
                                    return redirect()->route('patient.billing.show', $patientPayment->invoice)
                                        ->with('error', 'Payment was not completed. Status: ' . $statusResponse['original_status']);
                                } else {
                                    return redirect()->route('payment.cancelled')
                                        ->with('error', 'Payment was not completed. Status: ' . $statusResponse['original_status']);
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Failed to verify Paystack payment status', [
                        'reference' => $transactionRef,
                        'error' => $e->getMessage()
                    ]);
                }
                
                // Fallback - redirect based on payment type
                if ($patientPayment && $patientPayment->invoice) {
                    // Authenticate the patient before redirecting
                    $patient = $patientPayment->invoice->patient;
                    if ($patient) {
                        \Auth::guard('patient')->login($patient);
                    }
                    return redirect()->route('patient.billing.show', $patientPayment->invoice)
                        ->with('info', 'Payment received. Status will be updated once confirmed.');
                } else {
                    return redirect()->route('payment.success', ['transaction_id' => $transactionRef])
                        ->with('info', 'Payment received. Status will be updated once confirmed.');
                }
            }
            
            // No transaction found - redirect to cancelled
            return redirect()->route('payment.cancelled')
                ->with('error', 'Transaction not found.');
                
        } catch (Exception $e) {
            Log::error('Paystack callback error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return redirect()->route('payment.cancelled')
                ->with('error', 'An error occurred processing your payment.');
        }
    }

    public function btcPayCallback(Request $request)
    {
        try {
            // Log all parameters received from BTCPay Server
            Log::info('BTCPay Server callback parameters received', $request->all());
            
            $paymentId = $request->get('payment_id');
            $invoiceId = $request->get('invoice_id');
            $status = $request->get('status');
            $btcpayInvoiceId = $request->get('invoiceId'); // BTCPay's invoice ID
            
            // Check if we have URL parameters first (our custom parameters)
            if ($paymentId && $invoiceId) {
                Log::info('Using URL parameters for BTCPay callback', [
                    'payment_id' => $paymentId,
                    'invoice_id' => $invoiceId
                ]);
                
                // Find payment and invoice directly using our parameters
                $patientPayment = \App\Models\Payment::find($paymentId);
                $invoice = \App\Models\Invoice::find($invoiceId);
                
                if (!$patientPayment || !$invoice || $patientPayment->invoice_id != $invoice->id) {
                    Log::warning('Invalid payment or invoice ID in BTCPay callback', [
                        'payment_id' => $paymentId,
                        'invoice_id' => $invoiceId,
                        'payment_found' => !!$patientPayment,
                        'invoice_found' => !!$invoice
                    ]);
                    return redirect()->route('payment.cancelled')
                        ->with('error', 'Invalid payment reference.');
                }
                
                // Authenticate the patient
                $patient = $invoice->patient;
                if ($patient) {
                    \Auth::guard('patient')->login($patient);
                    Log::info('Patient authenticated for BTCPay callback', [
                        'patient_id' => $patient->id,
                        'payment_id' => $patientPayment->id
                    ]);
                }
                
                // Update payment status to completed for now
                // In a real scenario, you'd verify with BTCPay Server API
                $patientPayment->update([
                    'status' => 'completed',
                    'payment_date' => now(),
                ]);
                
                // Update invoice status
                $this->updateInvoiceStatus($invoice);
                
                return redirect()->route('patient.billing.show', $invoice)
                    ->with('success', 'Payment completed successfully!');
            }
            
            // Fallback to BTCPay Server parameters if available
            if (!$btcpayInvoiceId && !$status) {
                Log::warning('BTCPay callback missing reference', $request->all());
                return redirect()->route('payment.cancelled')
                    ->with('error', 'Payment reference not found.');
            }
            
            $transactionRef = $btcpayInvoiceId;
            
            // Find transaction by reference
            $transaction = PaymentTransaction::where('gateway_transaction_id', $transactionRef)
                ->orWhereJsonContains('gateway_response->invoiceId', $transactionRef)
                ->first();
            
            // Also check patient payments
            $patientPayment = \App\Models\Payment::where('gateway_transaction_id', $transactionRef)
                ->orWhereJsonContains('gateway_response->invoiceId', $transactionRef)
                ->first();
            
            Log::info('BTCPay callback received', [
                'btcpay_invoice_id' => $btcpayInvoiceId,
                'status' => $status,
                'transaction_found' => !!$transaction,
                'patient_payment_found' => !!$patientPayment,
                'all_params' => $request->all()
            ]);
            
            if ($transaction || $patientPayment) {
                // Check payment status directly with BTCPay Server if status indicates completion
                if (in_array($status, ['Settled', 'Processing', 'Confirmed'])) {
                    try {
                        $paymentGateway = PaymentGateway::where('provider', 'btcpay')->active()->first();
                        if ($paymentGateway) {
                            $gateway = PaymentGatewayFactory::createFromModel($paymentGateway);
                            $statusResponse = $gateway->getPaymentStatus($transactionRef);
                            
                            if ($statusResponse['success']) {
                                // Update transaction status
                                if ($transaction) {
                                    $transaction->update([
                                        'status' => $statusResponse['status'],
                                        'gateway_status' => $statusResponse['original_status'],
                                        'paid_at' => $statusResponse['status'] === 'completed' ? now() : $transaction->paid_at
                                    ]);
                                    
                                    // Update billing if completed
                                    if ($statusResponse['status'] === 'completed' && $transaction->billing) {
                                        $transaction->billing->update(['status' => 'paid']);
                                    }
                                }
                                
                                // Update patient payment
                                if ($patientPayment) {
                                    $patientPayment->update([
                                        'status' => $statusResponse['status'],
                                        'gateway_response' => $statusResponse,
                                    ]);
                                    
                                    // Update invoice status if payment completed
                                    if ($statusResponse['status'] === 'completed' && $patientPayment->invoice) {
                                        $this->updateInvoiceStatus($patientPayment->invoice);
                                        
                                        // Also update the admin billing if connected
                                        if ($patientPayment->invoice->billing_id) {
                                            $adminBilling = Billing::find($patientPayment->invoice->billing_id);
                                            if ($adminBilling) {
                                                $adminBilling->update(['status' => 'paid']);
                                                Log::info('Admin billing updated from patient payment', [
                                                    'billing_id' => $adminBilling->id,
                                                    'invoice_id' => $patientPayment->invoice->id,
                                                    'payment_id' => $patientPayment->id
                                                ]);
                                            }
                                        }
                                    }
                                }
                                
                                // Determine where to redirect based on payment type
                                if ($statusResponse['status'] === 'completed') {
                                    if ($patientPayment && $patientPayment->invoice) {
                                        // Patient payment - authenticate the patient and redirect to patient billing
                                        $patient = $patientPayment->invoice->patient;
                                        if ($patient) {
                                            \Auth::guard('patient')->login($patient);
                                            Log::info('Patient re-authenticated after BTCPay payment', [
                                                'patient_id' => $patient->id,
                                                'payment_id' => $patientPayment->id
                                            ]);
                                        }
                                        return redirect()->route('patient.billing.show', $patientPayment->invoice)
                                            ->with('success', 'Payment completed successfully!');
                                    } else {
                                        // Staff/general payment - redirect to general success
                                        return redirect()->route('payment.success', ['transaction_id' => $transactionRef])
                                            ->with('success', 'Payment completed successfully!');
                                    }
                                } else {
                                    // Payment not completed yet but processing
                                    if ($patientPayment && $patientPayment->invoice) {
                                        // Patient payment - authenticate the patient
                                        $patient = $patientPayment->invoice->patient;
                                        if ($patient) {
                                            \Auth::guard('patient')->login($patient);
                                        }
                                        return redirect()->route('patient.billing.show', $patientPayment->invoice)
                                            ->with('info', 'Payment is being processed. Status: ' . $statusResponse['original_status']);
                                    } else {
                                        return redirect()->route('payment.success', ['transaction_id' => $transactionRef])
                                            ->with('info', 'Payment is being processed. Status: ' . $statusResponse['original_status']);
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        Log::error('Failed to verify BTCPay Server payment status', [
                            'btcpay_invoice_id' => $btcpayInvoiceId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Handle cancelled/failed status
                if (in_array($status, ['Expired', 'Invalid'])) {
                    if ($patientPayment && $patientPayment->invoice) {
                        // Update payment status
                        $patientPayment->update(['status' => 'failed']);
                        // Authenticate the patient before redirecting
                        $patient = $patientPayment->invoice->patient;
                        if ($patient) {
                            \Auth::guard('patient')->login($patient);
                        }
                        return redirect()->route('patient.billing.show', $patientPayment->invoice)
                            ->with('error', 'Payment was ' . strtolower($status) . '.');
                    } else {
                        if ($transaction) {
                            $transaction->update(['status' => 'failed']);
                        }
                        return redirect()->route('payment.cancelled')
                            ->with('error', 'Payment was ' . strtolower($status) . '.');
                    }
                }
                
                // Fallback - redirect based on payment type with status info
                if ($patientPayment && $patientPayment->invoice) {
                    // Authenticate the patient before redirecting
                    $patient = $patientPayment->invoice->patient;
                    if ($patient) {
                        \Auth::guard('patient')->login($patient);
                    }
                    return redirect()->route('patient.billing.show', $patientPayment->invoice)
                        ->with('info', 'Payment received. Status will be updated once confirmed.');
                } else {
                    return redirect()->route('payment.success', ['transaction_id' => $transactionRef])
                        ->with('info', 'Payment received. Status will be updated once confirmed.');
                }
            }
            
            // No transaction found - redirect to cancelled
            return redirect()->route('payment.cancelled')
                ->with('error', 'Transaction not found.');
                
        } catch (Exception $e) {
            Log::error('BTCPay Server callback error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return redirect()->route('payment.cancelled')
                ->with('error', 'An error occurred processing your payment.');
        }
    }
    /**
     * Handle webhooks from payment gateways
     */
    public function handleWebhook(Request $request, $provider)
    {
        try {
            Log::info('Webhook received', [
                'provider' => $provider,
                'data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            $paymentGateway = PaymentGateway::where('provider', $provider)->active()->firstOrFail();
            $gateway = PaymentGatewayFactory::createFromModel($paymentGateway);

            $response = $gateway->processWebhook($request->all());

            if (!$response['success']) {
                throw new Exception($response['error']);
            }

            // Find and update transaction (general payment system)
            $transaction = PaymentTransaction::where('gateway_transaction_id', $response['payment_id'])->first();
            $patientPayment = \App\Models\Payment::where('gateway_transaction_id', $response['payment_id'])->first();

            if ($transaction) {
                $oldStatus = $transaction->status;
                $transaction->update([
                    'status' => $response['status'],
                    'gateway_status' => $response['original_status'],
                    'webhook_data' => $response,
                    'paid_at' => $response['status'] === 'completed' ? now() : $transaction->paid_at
                ]);

                // Log status change
                Log::info('Transaction status updated', [
                    'transaction_id' => $transaction->transaction_id,
                    'old_status' => $oldStatus,
                    'new_status' => $response['status'],
                    'provider' => $provider
                ]);

                // Update billing if completed
                if ($response['status'] === 'completed' && $transaction->billing) {
                    $transaction->billing->update(['status' => 'paid']);
                    
                    // Send payment completion notification
                    try {
                        $this->notificationService->sendBillingNotification(
                            $transaction->billing,
                            'payment_received',
                            $transaction->amount
                        );
                    } catch (Exception $e) {
                        Log::error('Failed to send webhook payment completion notification', [
                            'billing_id' => $transaction->billing->id,
                            'transaction_id' => $transaction->transaction_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Handle patient billing payments
            if ($patientPayment) {
                $oldStatus = $patientPayment->status;
                $patientPayment->update([
                    'status' => $response['status'],
                    'gateway_response' => $response,
                ]);

                // Log status change
                Log::info('Patient payment status updated', [
                    'payment_id' => $patientPayment->id,
                    'transaction_id' => $patientPayment->transaction_id,
                    'old_status' => $oldStatus,
                    'new_status' => $response['status'],
                    'provider' => $provider
                ]);

                // Update invoice status if payment completed
                if ($response['status'] === 'completed' && $patientPayment->invoice) {
                    $this->updateInvoiceStatus($patientPayment->invoice);
                    
                    // Send payment receipt email
                    try {
                        $emailService = app(\App\Services\HospitalEmailNotificationService::class);
                        $emailService->sendPaymentReceipt($patientPayment->invoice, $patientPayment);
                        Log::info('Payment receipt email sent via webhook', [
                            'invoice_id' => $patientPayment->invoice->id,
                            'payment_id' => $patientPayment->id
                        ]);
                    } catch (Exception $e) {
                        Log::error('Failed to send payment receipt email via webhook', [
                            'invoice_id' => $patientPayment->invoice->id,
                            'payment_id' => $patientPayment->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                    // Also update the admin billing if connected
                    // Note: syncToAdminBilling already handles this, but we ensure it's done here too
                    if ($patientPayment->invoice->billing_id) {
                        $adminBilling = Billing::find($patientPayment->invoice->billing_id);
                        if ($adminBilling) {
                            // Calculate total paid from all completed payments
                            $totalPaid = $patientPayment->invoice->payments()
                                ->where('status', 'completed')
                                ->sum('amount');
                            
                            // Update paid_amount - the model's saving event will automatically update status to 'paid' if paid_amount >= total_amount
                            $adminBilling->update([
                                'paid_amount' => $totalPaid,
                                'payment_method' => $patientPayment->payment_method ?? 'card',
                                'payment_reference' => $patientPayment->transaction_id ?? 'ONLINE_PAYMENT',
                                'paid_at' => $totalPaid >= $adminBilling->total_amount ? now() : $adminBilling->paid_at,
                            ]);
                            
                            Log::info('Admin billing updated from patient payment via webhook', [
                                'billing_id' => $adminBilling->id,
                                'invoice_id' => $patientPayment->invoice->id,
                                'payment_id' => $patientPayment->id,
                                'total_paid' => $totalPaid,
                                'billing_total' => $adminBilling->total_amount,
                                'billing_status' => $adminBilling->fresh()->status
                            ]);
                        }
                    }
                }
            }

            return response()->json(['message' => 'Webhook processed successfully'], 200);

        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus(Request $request, PaymentTransaction $transaction)
    {
        try {
            $gateway = PaymentGatewayFactory::createFromModel($transaction->paymentGateway);
            $response = $gateway->getPaymentStatus($transaction->gateway_transaction_id);

            if ($response['success']) {
                $transaction->update([
                    'status' => $response['status'],
                    'gateway_status' => $response['original_status'],
                    'paid_at' => $response['status'] === 'completed' ? now() : $transaction->paid_at
                ]);
            }

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show transaction details
     */
    public function showTransaction(PaymentTransaction $transaction)
    {
        return view('payment.transaction', compact('transaction'));
    }

    /**
     * List user transactions
     */
    public function listTransactions(Request $request)
    {
        $transactions = PaymentTransaction::where('user_id', $request->user()->id)
            ->with(['billing', 'paymentGateway'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('payment.transactions', compact('transactions'));
    }

    /**
     * Handle Flutterwave callback (redirect after payment)
     */
    public function flutterwaveCallback(Request $request)
    {
        try {
            // Log all parameters received from Flutterwave
            Log::info('Flutterwave callback parameters received', $request->all());
            
            $status = $request->get('status');
            $tx_ref = $request->get('tx_ref');
            $transaction_id = $request->get('transaction_id');
            $flw_ref = $request->get('flw_ref'); // Flutterwave's own reference
            
            if (!$tx_ref && !$transaction_id && !$flw_ref) {
                Log::warning('Flutterwave callback missing reference', $request->all());
                return redirect()->route('payment.cancelled')
                    ->with('error', 'Payment reference not found.');
            }
            
            // Use tx_ref or transaction_id as the transaction reference
            $transactionRef = $tx_ref ?: $transaction_id;
            
            // Find transaction by reference (order_id)
            $transaction = PaymentTransaction::where('gateway_transaction_id', $transactionRef)
                ->orWhereJsonContains('gateway_response->tx_ref', $transactionRef)
                ->first();
            
            // Also check patient payments
            $patientPayment = \App\Models\Payment::where('gateway_transaction_id', $transactionRef)
                ->orWhereJsonContains('gateway_response->tx_ref', $transactionRef)
                ->first();
            
            Log::info('Flutterwave callback received', [
                'tx_ref' => $transactionRef,
                'status' => $status,
                'transaction_found' => !!$transaction,
                'patient_payment_found' => !!$patientPayment,
                'all_params' => $request->all()
            ]);
            
            if ($transaction || $patientPayment) {
                // Check payment status directly with Flutterwave if status is successful
                if ($status === 'successful') {
                    try {
                        $paymentGateway = PaymentGateway::where('provider', 'flutterwave')->active()->first();
                        if ($paymentGateway) {
                            $gateway = PaymentGatewayFactory::createFromModel($paymentGateway);
                            // Use Flutterwave's transaction_id for verification, not our tx_ref
                            $flutterwaveTransactionId = $request->get('transaction_id');
                            $statusResponse = $gateway->getPaymentStatus($flutterwaveTransactionId ?: $transactionRef);
                            
                            if ($statusResponse['success']) {
                                // Update transaction status
                                if ($transaction) {
                                    $transaction->update([
                                        'status' => $statusResponse['status'],
                                        'gateway_status' => $statusResponse['original_status'],
                                        'paid_at' => $statusResponse['status'] === 'completed' ? now() : $transaction->paid_at
                                    ]);
                                    
                                    // Update billing if completed
                                    if ($statusResponse['status'] === 'completed' && $transaction->billing) {
                                        $transaction->billing->update(['status' => 'paid']);
                                    }
                                }
                                
                                // Update patient payment
                                if ($patientPayment) {
                                    $patientPayment->update([
                                        'status' => $statusResponse['status'],
                                        'gateway_response' => $statusResponse,
                                    ]);
                                    
                                    // Update invoice status if payment completed
                                    if ($statusResponse['status'] === 'completed' && $patientPayment->invoice) {
                                        $this->updateInvoiceStatus($patientPayment->invoice);
                                        
                                        // Also update the admin billing if connected
                                        if ($patientPayment->invoice->billing_id) {
                                            $adminBilling = Billing::find($patientPayment->invoice->billing_id);
                                            if ($adminBilling) {
                                                $adminBilling->update(['status' => 'paid']);
                                                Log::info('Admin billing updated from patient payment', [
                                                    'billing_id' => $adminBilling->id,
                                                    'invoice_id' => $patientPayment->invoice->id,
                                                    'payment_id' => $patientPayment->id
                                                ]);
                                            }
                                        }
                                    }
                                }
                                
                                // Determine where to redirect based on payment type
                                if ($statusResponse['status'] === 'completed') {
                                    if ($patientPayment && $patientPayment->invoice) {
                                        // Patient payment - authenticate the patient and redirect to patient billing
                                        $patient = $patientPayment->invoice->patient;
                                        if ($patient) {
                                            \Auth::guard('patient')->login($patient);
                                            Log::info('Patient re-authenticated after Flutterwave payment', [
                                                'patient_id' => $patient->id,
                                                'payment_id' => $patientPayment->id
                                            ]);
                                        }
                                        return redirect()->route('patient.billing.show', $patientPayment->invoice)
                                            ->with('success', 'Payment completed successfully!');
                                    } else {
                                        // Staff/general payment - redirect to general success
                                        return redirect()->route('payment.success', ['transaction_id' => $transactionRef])
                                            ->with('success', 'Payment completed successfully!');
                                    }
                                } else {
                                    // Payment not completed
                                    if ($patientPayment && $patientPayment->invoice) {
                                        // Patient payment - authenticate the patient
                                        $patient = $patientPayment->invoice->patient;
                                        if ($patient) {
                                            \Auth::guard('patient')->login($patient);
                                        }
                                        return redirect()->route('patient.billing.show', $patientPayment->invoice)
                                            ->with('error', 'Payment was not completed. Status: ' . $statusResponse['original_status']);
                                    } else {
                                        return redirect()->route('payment.cancelled')
                                            ->with('error', 'Payment was not completed. Status: ' . $statusResponse['original_status']);
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        Log::error('Failed to verify Flutterwave payment status', [
                            'tx_ref' => $transactionRef,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Handle cancelled/failed status or fallback
                if ($status === 'cancelled') {
                    if ($patientPayment && $patientPayment->invoice) {
                        // Authenticate the patient before redirecting
                        $patient = $patientPayment->invoice->patient;
                        if ($patient) {
                            \Auth::guard('patient')->login($patient);
                        }
                        return redirect()->route('patient.billing.show', $patientPayment->invoice)
                            ->with('error', 'Payment was cancelled.');
                    } else {
                        return redirect()->route('payment.cancelled')
                            ->with('error', 'Payment was cancelled.');
                    }
                }
                
                // Fallback - redirect based on payment type
                if ($patientPayment && $patientPayment->invoice) {
                    // Authenticate the patient before redirecting
                    $patient = $patientPayment->invoice->patient;
                    if ($patient) {
                        \Auth::guard('patient')->login($patient);
                    }
                    return redirect()->route('patient.billing.show', $patientPayment->invoice)
                        ->with('info', 'Payment received. Status will be updated once confirmed.');
                } else {
                    return redirect()->route('payment.success', ['transaction_id' => $transactionRef])
                        ->with('info', 'Payment received. Status will be updated once confirmed.');
                }
            }
            
            // No transaction found - redirect to cancelled
            return redirect()->route('payment.cancelled')
                ->with('error', 'Transaction not found.');
                
        } catch (Exception $e) {
            Log::error('Flutterwave callback error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return redirect()->route('payment.cancelled')
                ->with('error', 'An error occurred processing your payment.');
        }
    }

    /**
     * Handle CoinGate callback (redirect after payment)
     */
    public function coinGateCallback(Request $request)
    {
        try {
            // Log all parameters received from CoinGate
            Log::info('CoinGate callback parameters received', $request->all());
            
            $orderId = $request->get('order_id');
            $token = $request->get('token');
            $status = $request->get('status');
            $paymentId = $request->get('payment_id');
            $invoiceId = $request->get('invoice_id');
            
            // Check if we have URL parameters first (our custom parameters)
            if ($paymentId && $invoiceId) {
                Log::info('Using URL parameters for CoinGate callback', [
                    'payment_id' => $paymentId,
                    'invoice_id' => $invoiceId
                ]);
                
                // Find payment and invoice directly using our parameters
                $patientPayment = \App\Models\Payment::find($paymentId);
                $invoice = \App\Models\Invoice::find($invoiceId);
                $transaction = null; // We're dealing with patient payments here
                
                if (!$patientPayment || !$invoice || $patientPayment->invoice_id != $invoice->id) {
                    Log::warning('Invalid payment or invoice ID in CoinGate callback', [
                        'payment_id' => $paymentId,
                        'invoice_id' => $invoiceId,
                        'payment_found' => !!$patientPayment,
                        'invoice_found' => !!$invoice
                    ]);
                    return redirect()->route('payment.cancelled')
                        ->with('error', 'Invalid payment reference.');
                }
                
                // Authenticate the patient
                $patient = $invoice->patient;
                if ($patient) {
                    \Auth::guard('patient')->login($patient);
                    Log::info('Patient authenticated for CoinGate callback', [
                        'patient_id' => $patient->id,
                        'payment_id' => $patientPayment->id
                    ]);
                }
                
                // Update payment status to completed for now
                // In a real scenario, you'd verify with CoinGate API
                $patientPayment->update([
                    'status' => 'completed',
                    'payment_date' => now(),
                ]);
                
                // Update invoice status
                $this->updateInvoiceStatus($invoice);
                
                return redirect()->route('patient.billing.show', $invoice)
                    ->with('success', 'Payment completed successfully!');
            }
            
            // Fallback to original CoinGate parameters if available
            if (!$orderId && !$token) {
                Log::warning('CoinGate callback missing reference', $request->all());
                return redirect()->route('payment.cancelled')
                    ->with('error', 'Payment reference not found.');
            }
            
            // Use order_id or token as the transaction reference
            $transactionRef = $orderId ?: $token;
            
            // Find transaction by reference (order_id)
            $transaction = PaymentTransaction::where('gateway_transaction_id', $transactionRef)
                ->orWhereJsonContains('gateway_response->order_id', $transactionRef)
                ->orWhereJsonContains('gateway_response->token', $transactionRef)
                ->first();
            
            // Also check patient payments
            $patientPayment = \App\Models\Payment::where('gateway_transaction_id', $transactionRef)
                ->orWhereJsonContains('gateway_response->order_id', $transactionRef)
                ->orWhereJsonContains('gateway_response->token', $transactionRef)
                ->first();
            
            Log::info('CoinGate callback received', [
                'order_id' => $orderId,
                'token' => $token,
                'status' => $status,
                'transaction_found' => !!$transaction,
                'patient_payment_found' => !!$patientPayment,
                'all_params' => $request->all()
            ]);
            
            if ($transaction || $patientPayment) {
                // Check payment status directly with CoinGate if status indicates completion
                if (in_array($status, ['paid', 'confirming'])) {
                    try {
                        $paymentGateway = PaymentGateway::where('provider', 'coingate')->active()->first();
                        if ($paymentGateway) {
                            $gateway = PaymentGatewayFactory::createFromModel($paymentGateway);
                            $statusResponse = $gateway->getPaymentStatus($transactionRef);
                            
                            if ($statusResponse['success']) {
                                // Update transaction status
                                if ($transaction) {
                                    $transaction->update([
                                        'status' => $statusResponse['status'],
                                        'gateway_status' => $statusResponse['original_status'],
                                        'paid_at' => $statusResponse['status'] === 'completed' ? now() : $transaction->paid_at
                                    ]);
                                    
                                    // Update billing if completed
                                    if ($statusResponse['status'] === 'completed' && $transaction->billing) {
                                        $transaction->billing->update(['status' => 'paid']);
                                    }
                                }
                                
                                // Update patient payment
                                if ($patientPayment) {
                                    $patientPayment->update([
                                        'status' => $statusResponse['status'],
                                        'gateway_response' => $statusResponse,
                                    ]);
                                    
                                    // Update invoice status if payment completed
                                    if ($statusResponse['status'] === 'completed' && $patientPayment->invoice) {
                                        $this->updateInvoiceStatus($patientPayment->invoice);
                                        
                                        // Also update the admin billing if connected
                                        if ($patientPayment->invoice->billing_id) {
                                            $adminBilling = Billing::find($patientPayment->invoice->billing_id);
                                            if ($adminBilling) {
                                                $adminBilling->update(['status' => 'paid']);
                                                Log::info('Admin billing updated from patient payment', [
                                                    'billing_id' => $adminBilling->id,
                                                    'invoice_id' => $patientPayment->invoice->id,
                                                    'payment_id' => $patientPayment->id
                                                ]);
                                            }
                                        }
                                    }
                                }
                                
                                // Determine where to redirect based on payment type
                                if ($statusResponse['status'] === 'completed') {
                                    if ($patientPayment && $patientPayment->invoice) {
                                        // Patient payment - authenticate the patient and redirect to patient billing
                                        $patient = $patientPayment->invoice->patient;
                                        if ($patient) {
                                            \Auth::guard('patient')->login($patient);
                                            Log::info('Patient re-authenticated after CoinGate payment', [
                                                'patient_id' => $patient->id,
                                                'payment_id' => $patientPayment->id
                                            ]);
                                        }
                                        return redirect()->route('patient.billing.show', $patientPayment->invoice)
                                            ->with('success', 'Payment completed successfully!');
                                    } else {
                                        // Staff/general payment - redirect to general success
                                        return redirect()->route('payment.success', ['transaction_id' => $transactionRef])
                                            ->with('success', 'Payment completed successfully!');
                                    }
                                } else {
                                    // Payment not completed yet but processing
                                    if ($patientPayment && $patientPayment->invoice) {
                                        // Patient payment - authenticate the patient
                                        $patient = $patientPayment->invoice->patient;
                                        if ($patient) {
                                            \Auth::guard('patient')->login($patient);
                                        }
                                        return redirect()->route('patient.billing.show', $patientPayment->invoice)
                                            ->with('info', 'Payment is being processed. Status: ' . $statusResponse['original_status']);
                                    } else {
                                        return redirect()->route('payment.success', ['transaction_id' => $transactionRef])
                                            ->with('info', 'Payment is being processed. Status: ' . $statusResponse['original_status']);
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        Log::error('Failed to verify CoinGate payment status', [
                            'order_id' => $orderId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Handle cancelled/failed status
                if (in_array($status, ['cancelled', 'invalid', 'expired'])) {
                    if ($patientPayment && $patientPayment->invoice) {
                        // Update payment status
                        $patientPayment->update(['status' => 'failed']);
                        // Authenticate the patient before redirecting
                        $patient = $patientPayment->invoice->patient;
                        if ($patient) {
                            \Auth::guard('patient')->login($patient);
                        }
                        return redirect()->route('patient.billing.show', $patientPayment->invoice)
                            ->with('error', 'Payment was ' . $status . '.');
                    } else {
                        if ($transaction) {
                            $transaction->update(['status' => 'failed']);
                        }
                        return redirect()->route('payment.cancelled')
                            ->with('error', 'Payment was ' . $status . '.');
                    }
                }
                
                // Fallback - redirect based on payment type with status info
                if ($patientPayment && $patientPayment->invoice) {
                    // Authenticate the patient before redirecting
                    $patient = $patientPayment->invoice->patient;
                    if ($patient) {
                        \Auth::guard('patient')->login($patient);
                    }
                    return redirect()->route('patient.billing.show', $patientPayment->invoice)
                        ->with('info', 'Payment received. Status will be updated once confirmed.');
                } else {
                    return redirect()->route('payment.success', ['transaction_id' => $transactionRef])
                        ->with('info', 'Payment received. Status will be updated once confirmed.');
                }
            }
            
            // No transaction found - redirect to cancelled
            return redirect()->route('payment.cancelled')
                ->with('error', 'Transaction not found.');
                
        } catch (Exception $e) {
            Log::error('CoinGate callback error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return redirect()->route('payment.cancelled')
                ->with('error', 'An error occurred processing your payment.');
        }
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
     * Sync patient payment back to admin billing system
     */
    private function syncToAdminBilling($invoice, $totalPaid)
    {
        try {
            // Check if this invoice is linked to a billing record
            if (!$invoice->billing_id) {
                return; // No billing record to sync to
            }

            $billing = Billing::find($invoice->billing_id);
            if (!$billing) {
                Log::warning('Billing record not found for invoice', [
                    'invoice_id' => $invoice->id,
                    'billing_id' => $invoice->billing_id
                ]);
                return;
            }

            // Update billing record with payment information
            // Note: We update paid_amount and let the model's saving event automatically update the status
            $billing->update([
                'paid_amount' => $totalPaid,
                'payment_method' => $billing->payment_method ?: 'online', // Keep existing or set to 'online'
                'payment_reference' => $billing->payment_reference ?: 'ONLINE_PAYMENT',
                'paid_at' => $totalPaid >= $invoice->total_amount ? now() : $billing->paid_at,
                'updated_by' => null, // System update, not by specific user
            ]);
            
            // The model's saving event will automatically set status to:
            // - 'paid' if paid_amount >= total_amount
            // - 'partially_paid' if paid_amount > 0 but < total_amount
            // - 'pending' or 'overdue' if paid_amount = 0

            Log::info('Successfully synced patient payment to admin billing', [
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
                } catch (Exception $e) {
                    Log::error('Failed to send billing sync notification', [
                        'billing_id' => $billing->id,
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync patient payment to admin billing', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
