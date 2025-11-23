<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PdfService
{
    /**
     * Generate PDF from HTML content.
     *
     * @param string $html
     * @param string $filename
     * @param array $options
     * @return string Path to saved PDF file
     */
    public function generateFromHtml(string $html, string $filename, array $options = []): string
    {
        // Try to use dompdf if available
        if (class_exists(\Dompdf\Dompdf::class)) {
            return $this->generateWithDompdf($html, $filename, $options);
        }

        // Fallback: throw exception asking to install dompdf
        throw new \Exception('PDF generation requires dompdf/dompdf package. Please install it using: composer require dompdf/dompdf');
    }

    /**
     * Generate PDF using DomPDF.
     *
     * @param string $html
     * @param string $filename
     * @param array $options
     * @return string
     */
    protected function generateWithDompdf(string $html, string $filename, array $options = []): string
    {
        $dompdf = new \Dompdf\Dompdf();
        
        $dompdfOptions = new \Dompdf\Options();
        $dompdfOptions->setIsRemoteEnabled($options['remote_enabled'] ?? true);
        $dompdfOptions->setChroot(base_path());
        
        $dompdf->setOptions($dompdfOptions);
        $dompdf->setPaper($options['paper'] ?? 'A4', $options['orientation'] ?? 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        // Generate unique filename
        $storagePath = 'patient_documents/' . date('Y/m');
        $fullPath = $storagePath . '/' . $filename . '.pdf';

        // Ensure directory exists
        Storage::disk('private')->makeDirectory($storagePath);

        // Save PDF
        Storage::disk('private')->put($fullPath, $dompdf->output());

        return $fullPath;
    }

    /**
     * Get PDF file path for download.
     *
     * @param string $path
     * @return string
     */
    public function getPdfPath(string $path): string
    {
        return Storage::disk('private')->path($path);
    }

    /**
     * Check if PDF exists.
     *
     * @param string $path
     * @return bool
     */
    public function pdfExists(string $path): bool
    {
        return Storage::disk('private')->exists($path);
    }
}

