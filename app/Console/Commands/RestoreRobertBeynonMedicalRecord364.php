<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordAttachment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RestoreRobertBeynonMedicalRecord364 extends Command
{
    protected $signature = 'medical-records:restore-beynon-364
                            {--pdf-path= : Absolute path to Robert_Beynon-Photo-01-09-2026 PDF on this server}
                            {--dry-run : Validate only; do not write database or storage}
                            {--force : Skip interactive confirmation}';

    protected $description = 'Restore deleted medical record #364 for Robert Beynon (patient #452) with original doctor, user, and attachment IDs';

    private const MEDICAL_RECORD_ID = 364;

    private const ATTACHMENT_ID = 189;

    private const PATIENT_ID = 452;

    private const DOCTOR_ID = 17;

    private const APPOINTMENT_ID = 436;

    private const USER_ID = 25;

    private const ATTACHMENT_STORAGE_PATH = 'medical-records/364/Robert_Beynon-Photo-01-09-2026-1788289290_mxnhxa.pdf';

    private const ATTACHMENT_FILE_NAME = 'Robert_Beynon-Photo-01-09-2026.pdf';

    public const STAGING_PDF_RELATIVE_PATH = 'restore-incoming/Robert_Beynon-Photo-01-09-2026-1788289290_mxnhxa.pdf';

    public function handle(): int
    {
        $pdfPathOption = (string) ($this->option('pdf-path') ?? '');
        $dryRun = (bool) $this->option('dry-run');

        if (!$this->validateReferences()) {
            return self::FAILURE;
        }

        if (MedicalRecord::query()->whereKey(self::MEDICAL_RECORD_ID)->exists()) {
            $this->error('Medical record #'.self::MEDICAL_RECORD_ID.' already exists. Aborting.');

            return self::FAILURE;
        }

        if (MedicalRecordAttachment::query()->whereKey(self::ATTACHMENT_ID)->exists()) {
            $this->error('Medical record attachment #'.self::ATTACHMENT_ID.' already exists. Aborting.');

            return self::FAILURE;
        }

        $pdfPath = $this->resolvePdfPath($pdfPathOption);
        if ($pdfPath === null) {
            $this->error('PDF not found. Provide --pdf-path= or upload to private/'.self::STAGING_PDF_RELATIVE_PATH);
            $this->line('Tried: '.$pdfPathOption);
            $this->line('Also tried: '.storage_path('app/private/'.self::STAGING_PDF_RELATIVE_PATH));

            return self::FAILURE;
        }

        $pdfSize = filesize($pdfPath) ?: 0;
        if ($pdfSize !== 366554) {
            $this->warn("Expected PDF size 366554 bytes; got {$pdfSize}. Continuing anyway.");
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['medical_record_id', (string) self::MEDICAL_RECORD_ID],
                ['attachment_id', (string) self::ATTACHMENT_ID],
                ['patient_id', (string) self::PATIENT_ID],
                ['doctor_id', (string) self::DOCTOR_ID],
                ['appointment_id', (string) self::APPOINTMENT_ID],
                ['created_by / uploaded_by (user)', (string) self::USER_ID],
                ['record_date', '2026-09-01'],
                ['pdf_path', $pdfPath],
            ]
        );

        if ($dryRun) {
            $this->info('Dry run complete. No changes made.');

            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Restore medical record #364 and attachment #189 now?')) {
            $this->warn('Aborted.');

            return self::SUCCESS;
        }

        try {
            DB::transaction(function () use ($pdfPath): void {
                MedicalRecord::withoutEvents(function () use ($pdfPath): void {
                    $record = new MedicalRecord([
                        'patient_id' => self::PATIENT_ID,
                        'doctor_id' => self::DOCTOR_ID,
                        'appointment_id' => self::APPOINTMENT_ID,
                        'record_type' => 'consultation',
                        'record_date' => '2026-09-01',
                        'drug_history' => "Dapagliflozin 10mg od\r\nLansoprazole 15mg od bd\r\nMetformin 1g bd\r\nClopidogrel 75mg od\r\nAtorvastatin 40mg od \r\nLevetiracetam 500mg bd (no medical record provided re- clinical indication)",
                        'plan' => "provide medical record\r\nadv follow-up wiht NHS GP to explore further investigation on memory loss +/- vascular dementia in view of multiple CVA, poor cognition and low AMT score",
                        'notes' => "AMT 2/10\r\nchest clr, hs norm\r\nRockwood Clinical Frailty scale 6-7",
                        'is_private' => false,
                        'created_by' => self::USER_ID,
                        'updated_by' => self::USER_ID,
                    ]);
                    $record->id = self::MEDICAL_RECORD_ID;
                    $record->created_at = Carbon::parse('2026-09-01 19:54:24');
                    $record->updated_at = Carbon::parse('2026-09-01 20:01:30');
                    $record->save();

                    $directory = dirname(self::ATTACHMENT_STORAGE_PATH);
                    Storage::disk('private')->makeDirectory($directory);

                    $stream = fopen($pdfPath, 'rb');
                    if ($stream === false) {
                        throw new \RuntimeException("Unable to read PDF: {$pdfPath}");
                    }

                    Storage::disk('private')->put(self::ATTACHMENT_STORAGE_PATH, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }

                    if (!Storage::disk('private')->exists(self::ATTACHMENT_STORAGE_PATH)) {
                        throw new \RuntimeException('PDF copy to private storage failed.');
                    }

                    $attachment = new MedicalRecordAttachment([
                        'medical_record_id' => self::MEDICAL_RECORD_ID,
                        'uploaded_by' => self::USER_ID,
                        'file_name' => self::ATTACHMENT_FILE_NAME,
                        'file_path' => self::ATTACHMENT_STORAGE_PATH,
                        'file_type' => 'application/pdf',
                        'file_extension' => 'pdf',
                        'file_size' => filesize($pdfPath) ?: 366554,
                        'storage_disk' => 'private',
                        'file_category' => 'photo',
                        'description' => null,
                        'is_private' => true,
                        'virus_scan_status' => 'clean',
                        'virus_scan_at' => Carbon::parse('2026-09-01 20:01:30'),
                    ]);
                    $attachment->id = self::ATTACHMENT_ID;
                    $attachment->created_at = Carbon::parse('2026-09-01 20:01:30');
                    $attachment->updated_at = Carbon::parse('2026-09-01 20:01:30');
                    $attachment->saveQuietly();

                    $nextMedicalRecordId = max((int) MedicalRecord::query()->max('id') + 1, self::MEDICAL_RECORD_ID + 1);
                    $nextAttachmentId = max((int) MedicalRecordAttachment::query()->max('id') + 1, self::ATTACHMENT_ID + 1);

                    DB::statement('ALTER TABLE medical_records AUTO_INCREMENT = '.(int) $nextMedicalRecordId);
                    DB::statement('ALTER TABLE medical_record_attachments AUTO_INCREMENT = '.(int) $nextAttachmentId);
                });
            });
        } catch (\Throwable $e) {
            $this->error('Restore failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Restore complete.');
        $this->line('Medical record: #'.self::MEDICAL_RECORD_ID);
        $this->line('Attachment: #'.self::ATTACHMENT_ID);
        $this->line('Storage: private/'.self::ATTACHMENT_STORAGE_PATH);

        return self::SUCCESS;
    }

    private function validateReferences(): bool
    {
        $patient = Patient::query()->find(self::PATIENT_ID);
        if (!$patient) {
            $this->error('Patient #'.self::PATIENT_ID.' not found.');

            return false;
        }

        $doctor = Doctor::query()->find(self::DOCTOR_ID);
        if (!$doctor) {
            $this->error('Doctor #'.self::DOCTOR_ID.' not found.');

            return false;
        }

        if ((int) $doctor->user_id !== self::USER_ID) {
            $this->error(
                'Doctor #'.self::DOCTOR_ID.' is linked to user #'.$doctor->user_id
                .', expected user #'.self::USER_ID.' (Loretta Shoderu).'
            );

            return false;
        }

        $user = User::query()->find(self::USER_ID);
        if (!$user) {
            $this->error('User #'.self::USER_ID.' not found.');

            return false;
        }

        if (strtolower((string) $user->role) !== 'doctor') {
            $this->error('User #'.self::USER_ID.' is not a doctor (role='.$user->role.').');

            return false;
        }

        $appointment = Appointment::query()->find(self::APPOINTMENT_ID);
        if (!$appointment) {
            $this->error('Appointment #'.self::APPOINTMENT_ID.' not found.');

            return false;
        }

        if ((int) $appointment->patient_id !== self::PATIENT_ID) {
            $this->error(
                'Appointment #'.self::APPOINTMENT_ID.' belongs to patient #'.$appointment->patient_id
                .', expected #'.self::PATIENT_ID.'.'
            );

            return false;
        }

        if ((int) $appointment->doctor_id !== self::DOCTOR_ID) {
            $this->error(
                'Appointment #'.self::APPOINTMENT_ID.' belongs to doctor #'.$appointment->doctor_id
                .', expected #'.self::DOCTOR_ID.'.'
            );

            return false;
        }

        $this->info(sprintf(
            'Validated patient %s %s, doctor #%d (%s), user #%d (%s), appointment #%d.',
            $patient->first_name,
            $patient->last_name,
            $doctor->id,
            trim(($doctor->title ?? '').' '.$doctor->first_name.' '.$doctor->last_name),
            $user->id,
            $user->name,
            $appointment->id
        ));

        return true;
    }

    private function resolvePdfPath(string $pdfPathOption): ?string
    {
        $candidates = [];

        if ($pdfPathOption !== '') {
            $candidates[] = $pdfPathOption;
            $candidates[] = base_path($pdfPathOption);
            $candidates[] = storage_path('app/'.$pdfPathOption);
            $candidates[] = storage_path('app/private/'.$pdfPathOption);
        }

        $candidates[] = storage_path('app/private/'.self::STAGING_PDF_RELATIVE_PATH);

        foreach (array_unique($candidates) as $candidate) {
            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
