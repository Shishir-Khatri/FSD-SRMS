<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

if ($_SESSION['role'] == 3) {
    redirect('dashboard.php');
}

$query = $_GET['q'] ?? '';
$is_ajax = isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

// Pagination Settings
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if ($is_ajax) {
    if (strlen($query) < 1) {
        json_response([]);
    }

    $stmt = $pdo->prepare("SELECT s.id, s.first_name, s.last_name, u.email 
                          FROM students s 
                          JOIN users u ON s.user_id = u.id 
                          WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR u.email LIKE ? 
                          LIMIT 10");
    $term = "%$query%";
    $stmt->execute([$term, $term, $term]);
    $results = $stmt->fetchAll();

    json_response(array_map(function ($row) {
        return [
            'id' => $row['id'],
            'text' => $row['first_name'] . ' ' . $row['last_name'] . ' (' . $row['email'] . ')'
        ];
    }, $results));
}

// Full List / Search Results logic
$where_sql = "";
$params = [];

if (!empty($query)) {
    $where_sql = "WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR u.email LIKE ? OR u.username LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?";
    $term = "%$query%";
    $params = [$term, $term, $term, $term, $term];
}

// Count Total for Pagination
$count_sql = "SELECT COUNT(*) FROM students s JOIN users u ON s.user_id = u.id $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch Data
$sql = "SELECT s.*, u.email, u.username, u.is_active 
        FROM students s 
        JOIN users u ON s.user_id = u.id 
        $where_sql 
        ORDER BY s.id DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

render_view('admin/search.twig', [
    'query' => $query,
    'results' => $results,
    'csrf_token' => $_SESSION['csrf_token'],
    'pagination' => [
        'current' => $page,
        'total' => $total_pages,
        'has_next' => $page < $total_pages,
        'has_prev' => $page > 1,
        'next_page' => $page + 1,
        'prev_page' => $page - 1
    ]
]);
