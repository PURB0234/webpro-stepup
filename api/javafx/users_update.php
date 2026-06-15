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
$role = isset($_POST['role']) ? mysqli_real_escape_string($conn, $_POST['role']) : '';
$status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : '';

if ($id <= 0 || empty($role) || empty($status)) {
    echo json_encode([
        "success" => false,
        "message" => "Field wajib: id, role, status"
    ]);
    exit();
}

$query = "UPDATE users SET role = '$role', status = '$status' WHERE id = $id";

if (mysqli_query($conn, $query)) {
    echo json_encode([
        "success" => true,
        "message" => "User berhasil diupdate"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal update user: " . mysqli_error($conn)
    ]);
}
?>
