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
        "message" => "Akses ditolak. Hanya admin yang dapat menghapus badge."
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

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if (empty($id)) {
    echo json_encode([
        "success" => false,
        "message" => "ID badge wajib diisi"
    ]);
    exit();
}

// Retrieve old image before delete
$checkQuery = mysqli_query($conn, "SELECT badge_icon FROM badges WHERE id = $id");
if (mysqli_num_rows($checkQuery) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Badge dengan ID $id tidak ditemukan"
    ]);
    exit();
}

$badge = mysqli_fetch_assoc($checkQuery);

// Delete badge record (cascade deletes from user_badges will occur naturally if configured, but MySQL foreign keys ON DELETE CASCADE will handle it)
$query = "DELETE FROM badges WHERE id = $id";
if (mysqli_query($conn, $query)) {
    // Clean up file from uploads
    if (!empty($badge['badge_icon'])) {
        $filePath = __DIR__ . "/../../uploads/badge_icons/" . $badge['badge_icon'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Badge berhasil dihapus"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menghapus badge: " . mysqli_error($conn)
    ]);
}
?>
