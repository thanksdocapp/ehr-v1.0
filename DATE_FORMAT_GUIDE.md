# Date Format Configuration

## Overview
The application now supports configurable date formats. By default, dates are displayed in **dd-mm-yyyy** format throughout the application.

## Configuration

### Config File: `config/hospital.php`
```php
'date_format' => env('DATE_FORMAT', 'd-m-Y'), // Default: dd-mm-yyyy
'time_format' => env('TIME_FORMAT', 'H:i'), // Default: 24-hour format
'datetime_format' => env('DATETIME_FORMAT', 'd-m-Y H:i'), // Default: dd-mm-yyyy HH:mm
```

### Environment Variables (`.env`)
You can override these in your `.env` file:
```env
DATE_FORMAT=d-m-Y
TIME_FORMAT=H:i
DATETIME_FORMAT=d-m-Y H:i
```

## Helper Functions

### Available Functions

#### `formatDate($date, $format = null)`
Formats a date string to the configured format.
```php
formatDate('2025-11-01') // Returns: 01-11-2025
formatDate('2025-11-01', 'd/m/Y') // Returns: 01/11/2025
```

#### `formatDateTime($datetime, $format = null)`
Formats a datetime string to the configured format.
```php
formatDateTime('2025-11-01 14:30:00') // Returns: 01-11-2025 14:30
```

#### `formatTime($time, $format = null)`
Formats a time string to the configured format.
```php
formatTime('14:30:00') // Returns: 14:30
```

#### `parseDateInput($date)`
Converts dd-mm-yyyy format to Y-m-d for database storage.
```php
parseDateInput('01-11-2025') // Returns: 2025-11-01
```

## Usage in Blade Templates

### Display Dates
```blade
{{ formatDate($appointment->appointment_date) }}
{{ formatDateTime($appointment->created_at) }}
```

### Display Dates in Tables
```blade
<td>{{ formatDate($patient->date_of_birth) }}</td>
<td>{{ formatDateTime($appointment->created_at) }}</td>
```

## Usage in Controllers

### Parsing User Input
```php
$data['date_of_birth'] = parseDateInput($request->date_of_birth);
$data['appointment_date'] = parseDateInput($request->appointment_date);
```

### Displaying Dates
```php
$formattedDate = formatDate($appointment->appointment_date);
```

## Important Notes

### HTML5 Date Inputs
HTML5 `<input type="date">` fields always use yyyy-mm-dd format internally, regardless of how they're displayed in the browser. The browser will automatically format the display according to the user's locale.

- **Input value**: Always send in yyyy-mm-dd format
- **Display**: Browser handles locale-specific display
- **Database storage**: Always store in Y-m-d format

### Carbon Date Formatting
When using Carbon directly, use the helper functions or config:
```php
$date->format(config('hospital.date_format'))
```

## Common Date Format Codes

- `d` - Day with leading zeros (01-31)
- `m` - Month with leading zeros (01-12)
- `Y` - 4-digit year (2025)
- `y` - 2-digit year (25)
- `H` - 24-hour format with leading zeros (00-23)
- `h` - 12-hour format with leading zeros (01-12)
- `i` - Minutes with leading zeros (00-59)
- `s` - Seconds with leading zeros (00-59)
- `A` - AM/PM

## Examples

### British Format (dd-mm-yyyy)
```env
DATE_FORMAT=d-m-Y
```

### American Format (mm-dd-yyyy)
```env
DATE_FORMAT=m-d-Y
```

### ISO Format (yyyy-mm-dd)
```env
DATE_FORMAT=Y-m-d
```

### 12-hour Time Format
```env
TIME_FORMAT=h:i A
```

## Migration Notes

- Old views using `->format('Y-m-d')` will continue to work
- Gradually update views to use `formatDate()` helper
- Controllers receiving date inputs from forms should use `parseDateInput()`
- Run `composer dump-autoload` after updates to load helpers
