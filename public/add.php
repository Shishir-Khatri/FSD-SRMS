<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

// Only Admin and Super Admin can add
if (!in_array($_SESSION['role'], [1, 2])) {
    redirect('dashboard.php');
}

$type = $_GET['type'] ?? 'student';
$error = '';
$success = '';

// Block Admin from adding Admin
if ($type === 'admin' && $_SESSION['role'] != 1) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    // Common Fields
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role_id = ($type === 'admin') ? 2 : 3;
    $password = $_POST['password'] ?? 'Password123'; // Default or generated

    // Validation
    if (empty($username) || empty($email)) {
        $error = "Username and Email are required.";
    } else {
        // Check duplication
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username or Email already exists.";
        }
    }

    if (empty($error)) {
        try {
            $pdo->beginTransaction();

            // Insert User
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id, needs_password_reset) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$username, $email, hash_password($password), $role_id]);
            $user_id = $pdo->lastInsertId();

            if ($type === 'student') {
                $first_name = trim($_POST['first_name']);
                $last_name = trim($_POST['last_name']);
                $dob = $_POST['dob'];
                $contact = trim($_POST['contact']);
                $address = trim($_POST['address']);
                $academic_year = $_POST['academic_year'];

                $stmt = $pdo->prepare("INSERT INTO students (user_id, first_name, last_name, dob, contact, address, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $first_name, $last_name, $dob, $contact, $address, $academic_year]);
            }

            $pdo->commit();
            flash_message('success', ucfirst($type) . " added successfully with default password 'Password123'.");
            redirect('dashboard.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "System Error: " . $e->getMessage();
        }
    }
}

render_view('admin/add.twig', [
    'type' => $type,
    'error' => $error,
    'csrf_token' => $_SESSION['csrf_token']
]);
