<?php

header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

// Cek login
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$nama_user = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'User';

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Anda harus login terlebih dahulu"
    ]);
    exit;
}

$community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
$komentar = isset($_POST['komentar']) ? mysqli_real_escape_string($conn, $_POST['komentar']) : '';

if ($community_id <= 0 || empty(trim($komentar))) {
    echo json_encode([
        "success" => false,
        "message" => "Data tidak lengkap"
    ]);
    exit;
}

$nama_escaped = mysqli_real_escape_string($conn, $nama_user);

$query = mysqli_query($conn,
    "INSERT INTO comments (community_id, user_id, nama_user, komentar, created_at)
     VALUES ($community_id, $user_id, '$nama_escaped', '$komentar', NOW())"
);

if ($query) {
    // Get count of comments for this post
    $countQuery = mysqli_query($conn,
        "SELECT COUNT(*) as total FROM comments WHERE community_id = $community_id"
    );
    $countRow = mysqli_fetch_assoc($countQuery);

    echo json_encode([
        "success" => true,
        "message" => "Komentar berhasil ditambahkan",
        "total_komentar" => $countRow['total']
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menambahkan komentar: " . mysqli_error($conn)
    ]);
}
