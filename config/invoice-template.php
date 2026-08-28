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
    | wkhtmltopdf Binary Path
    |--------------------------------------------------------------------------
    | This is the path to the wkhtmltopdf binary executable on your system.
    | Choose the appropriate path based on your operating system.
    | Windows: Use the 'windows' path
    | Linux/Unix: Use the 'linux' path
    |
    */

    'binary' => [
        'windows' => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"',
        'linux' => '/usr/local/bin/wkhtmltopdf',
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Generation Options
    |--------------------------------------------------------------------------
    | These are the options passed to wkhtmltopdf for generating PDFs.
    | Configure these settings to control PDF quality and performance.
    |
    */

    'options' => [
        'encoding' => 'UTF-8',
        'enable-local-file-access' => TRUE,
        'disable-javascript' => TRUE,
        'disable-plugins' => TRUE,
        'disable-smart-shrinking' => TRUE,
        'no-pdf-compression' => FALSE,
        'disable-forms' => TRUE,
        'disable-internal-links' => TRUE,
        'disable-external-links' => TRUE,
        'print-media-type' => TRUE,
        'no-background' => FALSE,
        'grayscale' => FALSE,
        'load-error-handling' => 'ignore',
        'load-media-error-handling' => 'ignore',
        'javascript-delay' => 0,
        'window-status' => '',
        'minimum-font-size' => 8,
        'zoom' => 1.0,
        'viewport-size' => '1024x768',
        'lowquality' => FALSE,
        'dpi' => 150,
        'image-dpi' => 150,
        'image-quality' => 75,
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Generation Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time (in seconds) to wait for wkhtmltopdf to generate a PDF.
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
    | Font Family Name
    |--------------------------------------------------------------------------
    | The CSS font-family name to declare in @font-face and apply to body.
    | Use this when the font file name differs from the intended family name.
    | e.g. file is 'rtl-font.ttf' but family name should be 'NRT'.
    |
    | Supported formats:
    | - String: 'NRT'
    | - Array per language:
    |     'font-family' => [
    |         'ckb'     => 'NRT',
    |         'en'      => 'Outfit',
    |         'default' => 'NRT',
    |     ]
    |
    */

    'font-family' => '',

    /*
    |--------------------------------------------------------------------------
    | Locale Direction Key for Session
    |--------------------------------------------------------------------------
    | Session key used for text direction (LTR/RTL support) based on locale
    |
    */

    'locale-direction-key' => 'direction',
];
