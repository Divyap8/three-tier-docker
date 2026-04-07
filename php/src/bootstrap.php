<?php
declare(strict_types=1);

/**
 * Bootstrap — environment, database, helpers
 */

// ── Load environment ──────────────────────────────────────────
$env = [
    'DB_HOST'     => getenv('DB_HOST')     ?: 'mysql_db',
    'DB_PORT'     => getenv('DB_PORT')     ?: '3306',
    'DB_NAME'     => getenv('DB_NAME')     ?: 'appdb',
    'DB_USER'     => getenv('DB_USER')     ?: 'appuser',
    'DB_PASSWORD' => getenv('DB_PASSWORD') ?: 'secret',
    'APP_ENV'     => getenv('APP_ENV')     ?: 'production',
];

// ── Database singleton ────────────────────────────────────────
class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        global $env;
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $env['DB_HOST'], $env['DB_PORT'], $env['DB_NAME']
            );
            self::$instance = new PDO($dsn, $env['DB_USER'], $env['DB_PASSWORD'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }
}

// ── Minimal router ────────────────────────────────────────────
class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path   = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

        if (isset($this->routes[$method][$path])) {
            ($this->routes[$method][$path])();
        } else {
            http_response_code(404);
            json_response(['error' => 'Not found', 'path' => $path]);
        }
    }
}

// ── Response helpers ──────────────────────────────────────────
function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function render(string $view, array $vars = []): void
{
    extract($vars);
    $file = __DIR__ . "/views/{$view}.php";
    if (!file_exists($file)) {
        json_response(['error' => "View '{$view}' not found"], 500);
    }
    include $file;
    exit;
}
