<?php

namespace App\Helpers;

/**
 * Security Helper Functions
 * 
 * Provides security-related helper functions for sanitization and validation
 */
class SecurityHelper
{
    /**
     * Sanitize HTML content to prevent XSS attacks
     * 
     * This is a basic sanitization. For production, consider using HTMLPurifier
     * or similar library for more robust HTML sanitization.
     * 
     * @param string|null $html
     * @return string
     */
    public static function sanitizeHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Basic HTML sanitization - strip dangerous tags and attributes
        // For production, consider using: composer require ezyang/htmlpurifier
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><a><img><table><thead><tbody><tr><td><th>';
        
        // Strip script tags and event handlers
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi', '', $html);
        $html = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/on\w+\s*=\s*[^\s>]*/i', '', $html);
        
        // Strip dangerous protocols
        $html = preg_replace('/javascript:/i', '', $html);
        $html = preg_replace('/data:text\/html/i', '', $html);
        $html = preg_replace('/vbscript:/i', '', $html);
        
        // Strip style tags that could contain malicious CSS
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/gi', '', $html);
        
        // Basic strip_tags with allowed tags
        $html = strip_tags($html, $allowedTags);
        
        // Clean up any remaining dangerous attributes
        $html = preg_replace('/<(\w+)[^>]*\s(on\w+|javascript:|data:text\/html)[^>]*>/i', '<$1>', $html);
        
        return trim($html);
    }

    /**
     * Escape HTML while preserving basic formatting
     * 
     * @param string|null $text
     * @return string
     */
    public static function escapeHtml(?string $text): string
    {
        if (empty($text)) {
            return '';
        }
        
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Validate and sanitize file MIME type
     * 
     * @param string $mimeType
     * @param array $allowedTypes
     * @return bool
     */
    public static function isValidMimeType(string $mimeType, array $allowedTypes): bool
    {
        return in_array($mimeType, $allowedTypes);
    }
}

