<?php

return [
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
    | Font Family Configuration
    |--------------------------------------------------------------------------
    | Default font family for PDF generation
    |
    */

    'font' => '',

    /*
    |--------------------------------------------------------------------------
    | Font Directory Path
    |--------------------------------------------------------------------------
    | Directory path containing custom font files for PDF generation
    |
    */

    'font-dir' => '',

    /*
    |--------------------------------------------------------------------------
    | Locale Direction Key for Session
    |--------------------------------------------------------------------------
    | Session key used for text direction (LTR/RTL support) based on locale
    |
    */

    'locale-direction-key' => 'direction',
];
