<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicBillingController;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PendingBooking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZeroBalanceBookingCheckoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function zero_balance_invoice_completes_checkout_and_redirects_to_booking_success(): void
    {
        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Zero',
            'last_name' => 'Balance',
            'slug' => 'zero-balance-' . uniqid(),
            'specialization' => 'General',
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
        ]);

        $patient = Patient::create([
            'first_name' => 'Pat',
            'last_name' => 'Zero',
            'email' => 'zero-balance-' . uniqid() . '@example.com',
            'phone' => '07000000000',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'subtotal' => 50,
            'tax_amount' => 0,
            'discount_amount' => 50,
            'total_amount' => 0,
            'status' => 'pending',
            'description' => 'Consultation',
        ]);
        $invoice->generatePaymentToken();
        $invoice->refresh();

        $pending = PendingBooking::create([
            'booking_token' => PendingBooking::generateBookingToken(),
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addWeek()->toDateString(),
            'appointment_time' => '10:00',
            'is_online' => false,
            'patient_data' => [
                'first_name' => 'Pat',
                'last_name' => 'Zero',
                'email' => $patient->email,
                'phone' => '07000000000',
                'consultation_type' => 'in_person',
            ],
            'fee' => 0,
            'status' => 'pending_payment',
            'expires_at' => now()->addHours(24),
            'invoice_id' => $invoice->id,
        ]);

        session(['pending_booking_token' => $pending->booking_token]);

        $response = app(PublicBillingController::class)->tryCompleteZeroBalanceCheckout($invoice);

        $this->assertNotNull($response);
        $this->assertTrue($response->isRedirect());
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
        ]);
    }
}
