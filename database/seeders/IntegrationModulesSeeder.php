<?php

namespace Database\Seeders;

use App\Models\IntegrationModule;
use Illuminate\Database\Seeder;

class IntegrationModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Randox Lab Tests',
                'slug' => 'randox',
                'provider' => 'Randox',
                'type' => IntegrationModule::TYPE_LAB_TESTS,
                'description' => 'Order blood tests and receive results from Randox Health laboratories. Comprehensive pathology testing with fast turnaround times.',
                'website' => 'https://www.randox.com',
                'is_active' => false,
                'is_configured' => false,
                'capabilities' => [
                    'order_tests',
                    'track_orders',
                    'receive_results',
                    'view_reports',
                    'webhook_notifications',
                ],
                'settings' => [
                    'auto_notify_patient' => true,
                    'auto_notify_doctor' => true,
                    'results_require_review' => true,
                ],
            ],
            [
                'name' => 'Quincy Prescriptions',
                'slug' => 'quincy',
                'provider' => 'Quincy',
                'type' => IntegrationModule::TYPE_PRESCRIPTIONS,
                'description' => 'Send electronic prescriptions via Quincy. Supports private prescriptions with delivery options including patient delivery, clinic delivery, or token issuance.',
                'website' => 'https://quincy.health',
                'is_active' => false,
                'is_configured' => false,
                'capabilities' => [
                    'send_prescriptions',
                    'track_status',
                    'search_medicines',
                    'manage_patients',
                    'delivery_options',
                ],
                'settings' => [
                    'default_delivery_option' => 'deliver_customer',
                    'default_payee' => 'patient',
                    'auto_notify_patient' => true,
                ],
            ],
            [
                'name' => 'Vista Health Imaging',
                'slug' => 'vista_health',
                'provider' => 'VistaHealth',
                'type' => IntegrationModule::TYPE_IMAGING,
                'description' => 'Refer patients for MRI, CT, X-Ray, Ultrasound and other imaging services through Vista Health. Book appointments and receive reports electronically.',
                'website' => 'https://www.vista-health.co.uk',
                'is_active' => false,
                'is_configured' => false,
                'capabilities' => [
                    'submit_referrals',
                    'book_appointments',
                    'track_referrals',
                    'receive_reports',
                    'view_images',
                    'webhook_notifications',
                ],
                'settings' => [
                    'auto_notify_patient' => true,
                    'auto_notify_doctor' => true,
                    'reports_require_review' => true,
                    'allow_patient_booking' => false,
                ],
            ],
        ];

        foreach ($modules as $moduleData) {
            IntegrationModule::updateOrCreate(
                ['slug' => $moduleData['slug']],
                $moduleData
            );
        }

        $this->command->info('Integration modules seeded successfully.');
    }
}
