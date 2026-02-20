<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Inserts the doctor_payment_received email template (notify doctor when their patient pays).
     */
    public function up(): void
    {
        $body = "Dear {{doctor_name}},\n\nA payment has been received from your patient:\n\n"
            . "Patient: {{patient_name}}\n"
            . "Amount: {{amount}}\n"
            . "Description: {{description}}\n"
            . "Bill reference: {{billing_id}}\n\n"
            . "View billing details: {{billing_url}}\n\n"
            . "Regards,\n{{hospital_name}}";

        $variables = [
            'doctor_name' => "Doctor's name",
            'patient_name' => 'Patient full name',
            'amount' => 'Payment amount with currency',
            'description' => 'Bill/invoice description',
            'billing_id' => 'Billing record ID',
            'billing_url' => 'URL to view the bill',
            'hospital_name' => 'Hospital/organisation name',
        ];

        DB::table('email_templates')->updateOrInsert(
            ['name' => 'doctor_payment_received'],
            [
                'subject' => 'Patient payment received – {{patient_name}}',
                'body' => $body,
                'description' => 'Sent to the doctor when their patient pays an invoice/bill',
                'category' => 'billing',
                'status' => 'active',
                'variables' => json_encode($variables),
                'sender_name' => null,
                'sender_email' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_templates')->where('name', 'doctor_payment_received')->delete();
    }
};
