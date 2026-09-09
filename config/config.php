<?php
/**
 * Application configuration.
 *
 * Every value can be overridden by an environment variable of the same name,
 * which keeps credentials out of the file for anything beyond local XAMPP.
 */

$env = static fn (string $key, mixed $default) => ($v = getenv($key)) === false ? $default : $v;

return [
    'app' => [
        'name'     => 'GenomePlatform NU',
        'base_url' => $env('APP_BASE_URL', ''),   // sub-directory prefix; '' at the domain root
        'debug'    => (bool) $env('APP_DEBUG', true),
        'log_file' => dirname(__DIR__) . '/storage/logs/app.log',
    ],

    'db' => [
        'host'    => $env('DB_HOST', '127.0.0.1'),
        'port'    => (int) $env('DB_PORT', 3306),
        'name'    => $env('DB_NAME', 'genome_platform'),
        'user'    => $env('DB_USER', 'root'),
        'pass'    => $env('DB_PASS', ''),
        'charset' => 'utf8mb4',
        // Set DB_SOCKET to a mysql.sock path to connect over a unix socket
        // instead of TCP. XAMPP's default socket is at
        // /Applications/XAMPP/xamppfiles/var/mysql/mysql.sock
        'socket'  => $env('DB_SOCKET', null) ?: null,
    ],

    'session' => [
        'name'     => 'genomeplatform_session',
        'lifetime' => (int) $env('SESSION_LIFETIME', 7200),
    ],
];
