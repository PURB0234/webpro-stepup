<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ensure POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diizinkan. Gunakan POST."
    ]);
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin protection
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        "success" => false,
        "message" => "Akses ditolak. Hanya admin yang dapat menghapus challenge."
    ]);
    exit();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal"
    ]);
    exit();
}

// Retrieve ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if (empty($id)) {
    echo json_encode([
        "success" => false,
        "message" => "ID challenge wajib diisi"
    ]);
    exit();
}

// Check if challenge exists and get banner image
$query = "SELECT banner_image FROM challenges WHERE id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Challenge dengan ID $id tidak ditemukan"
    ]);
    exit();
}

$row = mysqli_fetch_assoc($result);
$banner_image = $row['banner_image'];

// Delete banner image if it exists
if (!empty($banner_image)) {
    $filePath = __DIR__ . "/../../uploads/" . $banner_image;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Delete from database
$deleteQuery = "DELETE FROM challenges WHERE id = $id";

if (mysqli_query($conn, $deleteQuery)) {
    echo json_encode([
        "success" => true,
        "message" => "Challenge berhasil dihapus"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menghapus challenge: " . mysqli_error($conn)
    ]);
}
?>
