<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Page Slugs
    |--------------------------------------------------------------------------
    |
    | Define the canonical slugs used to identify pages/sections for templates.
    | These values seed the “Page” tags input to keep entries consistent
    | (e.g., invoice, receipt, summary).
    |
    | Type: array<string>
    | Rules: lowercase, kebab-case, unique, trimmed (no spaces or duplicates)
    | Behavior:
    |   - Empty array => no preset suggestions; editors can add any slug.
    |   - Non-empty   => values appear as suggestions; editors can still add new.
    |
    | Example:
    |   'page-slugs' => ['invoice', 'receipt', 'summary'],
    |
    */

    'page-slugs' => [],

    /*
    |--------------------------------------------------------------------------
    | Security Password
    |--------------------------------------------------------------------------
    |
    | WARNING: Template content can execute PHP/HTML/JavaScript code.
    | This password protects against unauthorized template modifications
    | that could compromise system security.
    |
    |
    */

    'password' => '',

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the routing configuration for the invoice templates
    | package. The route prefix will be used as the base URL for all template
    | management routes (e.g., /invoice-templates).
    |
    | Example routes that will be generated:
    | - GET /invoice-templates (template listing page)
    | - POST /invoice-templates/store (create new template)
    | - PUT /invoice-templates/update/{id} (update existing template)
    | - DELETE /invoice-templates/delete/{id} (delete template)
    | - GET /invoice-templates/get-data (API endpoint for template data)
    |
    */

    'route-prefix' => 'invoice-templates',

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Define the middleware that should be applied to all invoice template routes.
    | The 'web' middleware group provides session state, CSRF protection, and
    | cookie encryption. The 'auth' middleware ensures only authenticated users
    | can access the template management interface.
    |
    | Common middleware options:
    | - 'web': Provides web-based features (sessions, CSRF, etc.)
    | - 'auth': Requires user authentication
    | - 'role:admin': Requires specific role (if using role-based access)
    | - 'permission:manage-templates': Requires specific permission
    |
    | You can add additional middleware as needed for your application's
    | security requirements.
    |
    */

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Database Table Configuration
    |--------------------------------------------------------------------------
    |
    | This section allows you to customize the database table name used for
    | storing invoice templates. You can change this if you need to use a
    | different table name or if you have naming conventions in your project.
    |
    */

    'table' => 'invoice_templates',

    /*
    |--------------------------------------------------------------------------
    | PDF Engine Binary Path
    |--------------------------------------------------------------------------
    | Path to the invoice-pdf engine. Leave it NULL to use the engine installed
    | by "php artisan invoice-template:install":
    |   storage/invoice-template/invoice-pdf (invoice-pdf.exe on Windows)
    |
    */

    'binary' => NULL,

    /*
    |--------------------------------------------------------------------------
    | Chrome Executable Path
    |--------------------------------------------------------------------------
    | The engine renders with headless Chrome, Chromium, Edge or Brave and
    | detects an installed browser automatically. Set a path to override it.
    |
    | Example:
    |   'chrome' => '/usr/bin/chromium',
    |
    */

    'chrome' => NULL,

    /*
    |--------------------------------------------------------------------------
    | PDF Generation Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time (in seconds) to wait for the engine to generate a PDF.
    | This prevents the process from hanging indefinitely on complex documents
    | or when server resources are limited.
    |
    | Default: 300 seconds (5 minutes)
    | Recommended range: 60-600 seconds depending on document complexity
    | Set to null to disable timeout (not recommended for production)
    |
    */

    'timeout' => 300,

    /*
    |--------------------------------------------------------------------------
    | Font Family Configuration
    |--------------------------------------------------------------------------
    | Default font family name or font file name for PDF generation.
    |
    | Supported formats:
    | - String (font name): 'Cairo', 'Roboto', 'Arial', or file 'vazir.ttf'
    | - Array per language:
    |     'font' => [
    |         'ar' => 'Cairo',
    |         'en' => 'Roboto',
    |         'ku' => 'Vazirmatn.ttf',
    |         'default' => 'Arial',
    |     ]
    |
    */

    'font' => '',

    /*
    |--------------------------------------------------------------------------
    | Font Directory Path
    |--------------------------------------------------------------------------
    | Directory path containing custom font files for PDF generation (if using font files).
    |
    | Supported formats:
    | - String: resource_path('fonts')
    | - Array per language:
    |     'font-dir' => [
    |         'ar' => resource_path('fonts/ar'),
    |         'en' => resource_path('fonts/en'),
    |         'default' => resource_path('fonts'),
    |     ]
    |
    */

    'font-dir' => '',

    /*
    |--------------------------------------------------------------------------
    | Font Family Name / Stack
    |--------------------------------------------------------------------------
    | The CSS font-family value declared in @font-face and applied to body.
    | Supports a single name or a full CSS font stack string.
    | The first name in the stack is used as the @font-face family name.
    |
    | Supported formats:
    | - Single name:  'NRT'
    | - Full stack:   "'NRT', 'Noto Sans Arabic', Tahoma, Arial, sans-serif"
    | - Array per language:
    |     'font-family' => [
    |         'ckb'     => "'NRT', 'Noto Sans Arabic', Tahoma, Arial, sans-serif",
    |         'en'      => "'Outfit', 'Segoe UI', system-ui, sans-serif",
    |         'default' => "'NRT', 'Noto Sans Arabic', Tahoma, Arial, sans-serif",
    |     ]
    |
    */
    'font-family' => '',

    /*
    |--------------------------------------------------------------------------
    | Locale Direction Key / Resolver
    |--------------------------------------------------------------------------
    | Session key used for text direction (LTR/RTL support) based on locale,
    | or a Closure/callable returning 'ltr' or 'rtl' (receives current locale).
    |
    | Supported formats:
    | - String (session key): 'direction'
    | - Closure: fn ($locale) => in_array($locale, ['ar', 'ckb', 'fa', 'he', 'ur']) ? 'rtl' : 'ltr'
    |
    */
    'locale-direction-key' => 'direction',

    /*
    |--------------------------------------------------------------------------
    | PDF Viewer Favicon
    |--------------------------------------------------------------------------
    | Favicon shown in the browser tab of the PDF viewer page.
    | Local files are embedded as base64, anything else is used as a URL.
    |
    | Supported formats:
    | - File path: public_path('assets/images/favicon.png')
    | - URL:       'https://example.com/favicon.png'
    | - Empty:     no favicon
    |
    */
    'favicon' => '',

    /*
    |--------------------------------------------------------------------------
    | PDF Viewer Theme
    |--------------------------------------------------------------------------
    | Color mode of the PDF viewer page: 'light' or 'dark'.
    | Can also be a callable that receives the authenticated user (or NULL)
    | and returns 'light', 'dark' or a boolean (TRUE = dark).
    |
    | Supported formats:
    | - String:   'light' or 'dark'
    | - Boolean:  TRUE (dark) or FALSE (light)
    | - Callable: [App\Support\Appearance::class, 'pdfViewerTheme']
    | - Closure:  fn ($user) => $user?->is_dark ? 'dark' : 'light'
    |
    | Prefer the [Class::class, 'method'] form so `php artisan config:cache`
    | keeps working; closures cannot be cached.
    |
    */
    'theme' => 'dark',
];
