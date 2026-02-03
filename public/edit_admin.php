<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

// Only Super Admin
if ($_SESSION['role'] != 1) {
    redirect('dashboard.php');
}

$id = $_GET['id'] ?? null;
if (!$id)
    redirect('admins.php');

// Fetch user ensuring it is an admin (role_id=2)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role_id = 2");
$stmt->execute([$id]);
$admin = $stmt->fetch();

if (!$admin) {
    redirect('admins.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($username) || empty($email)) {
        $error = "Username and Email are required.";
    } else {
        // Check uniqueness
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username or Email already exists.";
        } else {
            // Update
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$username, $email, $is_active, $id]);

            flash_message('success', 'Admin updated successfully.');
            redirect('admins.php');
        }
    }
}

render_view('admin/edit_admin.twig', [
    'admin' => $admin,
    'error' => $error,
    'csrf_token' => $_SESSION['csrf_token']
]);
