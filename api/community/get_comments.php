<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$community_id = isset($_GET['community_id']) ? intval($_GET['community_id']) : 0;

if ($community_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID postingan tidak valid"
    ]);
    exit;
}

$query = mysqli_query($conn,
    "SELECT comments.*, users.nama AS nama_user, users.foto_profile AS foto_user
     FROM comments
     LEFT JOIN users ON comments.user_id = users.id
     WHERE comments.community_id = $community_id
     ORDER BY comments.id ASC"
);

$data = [];

while ($row = mysqli_fetch_assoc($query)) {
    // Fallback jika user sudah dihapus
    if (empty($row['nama_user'])) {
        $row['nama_user'] = 'User';
    }
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
