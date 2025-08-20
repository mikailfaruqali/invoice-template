<?php

return [
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
        'lowquality' => TRUE,
        'dpi' => 72,
        'image-dpi' => 72,
        'image-quality' => 50,
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
