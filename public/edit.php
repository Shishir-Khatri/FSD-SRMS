<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

// Admin can edit Students. Super Admin can edit all (except maybe themselves via this simple form? - strict requirements say CRUD)

$id = $_GET['id'] ?? null;
if (!$id)
    redirect('dashboard.php');

// Fetch the student
$stmt = $pdo->prepare("SELECT s.*, u.username, u.email, u.role_id, u.is_active FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    redirect('dashboard.php');
}

// Permission check
if ($_SESSION['role'] == 3) {
    redirect('dashboard.php'); // Students can't edit profiles via this page
}

// Logic to process update
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']); // User data
    $contact = trim($_POST['contact']);
    $academic_year = $_POST['academic_year'];

    try {
        $pdo->beginTransaction();

        // Update User
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $student['user_id']]);

        // Update Student
        $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, contact = ?, academic_year = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $contact, $academic_year, $id]);

        $pdo->commit();
        flash_message('success', 'Student updated successfully.');
        redirect('dashboard.php');

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error updating record: " . $e->getMessage();
    }
}

render_view('admin/edit.twig', [
    'student' => $student,
    'error' => $error,
    'csrf_token' => $_SESSION['csrf_token']
]);
