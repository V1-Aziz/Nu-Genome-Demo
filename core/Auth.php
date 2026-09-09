<?php
namespace Core;

use App\Models\User;

/**
 * Session-backed authentication.
 *
 * Note the method names: the old code defined a global get_current_user(),
 * which silently collided with PHP's built-in of that name and never took
 * effect. Namespaced static methods make that class of clash impossible.
 */
class Auth
{
    private static ?array $cachedUser = null;

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    /** The signed-in user's row, or null. Cached for the request. */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedUser === null) {
            self::$cachedUser = self::withoutSecrets((new User())->find((int) $_SESSION['user_id']));

            // Session points at a deleted user - treat as signed out.
            if (self::$cachedUser === null) {
                self::logout();
                return null;
            }
        }

        return self::$cachedUser;
    }

    public static function id(): ?int
    {
        return self::check() ? (int) $_SESSION['user_id'] : null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();
        return $user !== null && in_array($user['role'], $roles, true);
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);   // prevent session fixation
        $_SESSION['user_id'] = (int) $user['id'];
        self::$cachedUser = self::withoutSecrets($user);
    }

    public static function logout(): void
    {
        self::$cachedUser = null;
        $_SESSION = [];

        // Actually expire the cookie; the old logout only destroyed server state.
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
    }

    /** Strip the password hash so it never reaches a view or a log. */
    private static function withoutSecrets(?array $user): ?array
    {
        if ($user !== null) {
            unset($user['password']);
        }

        return $user;
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
