<?php
namespace App\Models;

use Core\Auth;
use Core\Database;
use Core\Model;

class User extends Model
{
    protected string $table = 'users';

    public const ROLES = ['user', 'researcher', 'admin'];

    public function findByEmail(string $email): ?array
    {
        return Database::selectOne(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
    }

    /** True when the email or username is already taken. */
    public function exists(string $email, string $username): bool
    {
        return Database::selectOne(
            'SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1',
            [$email, $username]
        ) !== null;
    }

    public function create(string $username, string $email, string $password, string $role = 'user'): int
    {
        return Database::insert(
            'INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)',
            [$username, $email, Auth::hash($password), $role]
        );
    }

    /**
     * Verify credentials. Returns the user row on success, null on failure.
     */
    public function attempt(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        // Hash a dummy value when the user is missing so that response time
        // does not reveal whether the address is registered.
        if ($user === null) {
            Auth::verify($password, '$2y$10$usesomesillystringfor.HashingToEqualiseTimingxxxxxxxxxx');
            return null;
        }

        return Auth::verify($password, $user['password']) ? $user : null;
    }
}
