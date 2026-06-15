<?php

header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Anda harus login terlebih dahulu"
    ]);
    exit;
}

$query = mysqli_query($conn,
    "SELECT id, nama, email, nim, role, status, foto_profile
     FROM users WHERE id = $user_id"
);

$user = mysqli_fetch_assoc($query);

if ($user) {
    echo json_encode([
        "success" => true,
        "data" => $user
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "User tidak ditemukan"
    ]);
}
