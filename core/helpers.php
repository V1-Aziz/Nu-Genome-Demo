<?php
use Core\App;
use Core\Csrf;

/** Escape a value for HTML output. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build an application URL. */
function url(string $path = '/'): string
{
    return App::url($path);
}

/** Hidden CSRF field for a form. */
function csrf_field(): string
{
    return Csrf::field();
}

/** Old input value after a failed submit. */
function old(array $old, string $key, string $default = ''): string
{
    return isset($old[$key]) ? (string) $old[$key] : $default;
}

/** CSS custom-property name for a risk level. */
function risk_color(string $level): string
{
    return match ($level) {
        'High'   => 'var(--danger)',
        'Medium' => 'var(--warning)',
        default  => 'var(--success)',
    };
}

/** Literal hex for a risk level, for canvas/inline use. */
function risk_hex(string $level): string
{
    return match ($level) {
        'High'   => '#b91c1c',
        'Medium' => '#c2410c',
        default  => '#047857',
    };
}
