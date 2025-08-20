# Laravel Log Viewer

A simple and clean log viewer for Laravel applications. Easily view, manage, and delete your log files through a straightforward web interface. This package is designed to be lightweight and easy to integrate into any Laravel project.

![Screenshot of Laravel Log Viewer](https://user-images.githubusercontent.com/your-id/your-image.png)
*(You can add a screenshot here after you have it running)*

## Features

- **Simple Interface**: Clean, single-page view for your logs.
- **File Selection**: Easily switch between different log files.
- **File Deletion**: Clean up old log files directly from the UI.
- **Easy Installation**: Get up and running in minutes.
- **Configurable**: Publish the config file to customize the route and middleware.
- **Zero Dependencies**: No external CSS or JS frameworks required.

## Installation

You can install the package via Composer:

```bash
composer require mikailfaruqali/log-viewer
```

## Configuration

The package is designed to work out-of-the-box, but you can publish the configuration file for more control.

Publish the configuration file using the following command:

```bash
php artisan vendor:publish --provider="Mikailfaruqali\LogViewer\LogViewerServiceProvider" --tag="config"
```

This will create a `config/log-viewer.php` file in your project where you can customize the package settings:

```php
// config/log-viewer.php

return [
    // The URI path where the log viewer will be accessible.
    'route-path' => 'logs',

    // The middleware applied to the log viewer routes.
    // By default, it's protected by 'web' and 'auth' middleware.
    'middleware' => ['web', 'auth'],
];
```

You can also publish the views to customize the UI:

```bash
php artisan vendor:publish --provider="Mikailfaruqali\LogViewer\LogViewerServiceProvider" --tag="views"
```

## Usage

Once installed, navigate to the configured route in your browser. By default, this is:

`https://your-app.com/logs`

You must be authenticated to access this page, as defined by the default `auth` middleware in the configuration. You can change this to suit your application's needs (e.g., by creating a specific `can:view-logs` middleware).

## Security

By default, the log viewer is only accessible to authenticated users. If your application has roles and permissions, it is **highly recommended** to create a custom authorization policy.

You can do this by creating a custom middleware and adding it to the `middleware` array in the `config/log-viewer.php` file.

**Example: Admin-only middleware**

1.  Create the middleware: `php artisan make:middleware AbortIfNotAdmin`
2.  Implement the logic in the middleware's `handle` method.
3.  Update your `config/log-viewer.php`:
    ```php
    'middleware' => ['web', 'auth', 'admin'],
    ```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.