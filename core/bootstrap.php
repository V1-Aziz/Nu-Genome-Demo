<?php
/**
 * Application bootstrap: autoloading, config, error handling.
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// PSR-4 style autoloader for the two application namespaces.
spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Core\\' => BASE_PATH . '/core/',
        'App\\'  => BASE_PATH . '/app/',
    ];

    foreach ($prefixes as $prefix => $dir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file     = $dir . str_replace('\\', '/', $relative) . '.php';

        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require __DIR__ . '/helpers.php';

Core\App::boot(require BASE_PATH . '/config/config.php');

// Render friendly error pages instead of leaking stack traces to visitors.
set_exception_handler(static function (Throwable $e): void {
    Core\App::log('Uncaught ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

    if (!headers_sent()) {
        http_response_code(500);
    }

    $message = Core\App::config('app.debug')
        ? $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')'
        : '';

    try {
        (new App\Controllers\ErrorController())->serverError($message);
    } catch (Throwable) {
        echo 'A server error occurred.';
    }
});
