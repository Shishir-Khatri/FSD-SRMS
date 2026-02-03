<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // fetch current user pass to verify
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $real_current_hash = $stmt->fetchColumn();

    if (empty($current) || empty($new) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif (!verify_password($current, $real_current_hash)) {
        $error = "Incorrect current password.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } elseif (strlen($new) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Update
        $stmt = $pdo->prepare("UPDATE users SET password = ?, needs_password_reset = 0 WHERE id = ?");
        $stmt->execute([hash_password($new), $_SESSION['user_id']]);

        // Update session
        $_SESSION['needs_password_reset'] = 0;

        flash_message('success', 'Password changed successfully.');
        redirect('dashboard.php');
    }
}

render_view('change_password.twig', [
    'error' => $error,
    'csrf_token' => $_SESSION['csrf_token']
]);
