

# Laravel Invoice Template Generator

**Laravel Invoice Template Generator** is a powerful, flexible package by [**Snawbar**](https://snawbar.com) for generating PDF invoices in Laravel using fully customizable templates. It features a modern web UI, dynamic placeholders, advanced PDF options, and seamless integration with Snappy PDF and Twig.

<p align="center">
    <a href="https://snawbar.com">
        <img src="https://snawbar.com/images/backend/logo/1733997832.png" alt="Snawbar Logo" height="80">
    </a>
</p>

---

## About Snawbar

[**Snawbar**](https://snawbar.com) is a technology company specializing in ERP systems for large and small businesses. We deliver robust, scalable, and user-friendly solutions to help organizations streamline their operations, manage resources, and grow efficiently.

---

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Requirements](#requirements)
- [Configuration](#configuration)
- [Database Structure](#database-structure)
- [Web UI](#web-ui)
- [API Endpoints](#api-endpoints)
- [Usage](#usage)
- [Template Placeholders](#template-placeholders)
- [Twig Templating](#twig-templating)
- [PDF Generation Options](#pdf-generation-options)
- [Security](#security)
- [Extending the Package](#extending-the-package)
- [Full Example Workflow](#full-example-workflow)
- [Troubleshooting](#troubleshooting)
- [Credits](#credits)
- [License](#license)


## Features

- **Database-Driven Templates**: Store and manage invoice templates (header, content, footer) in your database, with support for multiple languages and custom page settings.
- **Modern Web UI**: Intuitive, responsive interface for creating, editing, and previewing templates. Includes search, filter, and keyboard shortcuts.
- **Dynamic Placeholders**: Use variables like `{company_name}`, `{current_date}`, `{current_user}`, and more. All placeholders are replaced at render time.
- **Logo Support**: Easily add your company logo to invoices by specifying a logo path in your template data.
- **Multi-Language**: Create templates for any language or locale.
- **Custom Page Settings**: Configure paper size (A4, A5, A3), orientation, width, height, and all margins.
- **Header/Footer Sections**: Separate HTML for header and footer, with full control over spacing and content.
- **Role-Based Access**: Secure all management routes with configurable middleware (default: `web`, `auth`).
- **RESTful API**: Full CRUD endpoints for programmatic template management.
- **Twig Templating**: Use Twig for advanced logic, loops, filters, and custom extensions in your templates.
- **Snappy PDF Integration**: High-quality PDF rendering with extensive configuration for DPI, fonts, compression, and more.
- **File Saving/Streaming**: Save PDFs to disk or stream them inline to the browser.
- **Extensible**: Add custom Twig extensions, global variables, and more.

---


## Installation

Install via Composer and publish the configuration and migration files:

```bash
composer require mikailfaruqali/invoice-template
php artisan vendor:publish --provider="Snawbar\\InvoiceTemplate\\InvoiceTemplateServiceProvider"
php artisan migrate
```

## Requirements

- PHP 7.4 or higher
- Laravel 8.x or higher
- [wkhtmltopdf](https://wkhtmltopdf.org/downloads.html) installed and available in your system path (set the binary path in config if needed)

## Configuration

After publishing, edit `config/invoice-template.php` to customize the following options:

- **Route Prefix**: Change the base URL for template management (default: `/invoice-templates`).
- **Middleware**: Control access to management routes (default: `['web', 'auth']`).
- **Database Table**: Table name for storing templates (default: `invoice_templates`).
- **wkhtmltopdf Binary Path**: Set the path for Windows/Linux as needed.
- **PDF Options**: Configure DPI, font, grayscale, compression, and more.
- **Font and Font Directory**: Use custom fonts in generated PDFs.
- **Locale Direction**: RTL/LTR support for multi-language invoices.

## Database Structure

The migration creates a table with the following fields:

- `id`: Primary key
- `page`: Template slug/identifier
- `header`: HTML for the PDF header
- `content`: Main HTML content for the invoice
- `footer`: HTML for the PDF footer
- `logo`: Optional logo path
- `watermark`: Optional watermark path
- `margin_top`, `margin_bottom`, `margin_left`, `margin_right`: Margins in mm
- `header_space`, `footer_space`: Spacing for header/footer in mm
- `orientation`: `portrait` or `landscape`
- `paper_size`: `A4`, `A5`, or `A3`
- `width`, `height`: Custom page dimensions in mm
- `lang`: Language code (e.g., `en`, `fr`)
- `is_active`: Boolean flag for active template

## Web UI

Access the web interface at `/invoice-templates` (or your configured route prefix). The UI provides:

- **Template Listing**: View all templates with search and filter options.
- **Create/Edit/Delete**: Add new templates, edit existing ones, or delete as needed.
- **Live Preview**: Instantly preview header, content, and footer HTML.
- **Page Configuration**: Set paper size, orientation, width, height, margins, and spacing visually.
- **Keyboard Shortcuts**: Ctrl+S to save, Esc to close modals.
- **Validation**: Client-side and server-side validation for all fields.

## API Endpoints

All routes are prefixed (default: `/invoice-templates`) and protected by middleware. Endpoints include:

- `GET /invoice-templates` — Web UI for managing templates
- `GET /invoice-templates/get-data` — List all templates (JSON)
- `POST /invoice-templates/store` — Create a new template
- `PUT /invoice-templates/update/{id}` — Update an existing template
- `DELETE /invoice-templates/delete/{id}` — Delete a template


## Usage

### Creating a Template Programmatically

```php
use Snawbar\InvoiceTemplate\InvoiceTemplate;

InvoiceTemplate::create($request); // expects: page, lang, header, content, footer, margins, etc.
```

### Generating a PDF

```php
use Snawbar\InvoiceTemplate\InvoiceTemplate;

// Show in browser (inline)
InvoiceTemplate::template('my-invoice')
    ->withData([
        'company_name' => 'Snawbar',
        'logo' => 'https://snawbar.com/images/backend/logo/1733997832.png',
        'invoice_number' => 'INV-2025-001',
        'amount' => 123.45,
        // ...
    ])
    ->inline();

// Save to file
$path = InvoiceTemplate::template('my-invoice')
    ->withData([
        'company_name' => 'Snawbar',
        'logo' => 'https://snawbar.com/images/backend/logo/1733997832.png',
        'invoice_number' => 'INV-2025-001',
        'amount' => 123.45,
        // ...
    ])
    ->save();
```

### Using the Web UI

1. Visit `/invoice-templates` in your browser.
2. Click "New Template" to create a template. Fill in all required fields, including header, content, and footer HTML.
3. Use the live preview buttons to see how each section will render.
4. Save the template. It will be available for PDF generation and API use.
5. Edit or delete templates as needed.

---

## Example Template Sections

Below are sample header, content, and footer HTML blocks for your invoice templates. You can use Twig and placeholders as needed.

### Header Example

```html
<div style="display: flex; align-items: center; justify-content: space-between;">
    <img src="{{ logo|default('https://snawbar.com/images/backend/logo/1733997832.png') }}" alt="Snawbar Logo" style="height: 60px;">
    <div style="text-align: right;">
        <h2 style="margin: 0;">{{ company_name|default('Snawbar') }}</h2>
        <p style="margin: 0; font-size: 12px; color: #888;">ERP Solutions for Every Business</p>
    </div>
</div>
<hr>
```

### Content Example

```html
<h1 style="margin-bottom: 0;">Invoice</h1>
<p style="margin-top: 0;">Invoice #: {{ invoice_number }}<br>Date: {{ current_date }}</p>

<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <thead>
        <tr>
            <th style="border-bottom: 1px solid #ccc; text-align: left;">Description</th>
            <th style="border-bottom: 1px solid #ccc; text-align: right;">Amount</th>
        </tr>
    </thead>
    <tbody>
        {% for item in items %}
        <tr>
            <td style="padding: 8px 0;">{{ item.description }}</td>
            <td style="padding: 8px 0; text-align: right;">{{ item.amount|number_format(2) }}</td>
        </tr>
        {% endfor %}
    </tbody>
</table>

<h3 style="text-align: right; margin-top: 30px;">Total: {{ amount|number_format(2) }}</h3>
```

### Footer Example

```html
<hr>
<p style="font-size: 12px; text-align: center; color: #888;">
    Thank you for your business!<br>
    &copy; {{ current_date|date('Y') }} {{ company_name }}. All rights reserved.
</p>
```

---


## Template Placeholders

You can use any data passed via `withData()` as a Twig variable in your template. Built-in placeholders include:

- `company_name`: Your company name (e.g., Snawbar)
- `logo`: Path to your company logo (e.g., `https://snawbar.com/images/backend/logo/1733997832.png`)
- `current_date`: The current date (e.g., 2025-08-20)
- `current_user`: The current user (e.g., mikailfaruqali)
- `current_datetime`: The current date and time (e.g., 2025-08-20 13:30:17)


## Twig Templating

All templates are rendered using Twig, allowing you to use advanced logic, loops, filters, and custom extensions. Example:

```twig
<h1>Invoice for {{ company_name }}</h1>
<ul>
{% for item in items %}
    <li>{{ item.name }}: {{ item.price|number_format(2) }}</li>
{% endfor %}
</ul>
<p>Total: {{ amount|number_format(2) }}</p>
```

You can add custom Twig extensions via `InvoiceTemplate::setExtension($extension)` and register global variables via the Laravel service container as `snawbar-invoice-template`.

## PDF Generation Options

Configure all PDF generation options in `config/invoice-template.php`:

- **DPI**: Set the dots per inch for high-quality output.
- **Image Quality**: Control image compression and quality.
- **Grayscale**: Enable grayscale output for black-and-white PDFs.
- **Compression**: Enable or disable PDF compression.
- **Fonts**: Use custom fonts by specifying the font and font directory.
- **JavaScript/Plugins**: Enable or disable JavaScript and plugins in the PDF renderer.
- **Viewport/Zoom**: Set viewport size and zoom for precise rendering.
- **Minimum Font Size**: Prevent text from being too small.

## Security

All management and API routes are protected by the `web` and `auth` middleware by default. You can add additional middleware (such as role or permission checks) in the configuration file to restrict access as needed.

## Extending the Package

- **Twig Extensions**: Add custom Twig extensions for advanced template logic using `InvoiceTemplate::setExtension($extension)`.
- **Global Variables**: Register global variables for all templates via the Laravel service container as `snawbar-invoice-template`.
- **Custom Fonts**: Place font files in your specified font directory and set the font name in the config.

## Full Example Workflow

1. **Install and configure the package** as described above.
2. **Create a template** via the web UI or API, specifying all required fields and HTML content.
3. **Preview and test** the template using the web UI's live preview feature.
4. **Generate a PDF** in your application code:

```php
InvoiceTemplate::template('invoice-template-en')
    ->withData([
        'company_name' => 'Acme Inc.',
        'invoice_number' => 'INV-2025-001',
        'amount' => 123.45,
        // ...
    ])
    ->inline();
```
5. **Save or stream** the PDF as needed, or use the file path for further processing.

## Troubleshooting

- If wkhtmltopdf is not found, set the correct binary path in the config for your OS.
- For PDF output issues, adjust margins, DPI, or HTML structure. Use the web UI preview to debug.
- Ensure storage and public directories are writable for file saving.

## Credits

- [barryvdh/laravel-snappy](https://github.com/barryvdh/laravel-snappy)
- [twig/twig](https://twig.symfony.com/)
- [Tailwind CSS](https://tailwindcss.com/)


## License

MIT