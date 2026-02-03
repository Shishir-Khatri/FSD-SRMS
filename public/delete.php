<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

// Admin and Super Admin can delete students.
// Only Super Admin can delete Admins.

$id = $_GET['id'] ?? null;
if (!$id)
    redirect('dashboard.php');

$role = $_SESSION['role'];
if ($role == 3)
    redirect('dashboard.php'); // Students cannot delete

// Check who we are deleting
$stmt = $pdo->prepare("SELECT s.user_id, u.role_id FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
$stmt->execute([$id]);
$target = $stmt->fetch();

if ($target) {
    if ($target['role_id'] == 1) {
        // Cannot delete Super Admin
        flash_message('error', 'Cannot delete Super Admin.');
    } elseif ($target['role_id'] == 2 && $role != 1) {
        // Only Super Admin can delete Admin
        flash_message('error', 'Permission denied.');
    } else {
        // Proceed with user deletion (Cascades to student)
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$target['user_id']]);
        flash_message('success', 'Student record deleted successfully.');
    }
} else {
    
    flash_message('error', 'Record not found.');
}

redirect('dashboard.php');
