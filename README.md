# Laravel Invoice Template Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mikailfaruqali/invoice-template.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/invoice-template)
[![Total Downloads](https://img.shields.io/packagist/dt/mikailfaruqali/invoice-template.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/invoice-template)
[![License](https://img.shields.io/packagist/l/mikailfaruqali/invoice-template.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/invoice-template)

A powerful Laravel package for generating professional PDF invoices with customizable templates. Features advanced header/footer support, multi-language capabilities, and a comprehensive template management system powered by a fast Go engine that renders with headless Chrome.

## Features

### 🎨 **Template Management**
- **Database-driven templates** with full CRUD operations
- **Multi-language support** with locale-based template selection
- **Page-specific templates** using customizable slugs (invoice, receipt, summary, etc.)
- **Visual template editor** with live preview capabilities
- **Password-protected content editing** for security

### 📄 **PDF Generation**
- **Professional PDF output** using a bundled Go engine and headless Chrome
- **Watermarks** stamped over every page with adjustable opacity
- **Page numbers** with `{PAGENO}` and `{TOPAGE}` in headers and footers
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
- **Lossless images** and modern CSS (flexbox, grid, web fonts)
- **Custom font support** with font directory configuration
- **Flexible middleware** for route protection
- **Configurable table names** and route prefixes
- **Cross-platform engine** (Windows, Linux, macOS on amd64 and arm64)

### 🔒 **Security**
- **Password protection** for template content modifications
- **Middleware-based access control**
- **Secure file generation** with unique filenames
- **CSRF protection** on all forms

## Installation

### Requirements

- PHP >= 7.4
- Laravel >= 5.0
- Google Chrome, Chromium, Microsoft Edge or Brave installed on the server

### Step 1: Install the Package

```bash
composer require mikailfaruqali/invoice-template
```

### Step 2: Install the PDF Engine

Download the engine for your operating system and architecture:

```bash
php artisan invoice-template:install
```

It is saved to `storage/invoice-template/invoice-pdf` (`invoice-pdf.exe` on Windows). Use `--force` to reinstall it, or `--tag=` to install a specific release.

Without internet access, install it offline instead:

```bash
# from a release archive or binary copied to the server
php artisan invoice-template:install --path=/path/to/invoice-pdf_linux_amd64.tar.gz

# or build it from the package source (requires Go 1.23+)
php artisan invoice-template:install --build
```

Then verify the engine and the browser it will use:

```bash
php artisan invoice-template:check
```

On a Linux server without a browser, install Chromium first, for example:

```bash
sudo apt-get install chromium
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

### Step 5: Configure the Engine (optional)

The engine and browser are detected automatically. Override them in `config/snawbar-invoice-template.php` when needed:

```php
'binary' => NULL,                 // NULL uses storage/invoice-template/invoice-pdf
'chrome' => '/usr/bin/chromium',  // NULL auto-detects Chrome, Chromium, Edge or Brave
'timeout' => 300,
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
    
    // PDF engine
    'binary' => NULL,
    'chrome' => NULL,
    'timeout' => 300,
];
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
        'zoom' => 0.9,
    ])
    ->inline();
```

Supported options: `page-size`, `page-width`, `page-height`, `orientation`, `margin-top`, `margin-bottom`, `margin-left`, `margin-right`, `header-spacing`, `footer-spacing`, `disable-smart-shrinking`, `zoom`, `watermark-opacity`, `header-first-page-only` and `footer-last-page-only`. The template's own settings take priority over these.

### Shared CSS Files

Share one stylesheet between the content, header, footer and watermark instead of repeating `<style>` and `@font-face` in every template:

```php
$pdf = InvoiceTemplate::make('invoice')
    ->renderContent('invoices.content')
    ->contentData(['invoice' => $invoice])
    ->cssFile(public_path('assets/css/print/invoice.css'))
    ->inline();

// several files at once
InvoiceTemplate::make()->cssFiles([
    public_path('assets/css/print/base.css'),
    public_path('assets/css/print/invoice.css'),
]);
```

The files are added at the top of each part's `<head>`, so a template's own styles still override them. Relative `url()` paths inside a CSS file (fonts, images) are resolved from the file's own folder.

### CSS Variables

Set CSS custom properties at runtime, for example a per-company brand color, and use them with `var()` in templates or shared CSS:

```php
InvoiceTemplate::make('invoice')
    ->renderContent('invoices.content')
    ->cssVariables([
        'brand' => $company->color,
        '--table-border' => '#dbdfea',
    ])
    ->cssVariable('font-size', '13px')
    ->inline();
```

```blade
<h1 style="color: var(--brand)">{{ $company->name }}</h1>
```

Names work with or without the leading `--`. The values apply to the content, header, footer and watermark, and override any `:root` defaults the templates define. Passing `null` or an empty value removes a variable.

### Watermarks

Each template has a **Watermark Content** field and an **Opacity** (0 – 1) in the template editor. The watermark is Blade HTML rendered with the content data and stamped over every page:

```blade
<div style="display:flex;align-items:center;justify-content:center;height:100vh">
    <div style="font-size:110px;color:#c00;transform:rotate(-35deg)">{{ $invoice->status }}</div>
</div>
```

Leave it empty for no watermark, or tick **Disable Watermark** (`disable_watermark` column) to turn it off while keeping its content.

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
  `watermark` longtext,                    -- Watermark template content
  `watermark_opacity` double DEFAULT 0.3,  -- Watermark opacity (0 - 1)
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
  `disable_watermark` tinyint(1) DEFAULT 0, -- Disable watermark rendering
  `header_first_page_only` tinyint(1) DEFAULT 0, -- Draw the header on the first page only
  `footer_last_page_only` tinyint(1) DEFAULT 0,  -- Draw the footer on the last page only
  `is_active` tinyint(1) DEFAULT 1,       -- Template active status
  PRIMARY KEY (`id`)
);
```

## Troubleshooting

### Common Issues

#### 1. PDF engine not found
```
PDF engine not found at [.../storage/invoice-template/invoice-pdf]
```
**Solution:** Run `php artisan invoice-template:install`, then `php artisan invoice-template:check`.

If the check cannot find a browser, install Chrome or Chromium, or set its path in the `chrome` config key.

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
4. **Reference local images and fonts by absolute path** (for example `public_path(...)`); the engine serves them to Chrome without a web request

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