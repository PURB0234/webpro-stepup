<?php
/**
 * Community Feed - GET All Posts
 * Endpoint untuk JavaFX desktop app.
 * Response: { success: true, data: [...] }
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$query = mysqli_query($conn,
    "SELECT community.*, users.nama AS nama_user, users.foto_profile AS foto_user
     FROM community
     LEFT JOIN users ON community.user_id = users.id
     ORDER BY community.id DESC"
);

if (!$query) {
    echo json_encode([
        "success" => false,
        "message" => "Query error: " . mysqli_error($conn)
    ]);
    exit();
}

$data = [];
while ($row = mysqli_fetch_assoc($query)) {
    $row['id'] = (int) $row['id'];
    $row['user_id'] = (int) $row['user_id'];
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "total" => count($data),
    "data" => $data
]);
?>
