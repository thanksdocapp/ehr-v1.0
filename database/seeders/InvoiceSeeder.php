<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Appointment;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only seed in local environment
        if (!app()->environment('local')) {
            return;
        }

        $patients = Patient::all();
        $appointments = Appointment::all();

        if ($patients->isEmpty()) {
            $this->command->warn('No patients found. Please seed patients first.');
            return;
        }

        $serviceItems = [
            ['type' => 'consultation', 'name' => 'General Consultation', 'price' => 150.00],
            ['type' => 'consultation', 'name' => 'Specialist Consultation', 'price' => 250.00],
            ['type' => 'procedure', 'name' => 'Blood Test - CBC', 'price' => 45.00],
            ['type' => 'procedure', 'name' => 'X-Ray Chest', 'price' => 120.00],
            ['type' => 'procedure', 'name' => 'ECG', 'price' => 80.00],
            ['type' => 'procedure', 'name' => 'Ultrasound', 'price' => 200.00],
            ['type' => 'medication', 'name' => 'Prescription Medications', 'price' => 75.00],
            ['type' => 'procedure', 'name' => 'Urinalysis', 'price' => 35.00],
            ['type' => 'procedure', 'name' => 'Lipid Panel', 'price' => 85.00],
            ['type' => 'procedure', 'name' => 'CT Scan', 'price' => 450.00],
            ['type' => 'procedure', 'name' => 'MRI Scan', 'price' => 800.00],
            ['type' => 'procedure', 'name' => 'Physical Therapy Session', 'price' => 95.00]
        ];

        $statuses = ['pending', 'paid', 'overdue', 'cancelled'];
        $descriptions = [
            'Medical services rendered during consultation',
            'Diagnostic procedures and tests',
            'Treatment and medication costs',
            'Emergency care services',
            'Routine health checkup'
        ];

        // Create invoices for patients
        foreach ($patients as $patient) {
            $invoiceCount = rand(1, 3); // 1-3 invoices per patient
            
            for ($i = 0; $i < $invoiceCount; $i++) {
                $appointment = $appointments->where('patient_id', $patient->id)->first();
                
                $invoiceDate = Carbon::now()->subDays(rand(1, 90));
                $dueDate = $invoiceDate->copy()->addDays(30);
                
                // Generate invoice number
                $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                
                $status = $statuses[array_rand($statuses)];
                
                // Adjust dates and paid_date based on status
                $paidDate = null;
                if ($status === 'paid') {
                    $paidDate = $invoiceDate->copy()->addDays(rand(1, 25));
                } elseif ($status === 'overdue') {
                    $dueDate = Carbon::now()->subDays(rand(1, 30));
                }
                
                // Calculate totals
                $subtotal = 0;
                $itemCount = rand(1, 4); // 1-4 items per invoice
                $invoiceItems = [];
                
                for ($j = 0; $j < $itemCount; $j++) {
                    $service = $serviceItems[array_rand($serviceItems)];
                    $quantity = rand(1, 2);
                    $unitPrice = $service['price'];
                    $totalPrice = $quantity * $unitPrice;
                    $subtotal += $totalPrice;
                    
                    $invoiceItems[] = [
                        'item_type' => $service['type'],
                        'item_name' => $service['name'],
                        'description' => 'Professional medical service',
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice
                    ];
                }
                
                $taxRate = 0.08; // 8% tax
                $discountRate = rand(0, 1) ? 0 : 0.1; // 10% discount for some invoices
                
                $discountAmount = $subtotal * $discountRate;
                $taxableAmount = $subtotal - $discountAmount;
                $taxAmount = $taxableAmount * $taxRate;
                $totalAmount = $taxableAmount + $taxAmount;
                
                // Create invoice
                $invoice = Invoice::create([
                    'patient_id' => $patient->id,
                    'appointment_id' => $appointment?->id,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'status' => $status,
                    'description' => $descriptions[array_rand($descriptions)],
                    'notes' => rand(0, 1) ? 'Thank you for choosing our medical services.' : null,
                    'paid_date' => $paidDate
                ]);
                
                // Create invoice items
                foreach ($invoiceItems as $item) {
                    InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
                }
            }
        }

        $this->command->info('Invoices and invoice items seeded successfully!');
    }
}
