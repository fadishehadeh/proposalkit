<?php
declare(strict_types=1);

function config(string $key = ''): mixed
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require BASE_PATH . '/config.php';
    }
    if ($key === '') {
        return $cfg;
    }
    return $cfg[$key] ?? null;
}

function e(mixed $val): string
{
    return htmlspecialchars((string) ($val ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(config('app')['base'], '/');
    return $base . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require BASE_PATH . '/views/' . str_replace('.', '/', $template) . '.php';
}

function layout(string $template, string $pageTitle, array $data = []): void
{
    extract($data, EXTR_SKIP);
    ob_start();
    require BASE_PATH . '/views/' . str_replace('.', '/', $template) . '.php';
    $content = ob_get_clean();
    require BASE_PATH . '/views/layout.php';
}

function flash(string $key, string $msg): void
{
    $_SESSION['flash'][$key] = $msg;
}

function get_flash(string $key): ?string
{
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function old(string $key, mixed $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function set_old(array $data): void
{
    $_SESSION['old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): bool
{
    $token = $_POST['_csrf'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function money(float $amount, int $decimals = 0): string
{
    return number_format($amount, $decimals);
}

function hourly_rate(float $monthly): float
{
    return $monthly / 150;
}

function daily_rate(float $monthly): float
{
    return hourly_rate($monthly) * 8;
}

function current_uri(): string
{
    $base = rtrim(config('app')['base'], '/');
    $uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return '/' . ltrim(substr($uri, strlen($base)), '/');
}

function active(string $prefix): string
{
    return str_starts_with(current_uri(), $prefix) ? 'active' : '';
}
