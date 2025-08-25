# mikailfaruqali/invoice-template

Simple Laravel package for managing and rendering PDF invoice templates.

## Installation

```bash
composer require mikailfaruqali/invoice-template
php artisan vendor:publish --provider="Snawbar\\InvoiceTemplate\\InvoiceTemplateServiceProvider"
php artisan migrate
```

## Usage

```php
use Snawbar\InvoiceTemplate\InvoiceTemplate;

// Create a template
InvoiceTemplate::create($request);

// Render PDF in browser
InvoiceTemplate::template('my-invoice')->inline();

// Save PDF to file
$path = InvoiceTemplate::template('my-invoice')->save();
```

## Web UI

Visit `/invoice-templates` to manage templates with a simple interface.

## License

MIT