<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

// Force password reset for students if needed
if ($_SESSION['role'] == 3 && $_SESSION['needs_password_reset']) {
    redirect('change_password.php');
}

$role_id = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$data = [
    'csrf_token' => $_SESSION['csrf_token'],
    'user' => $_SESSION
];

if ($role_id == 1 || $role_id == 2) {
    // Admin / Super Admin

    // Fetch stats
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $data['total_students'] = $stmt->fetchColumn();

    if ($role_id == 1) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 2");
        $data['total_admins'] = $stmt->fetchColumn();
    }

    // Recent Students
    $stmt = $pdo->query("SELECT s.*, u.username, u.email FROM students s JOIN users u ON s.user_id = u.id ORDER BY s.id DESC LIMIT 5");
    $data['recent_students'] = $stmt->fetchAll();

    render_view('admin/dashboard.twig', $data);

} elseif ($role_id == 3) {
    // Student
    $stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $student = $stmt->fetch();

    if (!$student) {
        // Should not happen if data is consistent, but handle it
        $data['error'] = "Student record not found please contact admin.";
    } else {
        $data['student'] = $student;

        // Fetch Attendance
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? ORDER BY attendance_date DESC LIMIT 10");
        $stmt->execute([$student['id']]);
        $data['attendance'] = $stmt->fetchAll();
    }

    render_view('student/dashboard.twig', $data);
} else {
    // Invalid role or corrupted session
    session_destroy();
    redirect('login.php');
}
