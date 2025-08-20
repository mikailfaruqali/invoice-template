# Laravel Invoice Template Generator

Generate PDF invoices with customizable templates in Laravel.

## Installation

```bash
composer require mikailfaruqali/invoice-template
php artisan vendor:publish --provider="Snawbar\InvoiceTemplate\InvoiceTemplateServiceProvider"
php artisan migrate
```

## Requirements

Install wkhtmltopdf:
- **Ubuntu:** `sudo apt-get install wkhtmltopdf`
- **Windows:** Download from https://wkhtmltopdf.org/downloads.html

## Usage

Create template:
```php
InvoiceTemplate::create($request); // route, name, header, content, footer
```

Generate PDF:
```php
// Show in browser
InvoiceTemplate::route('my-invoice')
    ->withData(['company_name' => 'My Company'])
    ->a4()
    ->inline();

// Save to file
$path = InvoiceTemplate::route('my-invoice')
    ->withData(['company_name' => 'My Company'])
    ->save();
```

## Placeholders

```html
{company_name}        <!-- Your data -->
{current_date}        <!-- 2025-08-20 -->
{current_user}        <!-- mikailfaruqali -->
{current_datetime}    <!-- 2025-08-20 13:30:17 -->
```

## License

MIT