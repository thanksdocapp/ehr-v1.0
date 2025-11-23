<?php

/**
 * Script to convert date formats from Y-m-d to d-m-Y in Blade views
 * 
 * This script finds and replaces date format patterns for DISPLAY purposes.
 * It does NOT change HTML5 date input values (which must remain Y-m-d).
 * 
 * Usage: php convert_date_formats.php
 */

require __DIR__ . '/vendor/autoload.php';

$viewsDirectory = __DIR__ . '/resources/views';
$backupDirectory = __DIR__ . '/backups/date_format_conversion_' . date('Y-m-d_His');

// Create backup directory
if (!is_dir(__DIR__ . '/backups')) {
    mkdir(__DIR__ . '/backups', 0755, true);
}
mkdir($backupDirectory, 0755, true);

echo "=== Date Format Conversion Script ===\n";
echo "Backing up files to: {$backupDirectory}\n\n";

// Function to process a file
function processFile($filePath, $backupDir, $viewsDir) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    $changes = 0;
    
    $lines = explode("\n", $content);
    $newLines = [];
    
    foreach ($lines as $lineNum => $line) {
        $originalLine = $line;
        
        // Skip lines with HTML5 date inputs - they must remain Y-m-d
        if (preg_match('/<input[^>]*type=[\'"]date[\'"]/i', $line)) {
            $newLines[] = $line;
            continue;
        }
        
        // Skip min/max attributes on date inputs
        if (preg_match('/\s(min|max)=[\'"]/', $line) && preg_match('/date\([\'"]Y-m-d/', $line)) {
            $newLines[] = $line;
            continue;
        }
        
        // 1. Replace $var->format('Y-m-d') with formatDate($var) for display
        $line = preg_replace_callback(
            '/(\$[a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*?)\->format\([\'"]Y-m-d[\'"]\)/',
            function($matches) use (&$changes) {
                $changes++;
                return 'formatDate(' . $matches[1] . ')';
            },
            $line
        );
        
        // 2. Replace $var->format('M d, Y') with formatDate($var)
        $line = preg_replace_callback(
            '/(\$[a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*?)\->format\([\'"]M d, Y[\'"]\)/',
            function($matches) use (&$changes) {
                $changes++;
                return 'formatDate(' . $matches[1] . ')';
            },
            $line
        );
        
        // 3. Replace $var->format('F j, Y') with formatDate($var)
        $line = preg_replace_callback(
            '/(\$[a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*?)\->format\([\'"]F j, Y[\'"]\)/',
            function($matches) use (&$changes) {
                $changes++;
                return 'formatDate(' . $matches[1] . ')';
            },
            $line
        );
        
        // 4. Replace $var->format('Y-m-d H:i') with formatDateTime($var)
        $line = preg_replace_callback(
            '/(\$[a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*?)\->format\([\'"]Y-m-d H:i[\'"]\)/',
            function($matches) use (&$changes) {
                $changes++;
                return 'formatDateTime(' . $matches[1] . ')';
            },
            $line
        );
        
        // 5. Replace $var->format('M d, Y H:i') with formatDateTime($var)
        $line = preg_replace_callback(
            '/(\$[a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*?)\->format\([\'"]M d, Y H:i[\'"]\)/',
            function($matches) use (&$changes) {
                $changes++;
                return 'formatDateTime(' . $matches[1] . ')';
            },
            $line
        );
        
        // 6. Replace $var->format('M d, Y g:i A') with formatDateTime($var)
        $line = preg_replace_callback(
            '/(\$[a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*?)\->format\([\'"]M d, Y g:i A[\'"]\)/',
            function($matches) use (&$changes) {
                $changes++;
                return 'formatDateTime(' . $matches[1] . ')';
            },
            $line
        );
        
        // 7. Replace \Carbon\Carbon::parse($var)->format('Y-m-d') with formatDate($var)
        $line = preg_replace_callback(
            '/\\\\Carbon\\\\Carbon::parse\((\$[a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*?)\)\->format\([\'"]Y-m-d[\'"]\)/',
            function($matches) use (&$changes) {
                $changes++;
                return 'formatDate(' . $matches[1] . ')';
            },
            $line
        );
        
        // 8. Replace \Carbon\Carbon::parse($var)->format('M d, Y') with formatDate($var)
        $line = preg_replace_callback(
            '/\\\\Carbon\\\\Carbon::parse\((\$[a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*?)\)\->format\([\'"]M d, Y[\'"]\)/',
            function($matches) use (&$changes) {
                $changes++;
                return 'formatDate(' . $matches[1] . ')';
            },
            $line
        );
        
        // 9. Replace standalone date('Y-m-d') with date('d-m-Y') for display (not in input values)
        // Only if not in a value attribute or min/max attribute
        if (!preg_match('/\s(value|min|max)=[\'"]/', $line)) {
            $line = preg_replace_callback(
                '/date\([\'"]Y-m-d[\'"]\)/',
                function($matches) use (&$changes) {
                    $changes++;
                    return 'date(\'d-m-Y\')';
                },
                $line
            );
        }
        
        $newLines[] = $line;
    }
    
    $newContent = implode("\n", $newLines);
    
    if ($newContent !== $originalContent) {
        // Create backup
        $relativePath = str_replace($viewsDir . DIRECTORY_SEPARATOR, '', $filePath);
        $backupPath = $backupDir . DIRECTORY_SEPARATOR . str_replace(DIRECTORY_SEPARATOR, '_', $relativePath);
        $backupDirPath = dirname($backupPath);
        if (!is_dir($backupDirPath)) {
            mkdir($backupDirPath, 0755, true);
        }
        file_put_contents($backupPath, $originalContent);
        
        // Write new content
        file_put_contents($filePath, $newContent);
        
        return $changes;
    }
    
    return 0;
}

// Find all Blade files
function findBladeFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php' && strpos($file->getFilename(), '.blade.php') !== false) {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

// Process all files
$files = findBladeFiles($viewsDirectory);
$totalFiles = 0;
$totalChanges = 0;

echo "Found " . count($files) . " Blade files\n";
echo "Processing files...\n\n";

foreach ($files as $file) {
    $changes = processFile($file, $backupDirectory, $viewsDirectory);
    if ($changes > 0) {
        $relativePath = str_replace($viewsDirectory . DIRECTORY_SEPARATOR, '', $file);
        echo "✓ Updated: {$relativePath} ({$changes} changes)\n";
        $totalFiles++;
        $totalChanges += $changes;
    }
}

echo "\n=== Conversion Complete ===\n";
echo "Files modified: {$totalFiles}\n";
echo "Total changes: {$totalChanges}\n";
echo "Backups saved to: {$backupDirectory}\n";
echo "\nNote: HTML5 date input values were preserved (they require Y-m-d format).\n";

