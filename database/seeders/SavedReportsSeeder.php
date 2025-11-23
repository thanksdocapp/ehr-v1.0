<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SavedReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin user ID, or create sample user ID
        $adminId = DB::table('users')->where('role', 'admin')->orWhere('role', 'administrator')->first()?->id ?? 1;
        
        $sampleReports = [
            [
                'name' => 'Monthly Patient Registrations',
                'description' => 'Track new patient registrations by month',
                'report_type' => 'patient',
                'query_config' => json_encode([
                    'table' => 'patients',
                    'date_field' => 'created_at',
                    'group_by' => 'month',
                    'metrics' => ['count']
                ]),
                'filters' => json_encode([
                    'date_range' => 'last_12_months'
                ]),
                'created_by' => $adminId,
                'is_public' => true,
                'view_count' => rand(5, 50),
                'last_viewed_at' => Carbon::now()->subDays(rand(1, 7)),
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(15)
            ],
            [
                'name' => 'Revenue by Department',
                'description' => 'Financial performance analysis by hospital department',
                'report_type' => 'financial',
                'query_config' => json_encode([
                    'table' => 'billing',
                    'join' => 'departments',
                    'group_by' => 'department',
                    'metrics' => ['sum', 'avg', 'count']
                ]),
                'filters' => json_encode([
                    'date_range' => 'last_6_months',
                    'status' => 'paid'
                ]),
                'created_by' => $adminId,
                'is_public' => true,
                'view_count' => rand(10, 75),
                'last_viewed_at' => Carbon::now()->subDays(rand(1, 3)),
                'created_at' => Carbon::now()->subDays(45),
                'updated_at' => Carbon::now()->subDays(10)
            ],
            [
                'name' => 'Doctor Performance Analysis',
                'description' => 'Track doctor productivity and patient satisfaction',
                'report_type' => 'doctor',
                'query_config' => json_encode([
                    'table' => 'appointments',
                    'join' => ['doctors', 'patients'],
                    'group_by' => 'doctor_id',
                    'metrics' => ['count', 'avg_duration', 'satisfaction_score']
                ]),
                'filters' => json_encode([
                    'date_range' => 'last_3_months',
                    'status' => 'completed'
                ]),
                'created_by' => $adminId,
                'is_public' => false,
                'view_count' => rand(15, 30),
                'last_viewed_at' => Carbon::now()->subHours(rand(2, 48)),
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(5)
            ],
            [
                'name' => 'Appointment Trends',
                'description' => 'Daily and weekly appointment booking patterns',
                'report_type' => 'custom',
                'query_config' => json_encode([
                    'table' => 'appointments',
                    'date_field' => 'appointment_date',
                    'group_by' => ['day_of_week', 'hour'],
                    'metrics' => ['count', 'avg_wait_time']
                ]),
                'filters' => json_encode([
                    'date_range' => 'last_2_months'
                ]),
                'created_by' => $adminId,
                'is_public' => true,
                'view_count' => rand(20, 60),
                'last_viewed_at' => Carbon::now()->subMinutes(rand(30, 120)),
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(2)
            ],
            [
                'name' => 'Outstanding Payments Report',
                'description' => 'Track unpaid bills and payment collection metrics',
                'report_type' => 'financial',
                'query_config' => json_encode([
                    'table' => 'billing',
                    'join' => 'patients',
                    'where' => ['status' => ['pending', 'overdue']],
                    'metrics' => ['sum', 'count', 'aging_analysis']
                ]),
                'filters' => json_encode([
                    'status' => ['pending', 'overdue'],
                    'amount_range' => ['min' => 0, 'max' => null]
                ]),
                'created_by' => $adminId,
                'is_public' => false,
                'view_count' => rand(25, 80),
                'last_viewed_at' => Carbon::now()->subHours(rand(1, 24)),
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(1)
            ]
        ];
        
        foreach ($sampleReports as $report) {
            DB::table('saved_reports')->insert($report);
        }
        
        // Add some sample export records
        $reportIds = DB::table('saved_reports')->pluck('id')->toArray();
        
        $sampleExports = [
            [
                'filename' => 'monthly_patient_registrations_2025_08.pdf',
                'file_path' => 'reports/exports/monthly_patient_registrations_2025_08.pdf',
                'export_type' => 'pdf',
                'report_type' => 'patient',
                'report_id' => $reportIds[0] ?? null,
                'exported_by' => $adminId,
                'file_size' => rand(150000, 500000),
                'status' => 'completed',
                'completed_at' => Carbon::now()->subDays(rand(1, 7)),
                'created_at' => Carbon::now()->subDays(rand(1, 7)),
                'updated_at' => Carbon::now()->subDays(rand(1, 7))
            ],
            [
                'filename' => 'revenue_by_department_2025_Q3.xlsx',
                'file_path' => 'reports/exports/revenue_by_department_2025_Q3.xlsx',
                'export_type' => 'excel',
                'report_type' => 'financial',
                'report_id' => $reportIds[1] ?? null,
                'exported_by' => $adminId,
                'file_size' => rand(80000, 250000),
                'status' => 'completed',
                'completed_at' => Carbon::now()->subDays(rand(1, 5)),
                'created_at' => Carbon::now()->subDays(rand(1, 5)),
                'updated_at' => Carbon::now()->subDays(rand(1, 5))
            ]
        ];
        
        foreach ($sampleExports as $export) {
            DB::table('report_exports')->insert($export);
        }
    }
}
