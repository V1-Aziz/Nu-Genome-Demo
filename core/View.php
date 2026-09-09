<?php
namespace Core;

use RuntimeException;

/**
 * Renders a view file inside a layout. Views receive data as local variables
 * and are responsible for escaping their own output via e().
 */
class View
{
    private const VIEW_PATH = __DIR__ . '/../app/Views/';

    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        echo self::capture($view, $data, $layout);
    }

    public static function capture(string $view, array $data = [], ?string $layout = 'main'): string
    {
        $content = self::renderFile($view, $data);

        if ($layout === null) {
            return $content;
        }

        return self::renderFile("layouts/{$layout}", $data + ['content' => $content]);
    }

    /** Render a partial and return it as a string. */
    public static function partial(string $view, array $data = []): string
    {
        return self::renderFile($view, $data);
    }

    private static function renderFile(string $view, array $data): string
    {
        $file = self::VIEW_PATH . str_replace('.', '/', $view) . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$view} ({$file})");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
