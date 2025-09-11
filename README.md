# Laravel Invoice Template Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mikailfaruqali/invoice-template.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/invoice-template)
[![Total Downloads](https://img.shields.io/packagist/dt/mikailfaruqali/invoice-template.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/invoice-template)
[![License](https://img.shields.io/packagist/l/mikailfaruqali/invoice-template.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/invoice-template)

A powerful Laravel package for generating professional PDF invoices with customizable templates. Features advanced header/footer support, multi-language capabilities, and a comprehensive template management system powered by wkhtmltopdf via Snappy PDF generator.

## Features

### 🎨 **Template Management**
- **Database-driven templates** with full CRUD operations
- **Multi-language support** with locale-based template selection
- **Page-specific templates** using customizable slugs (invoice, receipt, summary, etc.)
- **Visual template editor** with live preview capabilities
- **Password-protected content editing** for security

### 📄 **PDF Generation**
- **Professional PDF output** using wkhtmltopdf engine
- **Customizable headers and footers** with Blade template support
- **Multiple paper sizes** (A4, A5, A3, Letter, Legal)
- **Portrait and landscape orientations**
- **Precise margin control** (top, bottom, left, right)
- **Header/footer spacing configuration**

### 🌐 **Internationalization**
- **Multi-language template support**
- **RTL/LTR text direction** handling
- **Locale-based template fallback** system
- **Session-based direction configuration**

### ⚙️ **Advanced Configuration**
- **Comprehensive PDF options** (DPI, image quality, compression, etc.)
- **Custom font support** with font directory configuration
- **Flexible middleware** for route protection
- **Configurable table names** and route prefixes
- **Cross-platform binary support** (Windows/Linux)

### 🔒 **Security**
- **Password protection** for template content modifications
- **Middleware-based access control**
- **Secure file generation** with unique filenames
- **CSRF protection** on all forms

## Installation

### Requirements

- PHP >= 7.4
- Laravel >= 5.0
- wkhtmltopdf binary installed on your system

### Step 1: Install the Package

```bash
composer require mikailfaruqali/invoice-template
```

### Step 2: Install wkhtmltopdf

#### Windows
Download and install wkhtmltopdf from [official website](https://wkhtmltopdf.org/downloads.html)

#### Ubuntu/Debian
```bash
sudo apt-get update
sudo apt-get install wkhtmltopdf
```

#### CentOS/RHEL
```bash
sudo yum install wkhtmltopdf
```

### Step 3: Publish Assets

```bash
php artisan vendor:publish --tag=snawbar-invoice-template-assets
```

This will publish:
- Configuration file: `config/snawbar-invoice-template.php`
- Migration file: `database/migrations/2025_08_20_000001_create_invoice_templates_table.php`

### Step 4: Run Migrations

```bash
php artisan migrate
```

### Step 5: Configure wkhtmltopdf Binary Path

Edit `config/snawbar-invoice-template.php`:

```php
'binary' => [
    'windows' => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"',
    'linux' => '/usr/local/bin/wkhtmltopdf',  // Adjust path as needed
],
```

## Configuration

### Basic Configuration

```php
// config/snawbar-invoice-template.php

return [
    // Page slugs for template organization
    'page-slugs' => ['invoice', 'receipt', 'quotation', 'statement'],
    
    // Security password for content editing
    'password' => 'your-secure-password',
    
    // Route configuration
    'route-prefix' => 'invoice-templates',
    'middleware' => ['web', 'auth'],
    
    // Database table name
    'table' => 'invoice_templates',
    
    // PDF generation options
    'options' => [
        'encoding' => 'UTF-8',
        'enable-local-file-access' => true,
        'dpi' => 150,
        'image-quality' => 75,
        // ... more options
    ],
];
```

### Advanced PDF Options

```php
'options' => [
    'encoding' => 'UTF-8',
    'enable-local-file-access' => true,
    'disable-javascript' => true,
    'disable-plugins' => true,
    'print-media-type' => true,
    'no-background' => false,
    'grayscale' => false,
    'dpi' => 150,
    'image-dpi' => 150,
    'image-quality' => 75,
    'minimum-font-size' => 8,
    'zoom' => 1.0,
    'viewport-size' => '1024x768',
],
```

## Usage

### Template Management Interface

Access the template management interface at:
```
/invoice-templates
```

The interface provides:
- Create, edit, and delete templates
- Live preview of template changes
- Multi-language template management
- Page slug organization
- Margin and spacing configuration

### Basic PDF Generation

```php
use Snawbar\InvoiceTemplate\InvoiceTemplate;

// Generate PDF for default template
$pdf = InvoiceTemplate::make()
    ->renderContent('your-invoice-view')
    ->contentData(['invoice' => $invoice])
    ->inline(); // Download immediately

// Save PDF to storage
$filePath = InvoiceTemplate::make()
    ->renderContent('your-invoice-view')
    ->contentData(['invoice' => $invoice])
    ->save();
```

### Page-Specific Templates

```php
// Use specific page template
$pdf = InvoiceTemplate::make('invoice')
    ->renderContent('invoices.template')
    ->contentData(['invoice' => $invoice])
    ->inline();

// Use receipt template
$pdf = InvoiceTemplate::make('receipt')
    ->renderContent('receipts.template')
    ->contentData(['receipt' => $receipt])
    ->inline();
```

### Advanced Usage with Headers and Footers

```php
$pdf = InvoiceTemplate::make('invoice')
    ->renderContent('invoices.content')
    ->contentData(['invoice' => $invoice])
    ->renderHeader('invoices.header')
    ->headerData(['company' => $company])
    ->renderFooter('invoices.footer')
    ->footerData(['terms' => $terms])
    ->setOption('margin-top', 60)
    ->setOption('margin-bottom', 40)
    ->inline();
```

### Custom PDF Options

```php
$pdf = InvoiceTemplate::make()
    ->renderContent('your-view')
    ->setOptions([
        'page-size' => 'A4',
        'orientation' => 'portrait',
        'margin-top' => 50,
        'margin-bottom' => 30,
        'dpi' => 300
    ])
    ->inline();
```

### Working with Multiple Languages

```php
// Template selection priority:
// 1. Specific page + current locale
// 2. Specific page + wildcard locale (*)
// 3. Wildcard page (*) + current locale
// 4. Wildcard page (*) + wildcard locale (*)

// Set locale before generating
app()->setLocale('ar');

$pdf = InvoiceTemplate::make('invoice')
    ->renderContent('invoices.arabic')
    ->contentData(['invoice' => $invoice])
    ->inline();
```

### Programmatic Template Creation

```php
use Snawbar\InvoiceTemplate\InvoiceTemplate;

// Create default template
InvoiceTemplate::createDefault(['invoice'], [
    'header' => '<h1>{{ $company->name }}</h1>',
    'content' => '<div>Invoice content here</div>',
    'footer' => '<p>Thank you for your business</p>',
    'lang' => 'en',
    'paper_size' => 'A4',
    'orientation' => 'portrait'
]);
```

### Template Data Variables

Templates have access to default variables:

```blade
{{-- Available in all templates --}}
{{ $marginTop }}
{{ $marginRight }}
{{ $marginLeft }}
{{ $marginBottom }}
{{ $headerSpace }}
{{ $footerSpace }}
{{ $pageSize }}
{{ $orientation }}

{{-- Your custom data --}}
{{ $invoice->number }}
{{ $company->name }}
```

## Template Examples

### Invoice Header Template
```blade
<div style="text-align: center; padding: 20px;">
    <h1 style="margin: 0; color: #333;">{{ $company->name }}</h1>
    <p style="margin: 5px 0; color: #666;">{{ $company->address }}</p>
    <p style="margin: 5px 0; color: #666;">Phone: {{ $company->phone }} | Email: {{ $company->email }}</p>
</div>
```

### Invoice Content Template
```blade
<div style="padding: 20px;">
    <h2>Invoice #{{ $invoice->number }}</h2>
    
    <div style="margin: 20px 0;">
        <strong>Bill To:</strong><br>
        {{ $invoice->customer->name }}<br>
        {{ $invoice->customer->address }}
    </div>
    
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th style="border: 1px solid #ddd; padding: 10px; text-align: left;">Item</th>
                <th style="border: 1px solid #ddd; padding: 10px; text-align: right;">Qty</th>
                <th style="border: 1px solid #ddd; padding: 10px; text-align: right;">Price</th>
                <th style="border: 1px solid #ddd; padding: 10px; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td style="border: 1px solid #ddd; padding: 10px;">{{ $item->description }}</td>
                <td style="border: 1px solid #ddd; padding: 10px; text-align: right;">{{ $item->quantity }}</td>
                <td style="border: 1px solid #ddd; padding: 10px; text-align: right;">${{ number_format($item->price, 2) }}</td>
                <td style="border: 1px solid #ddd; padding: 10px; text-align: right;">${{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f5f5f5; font-weight: bold;">
                <td colspan="3" style="border: 1px solid #ddd; padding: 10px; text-align: right;">Total:</td>
                <td style="border: 1px solid #ddd; padding: 10px; text-align: right;">${{ number_format($invoice->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
```

### Invoice Footer Template
```blade
<div style="text-align: center; padding: 10px; font-size: 12px; color: #666;">
    <p>Thank you for your business!</p>
    <p>Questions? Contact us at {{ $company->email }} or {{ $company->phone }}</p>
    <p style="font-size: 10px;">Page {PAGENO} of {TOPAGE}</p>
</div>
```

## API Endpoints

The package provides RESTful API endpoints:

| Method | URI | Action | Description |
|--------|-----|--------|-------------|
| GET | `/invoice-templates` | index | Template management interface |
| GET | `/invoice-templates/get-data` | getData | Get all templates (JSON) |
| POST | `/invoice-templates/store` | store | Create new template |
| PUT | `/invoice-templates/update/{id}` | update | Update existing template |
| DELETE | `/invoice-templates/delete/{id}` | destroy | Delete template |

### API Usage Examples

```javascript
// Get all templates
fetch('/invoice-templates/get-data')
    .then(response => response.json())
    .then(templates => console.log(templates));

// Create new template
fetch('/invoice-templates/store', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        page: ['invoice'],
        lang: 'en',
        header: '<h1>Header</h1>',
        content: '<div>Content</div>',
        footer: '<p>Footer</p>',
        paper_size: 'A4',
        orientation: 'portrait',
        password: 'your-password'
    })
});
```

## Database Schema

The package creates a `invoice_templates` table with the following structure:

```sql
CREATE TABLE `invoice_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `page` json NOT NULL,                    -- Page slugs (JSON array)
  `header` longtext,                       -- Header template content
  `content` longtext,                      -- Main content template
  `footer` longtext,                       -- Footer template content
  `logo` text,                            -- Logo path/URL
  `margin_top` double DEFAULT 0,          -- Top margin (mm)
  `margin_bottom` double DEFAULT 0,       -- Bottom margin (mm)
  `margin_left` double DEFAULT 0,         -- Left margin (mm)
  `margin_right` double DEFAULT 0,        -- Right margin (mm)
  `header_space` double DEFAULT 0,        -- Header spacing (mm)
  `footer_space` double DEFAULT 0,        -- Footer spacing (mm)
  `orientation` enum('portrait','landscape') DEFAULT 'portrait',
  `paper_size` enum('A4','A5','A3','letter','legal') DEFAULT 'A4',
  `lang` varchar(255) DEFAULT 'en',       -- Language code
  `disabled_smart_shrinking` tinyint(1) DEFAULT 0,
  `disable_header` tinyint(1) DEFAULT 0,  -- Disable header rendering
  `disable_footer` tinyint(1) DEFAULT 0,  -- Disable footer rendering
  `is_active` tinyint(1) DEFAULT 1,       -- Template active status
  PRIMARY KEY (`id`)
);
```

## Troubleshooting

### Common Issues

#### 1. wkhtmltopdf not found
```
Error: The exit code was not zero: 127
```
**Solution:** Ensure wkhtmltopdf is installed and the binary path is correctly configured.

#### 2. Permission denied when saving PDFs
```
Error: Permission denied
```
**Solution:** Ensure the `public/files` directory is writable:
```bash
chmod -R 755 public/files
```

#### 3. Template not found
```
Error: No query results for model
```
**Solution:** Create a default template or ensure templates exist for your page slugs.

#### 4. CSRF token mismatch
```
Error: 419 Page Expired
```
**Solution:** Ensure CSRF token is included in AJAX requests:
```javascript
axios.defaults.headers.common['X-CSRF-TOKEN'] = 
    document.querySelector('meta[name="csrf-token"]').getAttribute('content');
```

### Performance Optimization

1. **Use template caching** for frequently used templates
2. **Optimize images** before including in templates
3. **Minimize CSS and HTML** in templates
4. **Use appropriate DPI settings** based on your needs

## Security Considerations

1. **Always validate input** when creating templates programmatically
2. **Use password protection** for content editing in production
3. **Sanitize user input** in template content
4. **Restrict access** using appropriate middleware
5. **Validate file paths** when working with logos and assets

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Check code style: `composer pint`

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

If you discover any security-related issues, please email alanfaruq85@gmail.com instead of using the issue tracker.

## Credits

- [Mikail Faruq Ali](https://github.com/mikailfaruqali)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

If you find this package helpful, please consider:
- ⭐ Starring the repository
- 🐛 Reporting bugs
- 💡 Suggesting new features
- 📖 Improving documentation

---

**Built with ❤️ for the Laravel community**