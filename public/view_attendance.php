<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
if (!in_array($_SESSION['role'], [1, 2])) {
    redirect('dashboard.php');
}

$student_id = $_GET['id'] ?? null;
if (!$student_id)
    redirect('dashboard.php');

// Fetch student details
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student)
    redirect('dashboard.php');

// Fetch attendance
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? ORDER BY attendance_date DESC");
$stmt->execute([$student_id]);
$attendance_records = $stmt->fetchAll();

// Calculate Stats
$total = count($attendance_records);
$present = 0;
foreach ($attendance_records as $rec) {
    if ($rec['status'] == 'Present')
        $present++;
}
$percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;

render_view('admin/view_attendance.twig', [
    'student' => $student,
    'records' => $attendance_records,
    'stats' => ['total' => $total, 'present' => $present, 'percentage' => $percentage]
]);
