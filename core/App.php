<?php
namespace Core;

/**
 * Application container: holds config, boots the session, wires the error handler.
 */
class App
{
    private static array $config = [];
    private static bool $booted = false;

    public static function boot(array $config): void
    {
        if (self::$booted) {
            return;
        }

        self::$config = $config;
        self::$booted = true;

        self::configureErrors();
        self::startSession();
    }

    /**
     * Read a config value by dotted path: App::config('db.host').
     */
    public static function config(string $key, mixed $default = null): mixed
    {
        $value = self::$config;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public static function log(string $message): void
    {
        $file = self::config('app.log_file');
        if (!$file) {
            return;
        }

        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        @error_log(
            sprintf('[%s] %s%s', date('Y-m-d H:i:s'), $message, PHP_EOL),
            3,
            $file
        );
    }

    /** Absolute URL for an application path. */
    public static function url(string $path = '/'): string
    {
        $base = rtrim((string) self::config('app.base_url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }

    private static function configureErrors(): void
    {
        $debug = (bool) self::config('app.debug', false);

        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', (string) self::config('app.log_file'));
    }

    private static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        session_name((string) self::config('session.name', 'PHPSESSID'));
        session_set_cookie_params([
            'lifetime' => (int) self::config('session.lifetime', 7200),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']),
        ]);
        session_start();
    }
}
