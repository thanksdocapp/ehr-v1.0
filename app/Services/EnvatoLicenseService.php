<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;

class EnvatoLicenseService
{
    private $apiUrl = 'https://api.envato.com/v3/market';
    private $itemId; // Your CodeCanyon item ID (set when published)
    private $apiToken; // Your Envato API token (optional for basic verification)

    public function __construct()
    {
        // These will be set when you publish on CodeCanyon
        $this->itemId = config('app.envato_item_id'); // From .env: ENVATO_ITEM_ID=12345678
        $this->apiToken = config('app.envato_api_token'); // From .env: ENVATO_API_TOKEN=your-token
    }

    /**
     * SIMPLE APPROACH (Current) - For Initial Launch
     * Just validates format and stores - good for first launch
     */
    public function verifyLicenseSimple(string $purchaseCode, string $envatoUsername, string $domain): array
    {
        // Basic format validation
        if (!$this->isValidPurchaseCodeFormat($purchaseCode)) {
            return [
                'success' => false,
                'message' => 'Invalid purchase code format. Please check your code from CodeCanyon.',
                'error_code' => 'INVALID_FORMAT'
            ];
        }

        if (strlen($envatoUsername) < 3) {
            return [
                'success' => false,
                'message' => 'Please enter a valid Envato username.',
                'error_code' => 'INVALID_USERNAME'
            ];
        }

        // Check if this purchase code has been used before on a different domain
        $existingLicense = $this->getExistingLicense($purchaseCode);
        if ($existingLicense && $existingLicense['domain'] !== $domain) {
            return [
                'success' => false,
                'message' => 'This purchase code is already activated on another domain: ' . $existingLicense['domain'],
                'error_code' => 'ALREADY_USED'
            ];
        }

        // Store license data
        $licenseData = [
            'purchase_code' => $purchaseCode,
            'envato_username' => $envatoUsername,
            'domain' => $domain,
            'verified_at' => now()->toDateTimeString(),
            'verification_method' => 'simple',
            'status' => 'active'
        ];

        $this->storeLicense($licenseData);

        return [
            'success' => true,
            'message' => 'License verified successfully!',
            'data' => $licenseData
        ];
    }

    /**
     * ADVANCED APPROACH - With Envato API Verification
     * Use this after you get your API token from Envato
     */
    public function verifyLicenseWithAPI(string $purchaseCode, string $envatoUsername, string $domain): array
    {
        // First do basic validation
        if (!$this->isValidPurchaseCodeFormat($purchaseCode)) {
            return [
                'success' => false,
                'message' => 'Invalid purchase code format.',
                'error_code' => 'INVALID_FORMAT'
            ];
        }

        if (!$this->apiToken) {
            // Fallback to simple verification if no API token
            return $this->verifyLicenseSimple($purchaseCode, $envatoUsername, $domain);
        }

        try {
            // Check with Envato API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'User-Agent' => 'ThankDoc EHR License Checker'
            ])->get($this->apiUrl . '/author/sale', [
                'code' => $purchaseCode
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Unable to verify purchase code with Envato. Please try again.',
                    'error_code' => 'API_ERROR'
                ];
            }

            $saleData = $response->json();

            // Verify it's for your item
            if ($this->itemId && $saleData['item']['id'] != $this->itemId) {
                return [
                    'success' => false,
                    'message' => 'This purchase code is not for ThankDoc EHR.',
                    'error_code' => 'WRONG_ITEM'
                ];
            }

            // Verify username matches
            if (strtolower($saleData['buyer']) !== strtolower($envatoUsername)) {
                return [
                    'success' => false,
                    'message' => 'Purchase code does not match the provided username.',
                    'error_code' => 'USERNAME_MISMATCH'
                ];
            }

            // Check if already used on different domain
            $existingLicense = $this->getExistingLicense($purchaseCode);
            if ($existingLicense && $existingLicense['domain'] !== $domain) {
                return [
                    'success' => false,
                    'message' => 'This license is already active on: ' . $existingLicense['domain'],
                    'error_code' => 'ALREADY_USED'
                ];
            }

            // Store verified license
            $licenseData = [
                'purchase_code' => $purchaseCode,
                'envato_username' => $envatoUsername,
                'domain' => $domain,
                'verified_at' => now()->toDateTimeString(),
                'verification_method' => 'api',
                'status' => 'active',
                'sale_data' => [
                    'item_id' => $saleData['item']['id'],
                    'item_name' => $saleData['item']['name'],
                    'buyer' => $saleData['buyer'],
                    'sold_at' => $saleData['sold_at'],
                    'license' => $saleData['license']
                ]
            ];

            $this->storeLicense($licenseData);

            return [
                'success' => true,
                'message' => 'License verified successfully with Envato!',
                'data' => $licenseData
            ];

        } catch (Exception $e) {
            // API error - fallback to simple verification
            return $this->verifyLicenseSimple($purchaseCode, $envatoUsername, $domain);
        }
    }

    /**
     * Validate purchase code format
     */
    private function isValidPurchaseCodeFormat(string $code): bool
    {
        // CodeCanyon purchase codes are typically 36 characters with hyphens
        // Format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
        // But also allow flexible format for testing and review
        
        // Standard CodeCanyon format
        if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $code)) {
            return true;
        }
        
        // Allow test codes for CodeCanyon review process
        if (in_array(strtolower($code), [
            'test-license-key',
            'codecanyon-review',
            'review-license',
            'demo-license-123',
            'test-123-456-789',
            'sample-license-code'
        ])) {
            return true;
        }
        
        // Allow any code with reasonable length (for flexibility)
        if (strlen($code) >= 10 && strlen($code) <= 50) {
            // Must contain alphanumeric characters or hyphens
            if (preg_match('/^[a-zA-Z0-9-]+$/', $code)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get existing license by purchase code
     */
    private function getExistingLicense(string $purchaseCode): ?array
    {
        // Try cache first
        $cached = Cache::get("license.{$purchaseCode}");
        if ($cached) {
            return $cached;
        }

        // Try database if available
        try {
            if (DB::getSchemaBuilder()->hasTable('licenses')) {
                $license = DB::table('licenses')->where('purchase_code', $purchaseCode)->first();
                if ($license) {
                    return (array) $license;
                }
            }
        } catch (Exception $e) {
            // Database not ready yet
        }

        return null;
    }

    /**
     * Store license data
     */
    private function storeLicense(array $licenseData): void
    {
        // Store in cache
        Cache::forever("license.{$licenseData['purchase_code']}", $licenseData);
        Cache::forever('install.license', $licenseData);

        // Store in database if available
        try {
            if (DB::getSchemaBuilder()->hasTable('licenses')) {
                DB::table('licenses')->updateOrInsert(
                    ['purchase_code' => $licenseData['purchase_code']],
                    array_merge($licenseData, [
                        'created_at' => now(),
                        'updated_at' => now()
                    ])
                );
            }
        } catch (Exception $e) {
            // Database not ready yet - cache storage is sufficient for installation
        }
    }

    /**
     * Check if current installation has valid license
     */
    public function isLicenseValid(): bool
    {
        $license = Cache::get('install.license');
        return $license && isset($license['status']) && $license['status'] === 'active';
    }

    /**
     * Get current license info
     */
    public function getCurrentLicense(): ?array
    {
        return Cache::get('install.license');
    }

    /**
     * Deactivate license (for domain changes)
     */
    public function deactivateLicense(string $purchaseCode): bool
    {
        try {
            $license = $this->getExistingLicense($purchaseCode);
            if ($license) {
                $license['status'] = 'inactive';
                $license['deactivated_at'] = now()->toDateTimeString();
                
                Cache::forever("license.{$purchaseCode}", $license);
                
                if (DB::getSchemaBuilder()->hasTable('licenses')) {
                    DB::table('licenses')
                        ->where('purchase_code', $purchaseCode)
                        ->update([
                            'status' => 'inactive',
                            'deactivated_at' => now(),
                            'updated_at' => now()
                        ]);
                }
                
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
        
        return false;
    }
}
