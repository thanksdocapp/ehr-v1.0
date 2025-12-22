<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\EmailLog;

class EncryptExistingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:encrypt-existing
                            {--model= : Specific model to encrypt (patient, medical-record, appointment, prescription, email-log, all)}
                            {--batch=100 : Number of records to process per batch}
                            {--force : Force encryption without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt existing unencrypted data in the database (HIPAA/GDPR compliance)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = $this->option('model') ?? 'all';
        $batchSize = (int) $this->option('batch');
        $force = $this->option('force');

        $this->info('=== Data Encryption Tool ===');
        $this->info('This will encrypt existing plaintext PHI/PII data for HIPAA/GDPR compliance.');
        $this->newLine();

        if (!$force) {
            $this->warn('⚠️  WARNING: This operation will modify database records.');
            $this->warn('⚠️  Ensure you have a RECENT backup before proceeding!');
            $this->warn('⚠️  This operation is IRREVERSIBLE without a backup.');
            $this->newLine();

            if (!$this->confirm('Do you have a recent database backup and want to proceed?')) {
                $this->error('Operation cancelled. Please create a backup first.');
                return 1;
            }
        }

        $this->newLine();
        $this->info('Starting encryption process...');
        $this->newLine();

        try {
            DB::beginTransaction();

            $models = $model === 'all' 
                ? ['patient', 'medical-record', 'appointment', 'prescription', 'email-log']
                : [$model];

            foreach ($models as $modelType) {
                match ($modelType) {
                    'patient' => $this->encryptPatients($batchSize),
                    'medical-record' => $this->encryptMedicalRecords($batchSize),
                    'appointment' => $this->encryptAppointments($batchSize),
                    'prescription' => $this->encryptPrescriptions($batchSize),
                    'email-log' => $this->encryptEmailLogs($batchSize),
                    default => $this->error("Unknown model: {$modelType}")
                };
            }

            if ($this->confirm('Commit all changes to database?', true)) {
                DB::commit();
                $this->info('✅ All changes committed successfully!');
                return 0;
            } else {
                DB::rollBack();
                $this->warn('⚠️  Changes rolled back.');
                return 1;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Encryption failed: ' . $e->getMessage());
            $this->error('All changes have been rolled back.');
            return 1;
        }
    }

    private function encryptPatients($batchSize)
    {
        $this->info('Encrypting Patient records...');
        
        $encryptedFields = ['insurance_number', 'emergency_contact', 'emergency_phone', 'notes'];
        $total = 0;

        DB::table('patients')->orderBy('id')->chunk($batchSize, function ($patients) use (&$total, $encryptedFields) {
            foreach ($patients as $patient) {
                $updates = [];
                $needsUpdate = false;

                foreach ($encryptedFields as $field) {
                    $value = $patient->$field;
                    
                    // Skip if null or already encrypted
                    if (is_null($value) || $this->isAlreadyEncrypted($value)) {
                        continue;
                    }

                    $updates[$field] = Crypt::encryptString($value);
                    $needsUpdate = true;
                }

                if ($needsUpdate) {
                    DB::table('patients')->where('id', $patient->id)->update($updates);
                    $total++;
                }
            }
        });

        $this->info("✅ Encrypted {$total} patient records");
        return $total;
    }

    private function encryptMedicalRecords($batchSize)
    {
        $this->info('Encrypting Medical Record data...');
        
        $encryptedFields = [
            'presenting_complaint', 'history_of_presenting_complaint', 'past_medical_history',
            'drug_history', 'allergies', 'social_history', 'family_history',
            'ideas_concerns_expectations', 'plan', 'diagnosis', 'symptoms', 'treatment', 'notes'
        ];
        
        $total = 0;

        DB::table('medical_records')->orderBy('id')->chunk($batchSize, function ($records) use (&$total, $encryptedFields) {
            foreach ($records as $record) {
                $updates = [];
                $needsUpdate = false;

                foreach ($encryptedFields as $field) {
                    $value = $record->$field;
                    
                    if (is_null($value) || $this->isAlreadyEncrypted($value)) {
                        continue;
                    }

                    $updates[$field] = Crypt::encryptString($value);
                    $needsUpdate = true;
                }

                if ($needsUpdate) {
                    DB::table('medical_records')->where('id', $record->id)->update($updates);
                    $total++;
                }
            }
        });

        $this->info("✅ Encrypted {$total} medical records");
        return $total;
    }

    private function encryptAppointments($batchSize)
    {
        $this->info('Encrypting Appointment data...');
        
        $encryptedFields = ['reason', 'symptoms', 'notes', 'diagnosis', 'prescription', 'follow_up_instructions'];
        $total = 0;

        DB::table('appointments')->orderBy('id')->chunk($batchSize, function ($appointments) use (&$total, $encryptedFields) {
            foreach ($appointments as $appointment) {
                $updates = [];
                $needsUpdate = false;

                foreach ($encryptedFields as $field) {
                    $value = $appointment->$field;
                    
                    if (is_null($value) || $this->isAlreadyEncrypted($value)) {
                        continue;
                    }

                    $updates[$field] = Crypt::encryptString($value);
                    $needsUpdate = true;
                }

                if ($needsUpdate) {
                    DB::table('appointments')->where('id', $appointment->id)->update($updates);
                    $total++;
                }
            }
        });

        $this->info("✅ Encrypted {$total} appointment records");
        return $total;
    }

    private function encryptPrescriptions($batchSize)
    {
        $this->info('Encrypting Prescription data...');
        
        $encryptedFields = ['diagnosis', 'notes', 'pharmacist_notes'];
        $total = 0;

        DB::table('prescriptions')->orderBy('id')->chunk($batchSize, function ($prescriptions) use (&$total, $encryptedFields) {
            foreach ($prescriptions as $prescription) {
                $updates = [];
                $needsUpdate = false;

                foreach ($encryptedFields as $field) {
                    $value = $prescription->$field;
                    
                    if (is_null($value) || $this->isAlreadyEncrypted($value)) {
                        continue;
                    }

                    $updates[$field] = Crypt::encryptString($value);
                    $needsUpdate = true;
                }

                if ($needsUpdate) {
                    DB::table('prescriptions')->where('id', $prescription->id)->update($updates);
                    $total++;
                }
            }
        });

        $this->info("✅ Encrypted {$total} prescription records");
        return $total;
    }

    private function encryptEmailLogs($batchSize)
    {
        $this->info('Encrypting Email Log bodies (may contain PHI)...');
        
        $total = 0;

        DB::table('email_logs')->orderBy('id')->chunk($batchSize, function ($emails) use (&$total) {
            foreach ($emails as $email) {
                // Only encrypt if contains patient data
                if (is_null($email->body) || $this->isAlreadyEncrypted($email->body)) {
                    continue;
                }

                // Encrypt body
                $updates = [
                    'body' => Crypt::encryptString($email->body),
                ];

                DB::table('email_logs')->where('id', $email->id)->update($updates);
                $total++;
            }
        });

        $this->info("✅ Encrypted {$total} email log records");
        return $total;
    }

    /**
     * Check if a value is already encrypted
     */
    private function isAlreadyEncrypted($value): bool
    {
        if (empty($value) || strlen($value) < 10) {
            return false;
        }

        try {
            // Try to decrypt - if it succeeds, it's already encrypted
            Crypt::decryptString($value);
            return true;
        } catch (\Exception $e) {
            // Can't decrypt = not encrypted (or corrupted, but we'll re-encrypt anyway)
            return false;
        }
    }
}

