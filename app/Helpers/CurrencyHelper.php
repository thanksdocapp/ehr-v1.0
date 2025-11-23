<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class CurrencyHelper
{
    /**
     * Get the system's default currency from admin settings.
     */
    public static function getDefaultCurrency(): string
    {
        return Cache::remember('app_default_currency', 3600, function () {
            $setting = Setting::where('key', 'default_currency')->first();
            return $setting?->value ?? 'USD'; // Fallback to USD if not set
        });
    }

    /**
     * Get the system's currency symbol from admin settings.
     */
    public static function getCurrencySymbol(): string
    {
        return Cache::remember('app_currency_symbol', 3600, function () {
            $setting = Setting::where('key', 'currency_symbol')->first();
            return $setting?->value ?? '$'; // Fallback to $ if not set
        });
    }

    /**
     * Get the system's timezone from admin settings.
     */
    public static function getSystemTimezone(): string
    {
        return Cache::remember('app_system_timezone', 3600, function () {
            $setting = Setting::where('key', 'app_timezone')->first();
            return $setting?->value ?? 'UTC'; // Fallback to UTC if not set
        });
    }

    /**
     * Format an amount with the system's currency symbol.
     */
    public static function format(float $amount, int $decimals = 2): string
    {
        $symbol = self::getCurrencySymbol();
        return $symbol . number_format($amount, $decimals);
    }

    /**
     * Get currency name from currency code.
     */
    public static function getCurrencyName(string $currencyCode = null): string
    {
        $currencyCode = $currencyCode ?? self::getDefaultCurrency();
        
        $currencyNames = [
            // Major Currencies
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'JPY' => 'Japanese Yen',
            'CHF' => 'Swiss Franc',
            'CAD' => 'Canadian Dollar',
            'AUD' => 'Australian Dollar',
            'CNY' => 'Chinese Yuan',
            
            // African Currencies
            'ZAR' => 'South African Rand',
            'NGN' => 'Nigerian Naira',
            'GHS' => 'Ghanaian Cedi',
            'KES' => 'Kenyan Shilling',
            'UGX' => 'Ugandan Shilling',
            'TZS' => 'Tanzanian Shilling',
            'EGP' => 'Egyptian Pound',
            'MAD' => 'Moroccan Dirham',
            'TND' => 'Tunisian Dinar',
            'DZD' => 'Algerian Dinar',
            'ETB' => 'Ethiopian Birr',
            'RWF' => 'Rwandan Franc',
            'ZMW' => 'Zambian Kwacha',
            'BWP' => 'Botswana Pula',
            'NAD' => 'Namibian Dollar',
            'MZN' => 'Mozambican Metical',
            
            // Asian Currencies
            'INR' => 'Indian Rupee',
            'KRW' => 'South Korean Won',
            'SGD' => 'Singapore Dollar',
            'HKD' => 'Hong Kong Dollar',
            'MYR' => 'Malaysian Ringgit',
            'THB' => 'Thai Baht',
            'IDR' => 'Indonesian Rupiah',
            'PHP' => 'Philippine Peso',
            'VND' => 'Vietnamese Dong',
            'PKR' => 'Pakistani Rupee',
            'BDT' => 'Bangladeshi Taka',
            'LKR' => 'Sri Lankan Rupee',
            'NPR' => 'Nepalese Rupee',
            'AFN' => 'Afghan Afghani',
            'IRR' => 'Iranian Rial',
            'IQD' => 'Iraqi Dinar',
            'KWD' => 'Kuwaiti Dinar',
            'SAR' => 'Saudi Arabian Riyal',
            'AED' => 'UAE Dirham',
            'QAR' => 'Qatari Riyal',
            'BHD' => 'Bahraini Dinar',
            'OMR' => 'Omani Rial',
            'ILS' => 'Israeli Shekel',
            'JOD' => 'Jordanian Dinar',
            'LBP' => 'Lebanese Pound',
            
            // European Currencies
            'NOK' => 'Norwegian Krone',
            'SEK' => 'Swedish Krona',
            'DKK' => 'Danish Krone',
            'PLN' => 'Polish Zloty',
            'CZK' => 'Czech Koruna',
            'HUF' => 'Hungarian Forint',
            'RON' => 'Romanian Leu',
            'BGN' => 'Bulgarian Lev',
            'TRY' => 'Turkish Lira',
            'RUB' => 'Russian Ruble',
            'UAH' => 'Ukrainian Hryvnia',
            
            // American Currencies
            'MXN' => 'Mexican Peso',
            'BRL' => 'Brazilian Real',
            'ARS' => 'Argentine Peso',
            'CLP' => 'Chilean Peso',
            'COP' => 'Colombian Peso',
            'PEN' => 'Peruvian Sol',
            'UYU' => 'Uruguayan Peso',
            'PYG' => 'Paraguayan Guarani',
            'BOB' => 'Bolivian Boliviano',
            'VES' => 'Venezuelan Bolívar',
            
            // Oceania & Others
            'NZD' => 'New Zealand Dollar',
            'FJD' => 'Fijian Dollar',
            'PGK' => 'Papua New Guinea Kina',
            
            // Central Asian Currencies
            'KZT' => 'Kazakhstani Tenge',
            'UZS' => 'Uzbekistani Som',
            'KGS' => 'Kyrgyzstani Som',
            'TJS' => 'Tajikistani Somoni',
            'TMT' => 'Turkmenistani Manat',
        ];
        
        return $currencyNames[$currencyCode] ?? $currencyCode;
    }

    /**
     * Clear currency cache (call this when currency settings are updated).
     */
    public static function clearCache(): void
    {
        Cache::forget('app_default_currency');
        Cache::forget('app_currency_symbol');
        Cache::forget('app_system_timezone');
    }
}
