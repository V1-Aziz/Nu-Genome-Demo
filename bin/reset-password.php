#!/usr/bin/env php
<?php
/**
 * Reset a user's password from the command line.
 *
 * Usage:
 *   php bin/reset-password.php <email> <new-password>
 *   php bin/reset-password.php --list
 *
 * Uses the application's own config and connection, so it always talks to the
 * same database the site does.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("This script is CLI only.\n");
}

require __DIR__ . '/../core/bootstrap.php';

use Core\Auth;
use Core\Database;

$args = array_slice($argv, 1);

if ($args === [] || $args[0] === '--help' || $args[0] === '-h') {
    fwrite(STDERR, "Usage: php bin/reset-password.php <email> <new-password>\n");
    fwrite(STDERR, "       php bin/reset-password.php --list\n");
    exit(1);
}

if ($args[0] === '--list') {
    printf("%-4s %-20s %-34s %s\n", 'ID', 'USERNAME', 'EMAIL', 'ROLE');
    foreach (Database::select('SELECT id, username, email, role FROM users ORDER BY id') as $u) {
        printf("%-4s %-20s %-34s %s\n", $u['id'], $u['username'], $u['email'], $u['role']);
    }
    exit(0);
}

if (count($args) < 2) {
    fwrite(STDERR, "Both an email and a new password are required.\n");
    exit(1);
}

[$email, $password] = $args;

if (strlen($password) < 8) {
    fwrite(STDERR, "Password must be at least 8 characters (the registration form enforces the same).\n");
    exit(1);
}

$user = Database::selectOne('SELECT id, username, email FROM users WHERE email = ?', [$email]);

if ($user === null) {
    fwrite(STDERR, "No user with email '{$email}'. Run with --list to see the accounts.\n");
    exit(1);
}

$hash = Auth::hash($password);

// Sanity check before writing: a hash that does not verify is worse than none.
if (!Auth::verify($password, $hash)) {
    fwrite(STDERR, "Generated hash failed to verify. Aborting without writing.\n");
    exit(1);
}

Database::run('UPDATE users SET password = ? WHERE id = ?', [$hash, $user['id']]);

// Read it back and confirm the stored value verifies.
$stored = Database::selectOne('SELECT password FROM users WHERE id = ?', [$user['id']]);

if ($stored === null || !Auth::verify($password, $stored['password'])) {
    fwrite(STDERR, "Wrote the hash but it does not verify when read back. Check the column type.\n");
    exit(1);
}

printf("Password updated for %s (id=%d, %s) and verified against the stored hash.\n",
    $user['email'], $user['id'], $user['username']);
