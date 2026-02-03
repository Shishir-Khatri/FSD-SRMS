<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

// Only Super Admin
if ($_SESSION['role'] != 1) {
    redirect('dashboard.php');
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = $_GET['q'] ?? '';
$is_ajax = isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

if ($is_ajax) {
    if (strlen($query) < 1) {
        json_response([]);
    }

    $term = "%$query%";
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE role_id = 2 AND (username LIKE ? OR email LIKE ?) LIMIT 10");
    $stmt->execute([$term, $term]);
    $results = $stmt->fetchAll();

    json_response(array_map(function ($row) {
        return [
            'id' => $row['id'],
            'text' => $row['username'] . ' (' . $row['email'] . ')'
        ];
    }, $results));
}

// Full List / Search Results logic
$where_sql = "WHERE role_id = 2";
$params = [];

if (!empty($query)) {
    $where_sql .= " AND (username LIKE ? OR email LIKE ?)";
    $term = "%$query%";
    $params = [$term, $term];
}

// Count
$count_sql = "SELECT COUNT(*) FROM users $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch
$sql = "SELECT * FROM users $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$admins = $stmt->fetchAll();

render_view('admin/admins.twig', [
    'query' => $query,
    'admins' => $admins,
    'pagination' => [
        'current' => $page,
        'total' => $total_pages,
        'has_next' => $page < $total_pages,
        'has_prev' => $page > 1,
        'next_page' => $page + 1,
        'prev_page' => $page - 1
    ]
]);
