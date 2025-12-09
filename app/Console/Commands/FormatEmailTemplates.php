<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailTemplate;

class FormatEmailTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email-templates:format {--dry-run : Show what would be changed without actually updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Format all email templates with proper HTML structure and styling';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting email template formatting...');
        
        $templates = EmailTemplate::all();
        $this->info("Found {$templates->count()} email templates to format.");
        
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }
        
        $formatted = 0;
        $skipped = 0;
        
        foreach ($templates as $template) {
            $this->line("Processing: {$template->name}...");
            
            $originalBody = $template->body;
            $formattedBody = $this->formatTemplateBody($originalBody, $template->category);
            
            if ($originalBody !== $formattedBody) {
                if (!$this->option('dry-run')) {
                    $template->update(['body' => $formattedBody]);
                }
                $this->info("  ✓ Formatted: {$template->name}");
                $formatted++;
            } else {
                $this->comment("  - Skipped: {$template->name} (already formatted)");
                $skipped++;
            }
        }
        
        $this->newLine();
        $this->info("Formatting complete!");
        $this->info("  - Formatted: {$formatted}");
        $this->info("  - Skipped: {$skipped}");
        
        if ($this->option('dry-run')) {
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }
        
        return 0;
    }
    
    /**
     * Format email template body with proper HTML structure
     */
    protected function formatTemplateBody($body, $category = 'general')
    {
        // Clean up body first
        $body = trim($body);
        
        // Convert escaped newlines to actual newlines
        $body = str_replace(['\\n', '\n'], "\n", $body);
        
        // Check if already has proper HTML structure with DOCTYPE
        if (stripos($body, '<!DOCTYPE html>') !== false || (stripos($body, '<html') !== false && stripos($body, '<head') !== false && stripos($body, '<body') !== false)) {
            // Already has complete HTML structure, just ensure it's properly formatted
            return $this->ensureProperFormatting($body, $category);
        }
        
        // Extract content if it has partial HTML tags
        $hasHtmlTags = preg_match('/<[^>]+>/', $body);
        
        if ($hasHtmlTags) {
            // Has some HTML but not complete structure - extract inner content
            if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $body, $matches)) {
                $body = $matches[1];
            } elseif (preg_match('/<div[^>]*>(.*?)<\/div>/is', $body, $matches)) {
                $body = $matches[1];
            }
        } else {
            // Plain text - convert to HTML paragraphs
            $body = $this->convertPlainTextToHtml($body);
        }
        
        // Wrap in proper email template structure
        return $this->wrapInEmailTemplate($body, $category);
    }
    
    /**
     * Convert plain text to HTML paragraphs
     */
    protected function convertPlainTextToHtml($text)
    {
        $lines = explode("\n", $text);
        $html = '';
        $currentParagraph = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                if (!empty($currentParagraph)) {
                    $html .= '<p style="margin: 0 0 15px 0; color: #333333; font-size: 16px; line-height: 1.6;">' . trim($currentParagraph) . '</p>' . "\n";
                    $currentParagraph = '';
                }
            } elseif (preg_match('/^(\*\s*|-\s*|\d+\.\s*)/', $line)) {
                // List item
                if (!empty($currentParagraph)) {
                    $html .= '<p style="margin: 0 0 15px 0; color: #333333; font-size: 16px; line-height: 1.6;">' . trim($currentParagraph) . '</p>' . "\n";
                    $currentParagraph = '';
                }
                $html .= '<p style="margin: 0 0 8px 0; color: #333333; font-size: 16px; line-height: 1.6; padding-left: 20px;">• ' . preg_replace('/^(\*\s*|-\s*|\d+\.\s*)/', '', $line) . '</p>' . "\n";
            } else {
                $currentParagraph .= ($currentParagraph ? ' ' : '') . $line;
            }
        }
        
        if (!empty($currentParagraph)) {
            $html .= '<p style="margin: 0 0 15px 0; color: #333333; font-size: 16px; line-height: 1.6;">' . trim($currentParagraph) . '</p>' . "\n";
        }
        
        return $html ?: '<p style="margin: 0 0 15px 0; color: #333333; font-size: 16px; line-height: 1.6;">' . htmlspecialchars($text) . '</p>';
    }
    
    /**
     * Wrap content in professional email template structure
     */
    protected function wrapInEmailTemplate($content, $category = 'general')
    {
        // Determine color scheme based on category
        $colors = $this->getCategoryColors($category);
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email from {{hospital_name}}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: ' . $colors['header'] . '; padding: 30px 20px; text-align: center; border-bottom: 3px solid ' . $colors['accent'] . ';">
                            <h1 style="color: ' . $colors['headerText'] . '; margin: 0; font-size: 28px; font-weight: 600;">{{hospital_name}}</h1>
                            <p style="color: ' . $colors['headerText'] . '; margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">Professional Healthcare Services</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            ' . $content . '
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">
                                <strong>{{hospital_name}}</strong>
                            </p>
                            <p style="margin: 0 0 10px 0; color: #999999; font-size: 12px;">
                                {{hospital_address}}<br>
                                Phone: {{hospital_phone}} | Email: {{hospital_email}}
                            </p>
                            <p style="margin: 15px 0 0 0; color: #999999; font-size: 11px; line-height: 1.5;">
                                This is an automated email from {{hospital_name}}.<br>
                                Please do not reply directly to this email. If you have questions, please contact us at {{hospital_phone}} or visit {{hospital_website}}.
                            </p>
                            <p style="margin: 15px 0 0 0; color: #999999; font-size: 10px;">
                                © ' . date('Y') . ' {{hospital_name}}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
    
    /**
     * Get color scheme based on category
     */
    protected function getCategoryColors($category)
    {
        $schemes = [
            'appointment' => [
                'header' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'accent' => '#667eea',
                'headerText' => '#ffffff',
            ],
            'billing' => [
                'header' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'accent' => '#f5576c',
                'headerText' => '#ffffff',
            ],
            'prescription' => [
                'header' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                'accent' => '#4facfe',
                'headerText' => '#ffffff',
            ],
            'emergency' => [
                'header' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                'accent' => '#fa709a',
                'headerText' => '#ffffff',
            ],
            'notification' => [
                'header' => 'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
                'accent' => '#30cfd0',
                'headerText' => '#ffffff',
            ],
            'welcome' => [
                'header' => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
                'accent' => '#a8edea',
                'headerText' => '#2c3e50',
            ],
            'reminder' => [
                'header' => 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
                'accent' => '#fcb69f',
                'headerText' => '#2c3e50',
            ],
            'general' => [
                'header' => 'linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%)',
                'accent' => '#1cc88a',
                'headerText' => '#ffffff',
            ],
        ];
        
        return $schemes[$category] ?? $schemes['general'];
    }
    
    /**
     * Ensure existing HTML templates are properly formatted
     */
    protected function ensureProperFormatting($html, $category = 'general')
    {
        // If it already has DOCTYPE and complete structure, ensure it follows our standards
        if (stripos($html, '<!DOCTYPE html>') !== false && stripos($html, '<html') !== false && stripos($html, '<body') !== false) {
            // Check if it has our standard footer structure
            if (stripos($html, '{{hospital_name}}') === false || stripos($html, 'Professional Healthcare Services') === false) {
                // Missing our standard structure - extract content and rewrap
                if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
                    $content = $matches[1];
                    // Remove footer if exists
                    $content = preg_replace('/<div[^>]*footer[^>]*>.*?<\/div>/is', '', $content);
                    $content = preg_replace('/<table[^>]*footer[^>]*>.*?<\/table>/is', '', $content);
                    return $this->wrapInEmailTemplate(trim($content), $category);
                }
            }
            
            // Has proper structure, just clean up
            return $this->cleanupHtml($html);
        }
        
        // If it has HTML tags but no DOCTYPE, add proper structure
        if (stripos($html, '<html') !== false || stripos($html, '<body') !== false) {
            // Extract body content
            if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
                return $this->wrapInEmailTemplate(trim($matches[1]), $category);
            }
            
            // Already has some HTML structure, just ensure DOCTYPE
            if (stripos($html, '<!DOCTYPE') === false) {
                $html = '<!DOCTYPE html>' . "\n" . $html;
            }
            return $this->cleanupHtml($html);
        }
        
        return $html;
    }
    
    /**
     * Clean up HTML formatting
     */
    protected function cleanupHtml($html)
    {
        // Remove extra whitespace
        $html = preg_replace('/>\s+</', '><', $html);
        
        // Ensure proper indentation (basic)
        $html = str_replace('><', ">\n<", $html);
        
        return trim($html);
    }
}

