<?php
namespace Core;

/**
 * Base controller: view rendering, redirects, flash messages, access guards.
 */
abstract class Controller
{
    /** Render a view inside the main layout. */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        View::render($view, $data + [
            'auth_user' => Auth::user(),
            'flash'     => $this->pullFlash(),
        ], $layout);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . App::url($path));
        exit;
    }

    /** Send the visitor to login, remembering where they were headed. */
    protected function requireAuth(): array
    {
        if (!Auth::check()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';
            $this->flash('error', 'Please sign in to continue.');
            $this->redirect('/login');
        }

        return Auth::user();
    }

    protected function requireRole(string ...$roles): array
    {
        $user = $this->requireAuth();

        if (!Auth::hasRole(...$roles)) {
            http_response_code(403);
            $this->view('errors/403', ['page_title' => 'Access Denied']);
            exit;
        }

        return $user;
    }

    protected function requireGuest(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type][] = $message;
    }

    private function pullFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    /** Previously submitted input, held over one redirect after a validation failure. */
    protected function pullOld(): array
    {
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        return is_array($old) ? $old : [];
    }

    /** Trimmed POST value. */
    protected function input(string $key, string $default = ''): string
    {
        $value = $_POST[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    /** Raw POST value, for passwords where trimming would be wrong. */
    protected function raw(string $key, string $default = ''): string
    {
        $value = $_POST[$key] ?? $default;
        return is_string($value) ? $value : $default;
    }

    /**
     * Reject POSTs without a valid CSRF token.
     *
     * Bounces back to the path that was posted to, which is a route we control.
     * The Referer header is attacker-controllable and is deliberately not used.
     */
    protected function verifyCsrf(): void
    {
        if (!Csrf::check(is_string($_POST['_token'] ?? null) ? $_POST['_token'] : '')) {
            $this->flash('error', 'Your session expired. Please submit the form again.');
            $this->redirect($this->safePath(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
        }
    }

    /**
     * Reduce a redirect target to a same-application path.
     * Anything with a scheme or host is discarded.
     */
    protected function safePath(string $path, string $fallback = '/'): string
    {
        $parsed = parse_url($path);

        if ($parsed === false || !empty($parsed['host']) || !empty($parsed['scheme'])) {
            return $fallback;
        }

        $clean = $parsed['path'] ?? $fallback;

        if (!empty($parsed['query'])) {
            $clean .= '?' . $parsed['query'];
        }

        // Reject protocol-relative targets such as //evil.example.com
        if (!str_starts_with($clean, '/') || str_starts_with($clean, '//')) {
            return $fallback;
        }

        return $clean;
    }
}
