<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$query = "SELECT id, nama, email, role, status, foto_profile FROM users ORDER BY id ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Query error: " . mysqli_error($conn)
    ]);
    exit();
}

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['id'] = (int) $row['id'];
    $users[] = $row;
}

echo json_encode([
    "success" => true,
    "total" => count($users),
    "data" => $users
]);
?>
