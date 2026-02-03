<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function check_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('CSRF Token Mismatch');
        }
    }
}

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function require_role($allowed_roles)
{
    require_login();
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        
        redirect('dashboard.php');
    }
}

function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

function hash_password($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}
