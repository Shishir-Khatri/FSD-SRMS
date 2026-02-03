<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];

    if (empty($identifier) || empty($password)) {
        $error = "Please enter both username/email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && verify_password($password, $user['password'])) {
            if (!$user['is_active']) {
                $error = "Account is deactivated.";
            } else {
                // Set Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role_id'];
                $_SESSION['role_name'] = get_role_name($user['role_id']);
                $_SESSION['needs_password_reset'] = $user['needs_password_reset'];

                // Redirect if password reset needed
                if ($user['needs_password_reset']) {
                    
                    $_SESSION['flash_message'] = "Please change your password.";
                    $_SESSION['flash_type'] = "warning";
                }

                redirect('dashboard.php');
            }
        } else {
            $error = "Invalid credentials.";
        }
    }
}

render_view('login.twig', [
    'error' => $error,
    'csrf_token' => $_SESSION['csrf_token']
]);
