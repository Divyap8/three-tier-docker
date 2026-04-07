<?php
declare(strict_types=1);

/**
 * Application Bootstrap
 * Tier 2 — PHP-FPM Application Server
 */

require_once __DIR__ . '/../bootstrap.php';

$router = new Router();

$router->get('/', function () {
    $db   = Database::getInstance();
    $posts = $db->query('SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC')->fetchAll();
    render('home', ['posts' => $posts]);
});

$router->get('/health', function () {
    $db = Database::getInstance();
    $db->query('SELECT 1');           // verify DB connectivity
    json_response(['status' => 'ok', 'timestamp' => date('c')]);
});

$router->get('/users', function () {
    $db    = Database::getInstance();
    $users = $db->query('SELECT id, username, email, created_at FROM users')->fetchAll();
    json_response(['users' => $users]);
});

$router->dispatch();
