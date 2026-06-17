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
        "message" => "Akses ditolak. Hanya admin yang dapat menghapus koleksi."
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
        "message" => "ID koleksi wajib diisi"
    ]);
    exit();
}

// Check if collection exists and get cover image
$query = "SELECT cover_image FROM collections WHERE id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Koleksi dengan ID $id tidak ditemukan"
    ]);
    exit();
}

$row = mysqli_fetch_assoc($result);
$cover_image = $row['cover_image'];

// Delete cover image if it exists
if (!empty($cover_image)) {
    $filePath = __DIR__ . "/../../uploads/" . $cover_image;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Delete from database
$deleteQuery = "DELETE FROM collections WHERE id = $id";

if (mysqli_query($conn, $deleteQuery)) {
    echo json_encode([
        "success" => true,
        "message" => "Koleksi berhasil dihapus"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menghapus koleksi: " . mysqli_error($conn)
    ]);
}
?>
