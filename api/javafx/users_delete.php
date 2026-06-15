<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diizinkan. Gunakan POST."
    ]);
    exit();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID user tidak valid"
    ]);
    exit();
}

$query = "DELETE FROM users WHERE id = $id";

if (mysqli_query($conn, $query)) {
    echo json_encode([
        "success" => true,
        "message" => "User berhasil dihapus"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menghapus user: " . mysqli_error($conn)
    ]);
}
?>
