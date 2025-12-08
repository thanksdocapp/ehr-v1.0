<?php

namespace App\Services;

use App\Models\GeneratedDocument;
use App\Models\Patient;
use App\Models\Template;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentPdfService
{
    protected TemplateMergeService $mergeService;

    public function __construct(TemplateMergeService $mergeService)
    {
        $this->mergeService = $mergeService;
    }

    /**
     * Generate a PDF document from a template for a patient.
     *
     * @param Template $template
     * @param Patient $patient
     * @param array $options
     * @return GeneratedDocument
     */
    public function generate(Template $template, Patient $patient, array $options = []): GeneratedDocument
    {
        $user = Auth::user();
        $customData = $options['custom_data'] ?? [];

        // Merge patient data into template
        $mergedContent = $this->mergeService->merge($template, $patient, $user, $customData);

        // Wrap content in HTML structure
        $htmlContent = $this->wrapInHtmlDocument($mergedContent, $template, $patient);

        // Generate PDF
        $pdfContent = $this->renderPdf($htmlContent, $options);

        // Generate filename and path
        $fileName = $this->generateFileName($template, $patient);
        $filePath = $this->generateFilePath($patient);
        $fullPath = $filePath . '/' . $fileName;

        // Store the PDF
        Storage::disk('private')->put($fullPath, $pdfContent);

        // Create the generated document record
        $document = GeneratedDocument::create([
            'template_id' => $template->id,
            'patient_id' => $patient->id,
            'file_path' => $fullPath,
            'file_name' => $fileName,
            'title' => $options['title'] ?? $template->name,
            'rendered_content' => $mergedContent,
            'status' => 'draft',
            'generated_by' => $user->id,
            'notes' => $options['notes'] ?? null,
        ]);

        return $document;
    }

    /**
     * Regenerate PDF for an existing document.
     */
    public function regenerate(GeneratedDocument $document, array $options = []): GeneratedDocument
    {
        if (!$document->isDraft()) {
            throw new \Exception('Only draft documents can be regenerated.');
        }

        // Delete old PDF if exists
        if ($document->file_path && Storage::disk('private')->exists($document->file_path)) {
            Storage::disk('private')->delete($document->file_path);
        }

        $template = $document->template;
        $patient = $document->patient;
        $customData = $options['custom_data'] ?? [];

        // Merge and generate new PDF
        $mergedContent = $this->mergeService->merge($template, $patient, Auth::user(), $customData);
        $htmlContent = $this->wrapInHtmlDocument($mergedContent, $template, $patient);
        $pdfContent = $this->renderPdf($htmlContent, $options);

        // Generate new filename
        $fileName = $this->generateFileName($template, $patient);
        $filePath = $this->generateFilePath($patient);
        $fullPath = $filePath . '/' . $fileName;

        // Store the PDF
        Storage::disk('private')->put($fullPath, $pdfContent);

        // Update document record
        $document->update([
            'file_path' => $fullPath,
            'file_name' => $fileName,
            'rendered_content' => $mergedContent,
            'title' => $options['title'] ?? $document->title,
            'notes' => $options['notes'] ?? $document->notes,
        ]);

        return $document->fresh();
    }

    /**
     * Render HTML content to PDF.
     */
    protected function renderPdf(string $htmlContent, array $options = []): string
    {
        $dompdfOptions = new Options();
        $dompdfOptions->set('isRemoteEnabled', true);
        $dompdfOptions->set('isHtml5ParserEnabled', true);
        $dompdfOptions->set('defaultFont', 'sans-serif');

        $dompdf = new Dompdf($dompdfOptions);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper(
            $options['paper'] ?? 'A4',
            $options['orientation'] ?? 'portrait'
        );
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Wrap content in a full HTML document with styling.
     */
    protected function wrapInHtmlDocument(string $content, Template $template, Patient $patient): string
    {
        $clinicName = $this->getClinicSetting('hospital_name', config('app.name'));
        $clinicLogo = $this->getClinicLogo();
        $documentTitle = $template->name . ' - ' . ($patient->full_name ?? $patient->first_name);

        $logoHtml = '';
        if ($clinicLogo) {
            $logoHtml = '<img src="' . $clinicLogo . '" alt="' . e($clinicName) . '" style="max-height: 60px; max-width: 200px;">';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{$documentTitle}</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header img {
            max-height: 60px;
        }
        .header h1 {
            margin: 10px 0 5px 0;
            font-size: 18pt;
            color: #667eea;
        }
        .header .clinic-info {
            font-size: 10pt;
            color: #666;
        }
        .content {
            padding: 0 10px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        p {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .signature-block {
            margin-top: 40px;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 250px;
            margin-top: 50px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        {$logoHtml}
        <div class="clinic-info">
            <strong>{$clinicName}</strong>
        </div>
    </div>

    <div class="content">
        {$content}
    </div>

    <div class="footer">
        Generated on {$this->getCurrentDate()} | Confidential Medical Document
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate a unique filename for the PDF.
     */
    protected function generateFileName(Template $template, Patient $patient): string
    {
        $templateSlug = Str::slug($template->name);
        $patientId = $patient->patient_id ?? $patient->id;
        $timestamp = now()->format('Ymd_His');

        return "{$templateSlug}_{$patientId}_{$timestamp}.pdf";
    }

    /**
     * Generate the storage path for the PDF.
     */
    protected function generateFilePath(Patient $patient): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        return "generated_documents/{$year}/{$month}";
    }

    /**
     * Get clinic logo URL.
     */
    protected function getClinicLogo(): ?string
    {
        $logoPath = $this->getClinicSetting('site_logo');

        if (!$logoPath) {
            return null;
        }

        // Convert to absolute URL for PDF rendering
        if (str_starts_with($logoPath, 'http')) {
            return $logoPath;
        }

        if (str_starts_with($logoPath, 'logos/') || str_starts_with($logoPath, 'settings/')) {
            return public_path('storage/' . $logoPath);
        }

        return public_path($logoPath);
    }

    /**
     * Get clinic setting.
     */
    protected function getClinicSetting(string $key, $default = ''): string
    {
        if (class_exists(\App\Models\SiteSetting::class)) {
            $value = \App\Models\SiteSetting::get($key);
            if ($value) {
                return $value;
            }
        }

        if (class_exists(\App\Models\Setting::class)) {
            $value = \App\Models\Setting::get($key);
            if ($value) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Get current date formatted.
     */
    protected function getCurrentDate(): string
    {
        return now()->format('d/m/Y H:i');
    }

    /**
     * Get the raw PDF content for a document.
     */
    public function getPdfContent(GeneratedDocument $document): ?string
    {
        if (!$document->pdfExists()) {
            return null;
        }

        return Storage::disk('private')->get($document->file_path);
    }

    /**
     * Stream PDF for download.
     */
    public function streamPdf(GeneratedDocument $document)
    {
        if (!$document->pdfExists()) {
            throw new \Exception('PDF file not found.');
        }

        return Storage::disk('private')->download(
            $document->file_path,
            Str::slug($document->title) . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
