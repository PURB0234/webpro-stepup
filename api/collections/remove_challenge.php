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
        "message" => "Akses ditolak. Hanya admin yang dapat mengelola challenge dalam koleksi."
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

// Retrieve collection_id and challenge_id
$collection_id = isset($_POST['collection_id']) ? intval($_POST['collection_id']) : 0;
$challenge_id = isset($_POST['challenge_id']) ? intval($_POST['challenge_id']) : 0;

if ($collection_id <= 0 || $challenge_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID tidak valid."
    ]);
    exit();
}

// Delete association from collection_challenges
$query = "DELETE FROM collection_challenges WHERE collection_id = $collection_id AND challenge_id = $challenge_id";

if (mysqli_query($conn, $query)) {
    echo json_encode([
        "success" => true,
        "message" => "Tantangan berhasil dihapus dari koleksi."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menghapus tantangan dari koleksi: " . mysqli_error($conn)
    ]);
}
?>
