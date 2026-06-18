<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordAttachment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use ZipArchive;

class AdminClinicDataExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function super_admin_can_preview_and_download_scoped_clinic_export(): void
    {
        [$deptA, $deptB, $doctorA, $patientA, $patientB, $publicRecord, $privateRecord] = $this->seedClinicFixtures();

        $admin = User::create([
            'name' => 'Super Admin Export',
            'email' => 'super-admin-export-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($admin, 'admin');

        $this->get(route('admin.clinic-export.index'))->assertOk()->assertSee('Clinic Data Export');

        $preview = $this->postJson(route('admin.clinic-export.preview'), [
            'department_id' => $deptA->id,
        ]);

        $preview->assertOk()
            ->assertJson([
                'patient_count' => 1,
                'record_count' => 1,
                'attachment_count' => 0,
                'attachments_included' => false,
            ]);

        $download = $this->post(route('admin.clinic-export.download'), [
            'department_id' => $deptA->id,
        ]);

        $download->assertOk();
        $download->assertHeader('content-disposition');

        $zipPath = $this->extractDownloadToTemp($download);
        $this->assertZipContainsExpectedFiles($zipPath, $patientA, $publicRecord, $privateRecord, false);

        $this->assertDatabaseHas('user_activities', [
            'user_id' => $admin->id,
            'action' => 'export',
            'model_type' => Department::class,
            'model_id' => $deptA->id,
        ]);

        @unlink($zipPath);

        // Other clinic should not leak into dept A export counts.
        $previewB = $this->postJson(route('admin.clinic-export.preview'), [
            'department_id' => $deptB->id,
        ]);
        $previewB->assertOk()->assertJson([
            'patient_count' => 1,
            'record_count' => 0,
            'attachment_count' => 0,
            'attachments_included' => false,
        ]);
    }

    /** @test */
    public function clinic_bound_admin_cannot_export_other_clinics(): void
    {
        [$deptA, $deptB] = $this->seedClinicFixtures();

        $clinicAdmin = User::create([
            'name' => 'Clinic Admin Export',
            'email' => 'clinic-admin-export-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
            'department_id' => $deptA->id,
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($clinicAdmin, 'admin');

        $this->postJson(route('admin.clinic-export.preview'), [
            'department_id' => $deptA->id,
        ])->assertOk();

        $this->postJson(route('admin.clinic-export.preview'), [
            'department_id' => $deptB->id,
        ])->assertForbidden();
    }

    /** @test */
    public function including_private_notes_adds_private_records_to_export(): void
    {
        [$deptA, , , $patientA, , $publicRecord, $privateRecord] = $this->seedClinicFixtures();

        $admin = User::create([
            'name' => 'Private Export Admin',
            'email' => 'private-export-admin-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($admin, 'admin');

        $this->postJson(route('admin.clinic-export.preview'), [
            'department_id' => $deptA->id,
            'include_private' => true,
        ])->assertOk()->assertJson([
            'patient_count' => 1,
            'record_count' => 2,
            'attachment_count' => 0,
            'attachments_included' => false,
        ]);

        $download = $this->post(route('admin.clinic-export.download'), [
            'department_id' => $deptA->id,
            'include_private' => '1',
        ]);

        $download->assertOk();
        $zipPath = $this->extractDownloadToTemp($download);
        $this->assertZipContainsExpectedFiles($zipPath, $patientA, $publicRecord, $privateRecord, true);
        @unlink($zipPath);
    }

    /** @test */
    public function non_admin_staff_cannot_access_clinic_export_routes(): void
    {
        $staff = User::create([
            'name' => 'Staff Export Block',
            'email' => 'staff-export-block-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'is_admin' => false,
            'is_active' => true,
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($staff, 'admin');

        $this->get(route('admin.clinic-export.index'))->assertRedirect(route('admin.login'));
    }

    /** @test */
    public function export_with_attachments_includes_files_and_manifest_in_zip(): void
    {
        [$deptA, , , , , $publicRecord] = $this->seedClinicFixtures();
        $attachment = $this->createAttachmentFile($publicRecord, 'scan.pdf', 'private', false);

        $admin = User::create([
            'name' => 'Attachment Export Admin',
            'email' => 'attachment-export-admin-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($admin, 'admin');

        $this->postJson(route('admin.clinic-export.preview'), [
            'department_id' => $deptA->id,
            'include_attachments' => true,
        ])->assertOk()->assertJson([
            'patient_count' => 1,
            'record_count' => 1,
            'attachment_count' => 1,
            'attachments_included' => true,
        ]);

        $download = $this->post(route('admin.clinic-export.download'), [
            'department_id' => $deptA->id,
            'include_attachments' => '1',
        ]);

        $download->assertOk();
        $zipPath = $this->extractDownloadToTemp($download);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);
        $this->assertNotFalse($zip->locateName('attachments.csv'));
        $expectedAttachmentPath = 'attachments/record_' . $publicRecord->id . '/' . $attachment->id . '_scan.pdf';
        $this->assertNotFalse($zip->locateName($expectedAttachmentPath));
        $attachmentsCsv = $zip->getFromName('attachments.csv');
        $manifest = $zip->getFromName('export_manifest.txt');
        $zip->close();

        $this->assertStringContainsString('scan.pdf', $attachmentsCsv);
        $this->assertStringContainsString('included', $attachmentsCsv);
        $this->assertStringContainsString('Attachment files packaged: 1', $manifest);

        @unlink($zipPath);
    }

    /** @test */
    public function private_attachments_are_excluded_unless_include_private_is_enabled(): void
    {
        [$deptA, , , , , $publicRecord] = $this->seedClinicFixtures();
        $this->createAttachmentFile($publicRecord, 'public-scan.pdf', 'private', false);
        $this->createAttachmentFile($publicRecord, 'private-scan.pdf', 'private', true);

        $admin = User::create([
            'name' => 'Private Attachment Admin',
            'email' => 'private-attachment-admin-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($admin, 'admin');

        $this->postJson(route('admin.clinic-export.preview'), [
            'department_id' => $deptA->id,
            'include_attachments' => true,
        ])->assertOk()->assertJson([
            'attachment_count' => 1,
        ]);

        $download = $this->post(route('admin.clinic-export.download'), [
            'department_id' => $deptA->id,
            'include_attachments' => '1',
        ]);

        $zipPath = $this->extractDownloadToTemp($download);
        $zip = new ZipArchive();
        $zip->open($zipPath);
        $attachmentsCsv = $zip->getFromName('attachments.csv');
        $zip->close();

        $this->assertStringContainsString('public-scan.pdf', $attachmentsCsv);
        $this->assertStringNotContainsString('private-scan.pdf', $attachmentsCsv);

        @unlink($zipPath);
    }

    private function createAttachmentFile(
        MedicalRecord $record,
        string $filename,
        string $disk,
        bool $isPrivate
    ): MedicalRecordAttachment {
        $relativePath = 'medical-records/' . $record->id . '/' . $filename;
        $directory = storage_path('app/' . $disk . '/medical-records/' . $record->id);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, 'test attachment payload');

        return MedicalRecordAttachment::create([
            'medical_record_id' => $record->id,
            'file_name' => $filename,
            'file_path' => $relativePath,
            'storage_disk' => $disk,
            'file_extension' => pathinfo($filename, PATHINFO_EXTENSION),
            'file_size' => 24,
            'file_category' => 'documents',
            'is_private' => $isPrivate,
        ]);
    }

    /**
     * @return array{0: Department, 1: Department, 2: Doctor, 3: Patient, 4: Patient, 5: MedicalRecord, 6: MedicalRecord}
     */
    private function seedClinicFixtures(): array
    {
        $deptA = Department::create([
            'name' => 'Export Clinic A',
            'slug' => 'export-clinic-a-' . uniqid(),
            'description' => 'Test clinic A',
            'is_active' => true,
        ]);

        $deptB = Department::create([
            'name' => 'Export Clinic B',
            'slug' => 'export-clinic-b-' . uniqid(),
            'description' => 'Test clinic B',
            'is_active' => true,
        ]);

        $doctorA = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Export',
            'last_name' => 'DoctorA',
            'slug' => 'export-doc-a-' . uniqid(),
            'specialization' => 'General',
            'department_id' => $deptA->id,
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'email' => 'doctor-a-' . uniqid() . '@example.com',
        ]);

        $patientA = Patient::create([
            'patient_id' => 'P-EXPORT-A-' . uniqid(),
            'first_name' => 'Alice',
            'last_name' => 'ExportA',
            'email' => 'alice-export-' . uniqid() . '@example.com',
            'phone' => '07123456780',
            'password' => bcrypt('password'),
            'department_id' => $deptA->id,
            'is_active' => true,
        ]);

        $patientB = Patient::create([
            'patient_id' => 'P-EXPORT-B-' . uniqid(),
            'first_name' => 'Bob',
            'last_name' => 'ExportB',
            'email' => 'bob-export-' . uniqid() . '@example.com',
            'phone' => '07123456781',
            'password' => bcrypt('password'),
            'department_id' => $deptB->id,
            'is_active' => true,
        ]);

        $publicRecord = MedicalRecord::create([
            'patient_id' => $patientA->id,
            'doctor_id' => $doctorA->id,
            'record_type' => 'consultation',
            'record_date' => now()->toDateString(),
            'diagnosis' => 'Public diagnosis marker',
            'is_private' => false,
        ]);

        $privateRecord = MedicalRecord::create([
            'patient_id' => $patientA->id,
            'doctor_id' => $doctorA->id,
            'record_type' => 'consultation',
            'record_date' => now()->toDateString(),
            'diagnosis' => 'Private diagnosis marker',
            'is_private' => true,
        ]);

        return [$deptA, $deptB, $doctorA, $patientA, $patientB, $publicRecord, $privateRecord];
    }

    private function extractDownloadToTemp($response): string
    {
        $tempPath = storage_path('app/temp/test-export-' . uniqid() . '.zip');
        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $sourcePath = $response->baseResponse->getFile()->getPathname();
        copy($sourcePath, $tempPath);

        return $tempPath;
    }

    private function assertZipContainsExpectedFiles(
        string $zipPath,
        Patient $patient,
        MedicalRecord $publicRecord,
        MedicalRecord $privateRecord,
        bool $includePrivate
    ): void {
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);

        $this->assertNotFalse($zip->locateName('export_manifest.txt'));
        $this->assertNotFalse($zip->locateName('patients.csv'));
        $this->assertNotFalse($zip->locateName('medical_records.csv'));

        $patientsCsv = $zip->getFromName('patients.csv');
        $recordsCsv = $zip->getFromName('medical_records.csv');
        $manifest = $zip->getFromName('export_manifest.txt');

        $zip->close();

        $this->assertStringContainsString($patient->patient_id, $patientsCsv);
        $this->assertStringContainsString('Public diagnosis marker', $recordsCsv);
        $this->assertStringContainsString('Clinic Data Export', $manifest);

        if ($includePrivate) {
            $this->assertStringContainsString('Private diagnosis marker', $recordsCsv);
        } else {
            $this->assertStringNotContainsString('Private diagnosis marker', $recordsCsv);
            $this->assertStringContainsString((string) $publicRecord->id, $recordsCsv);
        }
    }
}
