<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, [
    'cache' => false, // __DIR__ . '/../cache', 
    'debug' => true,
]);
$twig->addExtension(new \Twig\Extension\DebugExtension());
$twig->addGlobal('session', $_SESSION);

function render_view($template, $data = [])
{
    global $twig;

    if (isset($_SESSION['flash_message'])) {
        $data['flash_message'] = $_SESSION['flash_message'];
        $data['flash_type'] = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }

    echo $twig->render($template, $data);
}

function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function json_response($data, $status = 200)
{
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function flash_message($type = 'info', $message = '')
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function get_role_name($role_id)
{
    switch ($role_id) {
        case 1:
            return 'Super Admin';
        case 2:
            return 'Admin';
        case 3:
            return 'Student';
        default:
            return 'Unknown';
    }
}
