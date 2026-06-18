<?php

namespace App\Services;

use App\Models\Department;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordAttachment;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use ZipArchive;

class ClinicDataExportService
{
    /**
     * @return array<string, mixed>
     */
    public function normalizeFilters(array $input): array
    {
        return [
            'department_id' => (int) ($input['department_id'] ?? 0),
            'reg_from' => $input['reg_from'] ?? null,
            'reg_to' => $input['reg_to'] ?? null,
            'status' => $input['status'] ?? null,
            'record_date_from' => $input['record_date_from'] ?? null,
            'record_date_to' => $input['record_date_to'] ?? null,
            'record_type' => $input['record_type'] ?? null,
            'doctor_id' => filled($input['doctor_id'] ?? null) ? (int) $input['doctor_id'] : null,
            'include_private' => filter_var($input['include_private'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'include_attachments' => filter_var($input['include_attachments'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    public function assertDepartmentAccess(User $user, int $departmentId): void
    {
        if ($departmentId <= 0) {
            throw new AccessDeniedHttpException('A clinic must be selected for export.');
        }

        if ($user->department_id && (int) $user->department_id !== $departmentId) {
            throw new AccessDeniedHttpException('You can only export data for your assigned clinic.');
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     patient_count: int,
     *     record_count: int,
     *     attachment_count: int,
     *     attachments_included: bool
     * }
     */
    public function counts(array $filters): array
    {
        return [
            'patient_count' => $this->patientsQuery($filters)->count(),
            'record_count' => $this->medicalRecordsQuery($filters)->count(),
            'attachment_count' => $this->attachmentsQuery($filters)->count(),
            'attachments_included' => (bool) $filters['include_attachments'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     path: string,
     *     filename: string,
     *     counts: array{
     *         patient_count: int,
     *         record_count: int,
     *         attachment_count: int,
     *         attachments_packaged: int,
     *         attachments_missing_files: int,
     *         attachments_included: bool
     *     }
     * }
     */
    public function buildZip(array $filters, User $user): array
    {
        $department = Department::findOrFail($filters['department_id']);
        $counts = $this->counts($filters);
        $attachmentStats = ['packaged' => 0, 'missing_files' => 0];

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $safeSlug = preg_replace('/[^a-z0-9_-]+/i', '-', $department->slug ?? 'clinic') ?: 'clinic';
        $zipFilename = "clinic-export_{$safeSlug}_{$timestamp}.zip";
        $zipPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('clinic_export_', true) . '.zip';

        $patientsCsvPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('patients_', true) . '.csv';
        $recordsCsvPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('records_', true) . '.csv';
        $attachmentsCsvPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('attachments_', true) . '.csv';
        $manifestPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('manifest_', true) . '.txt';
        $tempAttachmentPaths = [];

        try {
            $this->writePatientsCsv($patientsCsvPath, $filters);
            $this->writeMedicalRecordsCsv($recordsCsvPath, $filters);

            if ($filters['include_attachments']) {
                $attachmentStats = $this->writeAttachmentsCsv($attachmentsCsvPath, $filters);
            }

            $counts['attachments_packaged'] = $attachmentStats['packaged'];
            $counts['attachments_missing_files'] = $attachmentStats['missing_files'];

            file_put_contents(
                $manifestPath,
                $this->buildManifest($department, $user, $filters, $counts)
            );

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Unable to create export archive.');
            }

            $zip->addFile($manifestPath, 'export_manifest.txt');
            $zip->addFile($patientsCsvPath, 'patients.csv');
            $zip->addFile($recordsCsvPath, 'medical_records.csv');

            if ($filters['include_attachments']) {
                $zip->addFile($attachmentsCsvPath, 'attachments.csv');
                $tempAttachmentPaths = $this->addAttachmentsToZip($zip, $filters);
            }

            $zip->close();
        } finally {
            @unlink($patientsCsvPath);
            @unlink($recordsCsvPath);
            @unlink($attachmentsCsvPath);
            @unlink($manifestPath);

            foreach ($tempAttachmentPaths as $tempAttachmentPath) {
                @unlink($tempAttachmentPath);
            }
        }

        $this->logExport($user, $department, $filters, $counts);

        return [
            'path' => $zipPath,
            'filename' => $zipFilename,
            'counts' => $counts,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function patientsQuery(array $filters): Builder
    {
        $query = Patient::query()->byDepartment($filters['department_id']);

        if (filled($filters['reg_from'])) {
            $query->where('created_at', '>=', parseDateInput($filters['reg_from']) . ' 00:00:00');
        }
        if (filled($filters['reg_to'])) {
            $query->where('created_at', '<=', parseDateInput($filters['reg_to']) . ' 23:59:59');
        }

        if ($filters['status'] === 'active') {
            $query->where('is_active', true);
        } elseif (in_array($filters['status'], ['inactive', 'archived'], true)) {
            $query->where('is_active', false);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function medicalRecordsQuery(array $filters, bool $withRelations = true): Builder
    {
        $query = MedicalRecord::query()->byDepartment($filters['department_id']);

        if ($withRelations) {
            $query->with(['patient', 'doctor.user']);
        }

        if (! $filters['include_private']) {
            $query->where(function (Builder $q) {
                $q->where('is_private', false)->orWhereNull('is_private');
            });
        }

        if (filled($filters['record_type'])) {
            $query->where('record_type', $filters['record_type']);
        }

        if ($filters['doctor_id']) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (filled($filters['record_date_from'])) {
            $from = parseDateInput($filters['record_date_from']);
            $query->whereRaw('DATE(COALESCE(record_date, created_at)) >= ?', [$from]);
        }

        if (filled($filters['record_date_to'])) {
            $to = parseDateInput($filters['record_date_to']);
            $query->whereRaw('DATE(COALESCE(record_date, created_at)) <= ?', [$to]);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function attachmentsQuery(array $filters): Builder
    {
        if (! $filters['include_attachments']) {
            return MedicalRecordAttachment::query()->whereRaw('1 = 0');
        }

        $recordIds = $this->medicalRecordsQuery($filters, false)->select('medical_records.id');

        $query = MedicalRecordAttachment::query()
            ->whereIn('medical_record_id', $recordIds)
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

        if (! $filters['include_private']) {
            $query->where(function (Builder $q) {
                $q->where('is_private', false)->orWhereNull('is_private');
            });
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function writePatientsCsv(string $path, array $filters): void
    {
        $handle = fopen($path, 'w');
        if ($handle === false) {
            throw new \RuntimeException('Unable to write patients export file.');
        }

        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($handle, [
            'Patient ID',
            'First Name',
            'Last Name',
            'Full Name',
            'Email',
            'Phone',
            'Date of Birth',
            'Age',
            'Gender',
            'Blood Group',
            'Address',
            'City',
            'State',
            'Country',
            'Postal Code',
            'Emergency Contact Name',
            'Emergency Contact Phone',
            'Insurance Provider',
            'Insurance Number',
            'Allergies',
            'Medical Conditions',
            'Assigned Clinics',
            'Created By Doctor',
            'Assigned Doctor',
            'Last Appointment',
            'Booking Source',
            'Notes / Comment',
            'Invoice Count',
            'Total Invoiced',
            'Latest Invoice No',
            'Status',
            'Registration Date',
            'Last Updated',
        ]);

        $hasCreatedFrom = Schema::hasColumn('appointments', 'created_from');

        $this->patientsQuery($filters)
            ->with([
                'departments',
                'department',
                'createdByDoctor',
                'assignedDoctor.user',
                'appointments' => function ($q) use ($hasCreatedFrom) {
                    $columns = ['id', 'patient_id', 'appointment_date', 'status'];
                    if ($hasCreatedFrom) {
                        $columns[] = 'created_from';
                    }
                    $q->select($columns)->orderByDesc('appointment_date');
                },
                'invoices' => function ($q) {
                    $q->select('id', 'patient_id', 'invoice_number', 'total_amount', 'created_at')
                        ->orderByDesc('created_at');
                },
            ])
            ->orderBy('created_at', 'desc')
            ->chunk(200, function ($patients) use ($handle) {
                foreach ($patients as $patient) {
                    $departments = $patient->departments->pluck('name')->join(', ')
                        ?: ($patient->department ? $patient->department->name : '');
                    $allergies = is_array($patient->allergies)
                        ? implode(', ', $patient->allergies)
                        : ($patient->allergies ?? '');
                    $conditions = is_array($patient->medical_conditions)
                        ? implode(', ', $patient->medical_conditions)
                        : ($patient->medical_conditions ?? '');

                    $age = $patient->date_of_birth ? Carbon::parse($patient->date_of_birth)->age : '';

                    $latestAppointment = $patient->appointments->first();
                    $lastAppointment = $latestAppointment && $latestAppointment->appointment_date
                        ? $latestAppointment->appointment_date->format('Y-m-d')
                        : '';
                    $bookingSource = $latestAppointment ? ($latestAppointment->created_from ?? '') : '';

                    $invoiceCount = $patient->invoices->count();
                    $totalInvoiced = $invoiceCount
                        ? number_format((float) $patient->invoices->sum('total_amount'), 2, '.', '')
                        : '';
                    $latestInvoiceNo = $invoiceCount
                        ? ($patient->invoices->first()->invoice_number ?? '')
                        : '';

                    $assignedDoctor = $patient->assignedDoctor
                        ? ($patient->assignedDoctor->user->name ?? $patient->assignedDoctor->full_name ?? '')
                        : '';

                    fputcsv($handle, [
                        $patient->patient_id,
                        $patient->first_name,
                        $patient->last_name,
                        $patient->full_name,
                        $patient->email ?? '',
                        $patient->phone ?? '',
                        $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '',
                        $age,
                        ucfirst($patient->gender ?? ''),
                        $patient->blood_group ?? '',
                        $patient->address ?? '',
                        $patient->city ?? '',
                        $patient->state ?? '',
                        $patient->country ?? '',
                        $patient->postal_code ?? '',
                        $patient->emergency_contact ?? '',
                        $patient->emergency_phone ?? '',
                        $patient->insurance_provider ?? '',
                        $patient->insurance_number ?? '',
                        $allergies,
                        $conditions,
                        $departments,
                        $patient->createdByDoctor ? $patient->createdByDoctor->full_name : '',
                        $assignedDoctor,
                        $lastAppointment,
                        $bookingSource,
                        $patient->notes ?? '',
                        $invoiceCount,
                        $totalInvoiced,
                        $latestInvoiceNo,
                        $patient->is_active ? 'Active' : 'Inactive',
                        $patient->created_at->format('Y-m-d H:i:s'),
                        $patient->updated_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

        fclose($handle);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function writeMedicalRecordsCsv(string $path, array $filters): void
    {
        $handle = fopen($path, 'w');
        if ($handle === false) {
            throw new \RuntimeException('Unable to write medical records export file.');
        }

        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($handle, [
            'Record ID',
            'Patient ID',
            'Patient Email',
            'Doctor ID',
            'Doctor Email',
            'Appointment ID',
            'Record Type',
            'Record Date',
            'Follow Up Date',
            'Presenting Complaint',
            'History Of Presenting Complaint',
            'Past Medical History',
            'Drug History',
            'Allergies',
            'Social History',
            'Family History',
            'Ideas Concerns Expectations',
            'Plan',
            'Diagnosis',
            'Symptoms',
            'Treatment',
            'Notes',
            'Blood Pressure',
            'Temperature',
            'Pulse',
            'Respiratory Rate',
            'Oxygen Saturation',
            'Weight',
            'Height',
            'Is Private',
            'Created At',
            'Updated At',
        ]);

        $this->medicalRecordsQuery($filters)
            ->orderBy('created_at', 'desc')
            ->chunk(200, function ($records) use ($handle) {
                foreach ($records as $record) {
                    $vitals = is_array($record->vital_signs) ? $record->vital_signs : [];
                    $doctorEmail = $record->doctor
                        ? ($record->doctor->email ?: ($record->doctor->user->email ?? ''))
                        : '';

                    fputcsv($handle, [
                        $record->id,
                        $record->patient?->patient_id ?? '',
                        $record->patient?->email ?? '',
                        $record->doctor_id,
                        $doctorEmail,
                        $record->appointment_id,
                        $record->record_type,
                        $record->record_date ? $record->record_date->format('Y-m-d') : '',
                        $record->follow_up_date ? $record->follow_up_date->format('Y-m-d') : '',
                        $record->presenting_complaint ?? '',
                        $record->history_of_presenting_complaint ?? '',
                        $record->past_medical_history ?? '',
                        $record->drug_history ?? '',
                        $record->allergies ?? '',
                        $record->social_history ?? '',
                        $record->family_history ?? '',
                        $record->ideas_concerns_expectations ?? '',
                        $record->plan ?? '',
                        $record->diagnosis ?? '',
                        $record->symptoms ?? '',
                        $record->treatment ?? '',
                        $record->notes ?? '',
                        $vitals['blood_pressure'] ?? $vitals['Blood Pressure'] ?? '',
                        $vitals['temperature'] ?? $vitals['Temperature'] ?? '',
                        $vitals['pulse'] ?? $vitals['Pulse'] ?? '',
                        $vitals['respiratory_rate'] ?? $vitals['Respiratory Rate'] ?? '',
                        $vitals['oxygen_saturation'] ?? $vitals['Oxygen Saturation'] ?? '',
                        $vitals['weight'] ?? $vitals['Weight'] ?? '',
                        $vitals['height'] ?? $vitals['Height'] ?? '',
                        $record->is_private ? '1' : '0',
                        $record->created_at?->format('Y-m-d H:i:s') ?? '',
                        $record->updated_at?->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            });

        fclose($handle);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{packaged: int, missing_files: int}
     */
    private function writeAttachmentsCsv(string $path, array $filters): array
    {
        $handle = fopen($path, 'w');
        if ($handle === false) {
            throw new \RuntimeException('Unable to write attachments export file.');
        }

        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($handle, [
            'Attachment ID',
            'Medical Record ID',
            'Patient ID',
            'File Name',
            'File Category',
            'File Type',
            'File Extension',
            'File Size Bytes',
            'Is Private',
            'Zip Path',
            'File Status',
            'Uploaded At',
        ]);

        $packaged = 0;
        $missingFiles = 0;

        $this->attachmentsQuery($filters)
            ->with(['medicalRecord.patient'])
            ->orderBy('medical_record_id')
            ->orderBy('id')
            ->chunk(200, function ($attachments) use ($handle, &$packaged, &$missingFiles) {
                foreach ($attachments as $attachment) {
                    $zipPath = $this->attachmentZipPath($attachment);
                    $resolved = $this->resolveAttachmentLocalPath($attachment);
                    $fileStatus = $resolved ? 'included' : 'missing';

                    if ($resolved) {
                        $packaged++;
                    } else {
                        $missingFiles++;
                    }

                    fputcsv($handle, [
                        $attachment->id,
                        $attachment->medical_record_id,
                        $attachment->medicalRecord?->patient?->patient_id ?? '',
                        $attachment->file_name,
                        $attachment->file_category,
                        $attachment->file_type,
                        $attachment->file_extension,
                        $attachment->file_size,
                        $attachment->is_private ? '1' : '0',
                        $zipPath,
                        $fileStatus,
                        $attachment->created_at?->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            });

        fclose($handle);

        return [
            'packaged' => $packaged,
            'missing_files' => $missingFiles,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<string>
     */
    private function addAttachmentsToZip(ZipArchive $zip, array $filters): array
    {
        $tempPaths = [];

        $this->attachmentsQuery($filters)
            ->orderBy('id')
            ->chunk(100, function ($attachments) use ($zip, &$tempPaths) {
                foreach ($attachments as $attachment) {
                    $resolved = $this->resolveAttachmentLocalPath($attachment);
                    if (! $resolved) {
                        continue;
                    }

                    $zip->addFile($resolved['path'], $this->attachmentZipPath($attachment));

                    if ($resolved['cleanup']) {
                        $tempPaths[] = $resolved['path'];
                    }
                }
            });

        return $tempPaths;
    }

    private function attachmentZipPath(MedicalRecordAttachment $attachment): string
    {
        $name = $attachment->file_name ?: ('file.' . ($attachment->file_extension ?: 'bin'));
        $safeName = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $name) ?: 'file';

        return 'attachments/record_' . $attachment->medical_record_id . '/'
            . $attachment->id . '_' . $safeName;
    }

    /**
     * @return array{path: string, cleanup: bool}|null
     */
    private function resolveAttachmentLocalPath(MedicalRecordAttachment $attachment): ?array
    {
        $location = $attachment->resolveStorageLocation();
        if (! $location) {
            return null;
        }

        if ($location['disk'] === '_absolute') {
            return is_file($location['path'])
                ? ['path' => $location['path'], 'cleanup' => false]
                : null;
        }

        $disk = Storage::disk($location['disk']);

        if (method_exists($disk, 'path')) {
            $localPath = $disk->path($location['path']);
            if (is_file($localPath)) {
                return ['path' => $localPath, 'cleanup' => false];
            }
        }

        if (! $disk->exists($location['path'])) {
            return null;
        }

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('attach_export_', true)
            . '_' . basename($location['path']);
        file_put_contents($tempPath, $disk->get($location['path']));

        return ['path' => $tempPath, 'cleanup' => true];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $counts
     */
    private function buildManifest(Department $department, User $user, array $filters, array $counts): string
    {
        $lines = [
            'Clinic Data Export',
            '==================',
            'Generated: ' . now()->toDateTimeString(),
            'Clinic: ' . $department->name . ' (ID ' . $department->id . ')',
            'Exported by: ' . ($user->name ?? 'Admin') . ' (' . ($user->email ?? 'unknown') . ')',
            '',
            'Filters',
            '-------',
            'Patient registration from: ' . ($filters['reg_from'] ?: 'any'),
            'Patient registration to: ' . ($filters['reg_to'] ?: 'any'),
            'Patient status: ' . ($filters['status'] ?: 'any'),
            'Record date from: ' . ($filters['record_date_from'] ?: 'any'),
            'Record date to: ' . ($filters['record_date_to'] ?: 'any'),
            'Record type: ' . ($filters['record_type'] ?: 'any'),
            'Doctor ID: ' . ($filters['doctor_id'] ?: 'any'),
            'Include private notes: ' . ($filters['include_private'] ? 'yes' : 'no'),
            'Include attachments: ' . ($filters['include_attachments'] ? 'yes' : 'no'),
            '',
            'Counts',
            '------',
            'Patients: ' . $counts['patient_count'],
            'Medical records: ' . $counts['record_count'],
            'Attachments matched: ' . ($counts['attachment_count'] ?? 0),
        ];

        if ($filters['include_attachments']) {
            $lines[] = 'Attachment files packaged: ' . ($counts['attachments_packaged'] ?? 0);
            $lines[] = 'Attachment files missing: ' . ($counts['attachments_missing_files'] ?? 0);
        }

        $lines[] = '';
        $lines[] = 'Files';
        $lines[] = '-----';
        $lines[] = '- export_manifest.txt';
        $lines[] = '- patients.csv';
        $lines[] = '- medical_records.csv';

        if ($filters['include_attachments']) {
            $lines[] = '- attachments.csv';
            $lines[] = '- attachments/ (binary files referenced in attachments.csv)';
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $counts
     */
    private function logExport(User $user, Department $department, array $filters, array $counts): void
    {
        $description = 'Clinic data export: ' . $department->name
            . ' (' . $counts['patient_count'] . ' patients, ' . $counts['record_count'] . ' records';

        if ($filters['include_attachments']) {
            $description .= ', ' . ($counts['attachment_count'] ?? 0) . ' attachments';
        }

        $description .= ')';

        UserActivity::log([
            'user_id' => $user->id,
            'action' => 'export',
            'model_type' => Department::class,
            'model_id' => $department->id,
            'description' => $description,
            'new_values' => [
                'export_type' => 'clinic_data',
                'filters' => $filters,
                'patient_count' => $counts['patient_count'],
                'record_count' => $counts['record_count'],
                'attachment_count' => $counts['attachment_count'] ?? 0,
                'attachments_packaged' => $counts['attachments_packaged'] ?? 0,
                'attachments_missing_files' => $counts['attachments_missing_files'] ?? 0,
            ],
            'severity' => 'medium',
        ]);
    }
}
