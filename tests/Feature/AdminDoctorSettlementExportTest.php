<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\Billing;
use App\Models\Doctor;
use App\Models\DoctorSettlement;
use App\Models\DoctorSettlementLine;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDoctorSettlementExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_export_settlement_as_csv(): void
    {
        [$settlement, $admin] = $this->seedSettlementFixture();

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.doctor-settlements.export-csv', $settlement));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Doctor settlement request export', $content);
        $this->assertStringContainsString('Settlement ID,'.$settlement->id, $content);
        $this->assertStringContainsString('Export Doctor', $content);
        $this->assertStringContainsString('Settle Patient', $content);
        $this->assertStringContainsString('Date,Amount,Method,Source', $content);
        $this->assertStringContainsString('Card', $content);
        $this->assertStringContainsString('Billing', $content);
        $this->assertStringContainsString('150.00', $content);
        $settlement->load('lines');
        $this->assertStringContainsString($settlement->lines->first()->description, $content);
    }

    /** @test */
    public function admin_can_export_settlement_as_pdf(): void
    {
        [$settlement, $admin] = $this->seedSettlementFixture();

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.doctor-settlements.export-pdf', $settlement));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->streamedContent());
    }

    /** @test */
    public function admin_settlement_show_page_includes_export_links(): void
    {
        [$settlement, $admin] = $this->seedSettlementFixture();

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($admin, 'admin');

        $this->get(route('admin.doctor-settlements.show', $settlement))
            ->assertOk()
            ->assertSee('Invoice', false)
            ->assertSee('Settle Patient', false)
            ->assertSee(route('admin.doctor-settlements.export-csv', $settlement), false)
            ->assertSee(route('admin.doctor-settlements.export-pdf', $settlement), false);
    }

    /**
     * @return array{0: DoctorSettlement, 1: User}
     */
    private function seedSettlementFixture(): array
    {
        $doctorUser = User::create([
            'name' => 'Export Doctor',
            'email' => 'export-doctor-'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'title' => 'Dr.',
            'first_name' => 'Export',
            'last_name' => 'Doctor',
            'slug' => 'export-doctor-'.uniqid(),
            'specialization' => 'General',
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 3,
            'email' => $doctorUser->email,
        ]);

        $patient = Patient::create([
            'patient_id' => 'P-SETTLE-'.uniqid(),
            'first_name' => 'Settle',
            'last_name' => 'Patient',
            'email' => 'settle-patient-'.uniqid().'@example.com',
            'phone' => '07123456789',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $billNumber = 'B-'.uniqid();
        $invoiceNumber = 'INV'.uniqid();
        $paymentDate = now();

        $billing = Billing::create([
            'bill_number' => $billNumber,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'billing_date' => $paymentDate->toDateString(),
            'description' => 'Consultation fee',
            'subtotal' => 150.00,
            'total_amount' => 150.00,
            'paid_amount' => 150.00,
            'status' => 'paid',
            'created_by' => $doctorUser->id,
        ]);

        $invoice = Invoice::create([
            'billing_id' => $billing->id,
            'patient_id' => $patient->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $paymentDate->toDateString(),
            'subtotal' => 150.00,
            'total_amount' => 150.00,
            'status' => 'paid',
            'description' => 'Payment via public link | Consultation fee',
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'payment_date' => $paymentDate,
            'amount' => 150.00,
            'payment_method' => 'card',
            'status' => 'completed',
        ]);

        $settlement = DoctorSettlement::create([
            'doctor_id' => $doctor->id,
            'period_type' => DoctorSettlement::PERIOD_MONTHLY,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'status' => DoctorSettlement::STATUS_SUBMITTED,
            'total_amount' => 150.00,
            'submitted_at' => now(),
        ]);

        DoctorSettlementLine::create([
            'doctor_settlement_id' => $settlement->id,
            'billing_id' => $billing->id,
            'description' => 'Bill '.$billNumber.' — Billing — '.$paymentDate->format('Y-m-d'),
            'amount' => 150.00,
        ]);

        $admin = User::create([
            'name' => 'Settlement Export Admin',
            'email' => 'settlement-export-admin-'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);

        return [$settlement, $admin];
    }
}
