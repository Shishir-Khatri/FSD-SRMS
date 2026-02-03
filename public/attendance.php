<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
if (!in_array($_SESSION['role'], [1, 2])) {
    redirect('dashboard.php');
}

$date = $_GET['date'] ?? date('Y-m-d');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $attendance = $_POST['attendance'] ?? [];
    $date = $_POST['date'];

    try {
        $pdo->beginTransaction();


        foreach ($attendance as $student_id => $status) {
            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND attendance_date = ?");
            $stmt->execute([$student_id, $date]);
            $exists = $stmt->fetch();

            if ($exists) {
                $update = $pdo->prepare("UPDATE attendance SET status = ?, recorded_by = ? WHERE id = ?");
                $update->execute([$status, $_SESSION['user_id'], $exists['id']]);
            } else {
                $insert = $pdo->prepare("INSERT INTO attendance (student_id, attendance_date, status, recorded_by) VALUES (?, ?, ?, ?)");
                $insert->execute([$student_id, $date, $status, $_SESSION['user_id']]);
            }
        }

        $pdo->commit();
        $message = "Attendance saved successfully for $date";

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error saving attendance: " . $e->getMessage();
    }
}

// Fetch students and their attendance for the selected date
$stmt = $pdo->prepare("
    SELECT s.id, s.first_name, s.last_name, a.status 
    FROM students s 
    LEFT JOIN attendance a ON s.id = a.student_id AND a.attendance_date = ?
    ORDER BY s.last_name ASC
");
$stmt->execute([$date]);
$students = $stmt->fetchAll();

render_view('admin/attendance.twig', [
    'date' => $date,
    'students' => $students,
    'message' => $message,
    'csrf_token' => $_SESSION['csrf_token']
]);
