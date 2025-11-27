<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class PublicBillingController extends Controller
{
    /**
     * Show invoice and payment form using payment token (no authentication required)
     */
    public function showInvoice(string $token): View|RedirectResponse
    {
        $invoice = Invoice::where('payment_token', $token)->first();

        if (!$invoice) {
            return redirect()->route('public.billing.invalid')
                ->with('error', 'Invalid or expired payment link.');
        }

        // Check if token is expired
        if ($invoice->payment_token_expires_at && $invoice->payment_token_expires_at->isPast()) {
            return redirect()->route('public.billing.invalid')
                ->with('error', 'This payment link has expired. Please contact the hospital for a new link.');
        }

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return view('public.billing.paid', compact('invoice'));
        }

        $invoice->load(['patient', 'appointment', 'billing.doctor']);

        // Get available payment gateways
        $gateways = PaymentGateway::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('public.billing.show', compact('invoice', 'gateways', 'token'));
    }

    /**
     * Show payment form with selected gateway
     */
    public function showPaymentForm(Request $request, string $token): View|RedirectResponse
    {
        $invoice = Invoice::where('payment_token', $token)->first();

        if (!$invoice || !$invoice->isPaymentTokenValid($token)) {
            return redirect()->route('public.billing.invalid')
                ->with('error', 'Invalid or expired payment link.');
        }

        if ($invoice->status === 'paid') {
            return redirect()->route('public.billing.pay', ['token' => $token])
                ->with('info', 'This invoice has already been paid.');
        }

        $request->validate([
            'payment_gateway' => 'required|string|exists:payment_gateways,provider'
        ]);

        $selectedGateway = PaymentGateway::where('provider', $request->payment_gateway)
            ->where('is_active', true)
            ->firstOrFail();

        $invoice->load(['invoiceItems', 'patient']);

        return view('public.billing.payment-form', compact('invoice', 'selectedGateway', 'token'));
    }

    /**
     * Process payment for invoice
     */
    public function processPayment(Request $request, string $token)
    {
        $invoice = Invoice::where('payment_token', $token)->first();

        if (!$invoice || !$invoice->isPaymentTokenValid($token)) {
            return back()->withErrors(['error' => 'Invalid or expired payment link.']);
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
        $stripeGateway = new \App\Services\PaymentGateway\StripeGateway();
        $credentials = array_merge($gateway->credentials ?? [], [
            'test_mode' => $gateway->test_mode
        ]);
        $stripeGateway->initialize($credentials);

        $response = $stripeGateway->createCheckoutSession([
            'amount' => $payment->amount,
            'currency' => 'usd',
            'order_id' => $payment->transaction_id,
            'customer_email' => $invoice->patient->email,
            'description' => 'Invoice #' . $invoice->invoice_number,
            'success_url' => route('public.billing.success', ['token' => $token]),
            'cancel_url' => route('public.billing.pay', ['token' => $token]) . '?payment_cancelled=1',
            'is_public_payment' => true,
        ]);

        if (!$response['success']) {
            return back()->withErrors(['error' => 'Failed to create Stripe checkout session: ' . $response['error']]);
        }

        $payment->update([
            'gateway_transaction_id' => $response['payment_id'],
            'status' => 'pending',
            'gateway_response' => $response['gateway_response'] ?? [],
        ]);

        return redirect()->to($response['payment_url']);
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
    public function paymentSuccess(string $token): View|RedirectResponse
    {
        $invoice = Invoice::where('payment_token', $token)->first();

        if (!$invoice) {
            return redirect()->route('public.billing.invalid')
                ->with('error', 'Invalid payment link.');
        }

        $invoice->load(['patient', 'payments' => function($query) {
            $query->where('status', 'completed')->latest();
        }]);

        return view('public.billing.success', compact('invoice', 'token'));
    }

    /**
     * Show invalid/expired link page
     */
    public function invalid(): View
    {
        return view('public.billing.invalid');
    }

    /**
     * Generate transaction ID
     */
    private function generateTransactionId(): string
    {
        return 'TXN' . date('Ymd') . time() . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
}
