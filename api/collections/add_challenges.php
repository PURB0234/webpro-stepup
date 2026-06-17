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

// Retrieve collection_id and challenge_ids
$collection_id = isset($_POST['collection_id']) ? intval($_POST['collection_id']) : 0;
$challenge_ids_input = isset($_POST['challenge_ids']) ? $_POST['challenge_ids'] : null;

if ($collection_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID koleksi tidak valid"
    ]);
    exit();
}

if (empty($challenge_ids_input)) {
    echo json_encode([
        "success" => false,
        "message" => "Pilih minimal satu challenge untuk ditambahkan."
    ]);
    exit();
}

// Parse challenge_ids to array
$challenge_ids = [];
if (is_array($challenge_ids_input)) {
    $challenge_ids = array_map('intval', $challenge_ids_input);
} else {
    // Comma separated string
    $challenge_ids = array_map('intval', explode(',', $challenge_ids_input));
}

// Check if collection exists
$checkCol = mysqli_query($conn, "SELECT id FROM collections WHERE id = $collection_id");
if (mysqli_num_rows($checkCol) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Koleksi tidak ditemukan"
    ]);
    exit();
}

$successCount = 0;
$skippedCount = 0;

foreach ($challenge_ids as $ch_id) {
    if ($ch_id <= 0) continue;
    
    // Check if challenge exists
    $checkCh = mysqli_query($conn, "SELECT id FROM challenges WHERE id = $ch_id");
    if (mysqli_num_rows($checkCh) === 0) continue;

    // Check if association already exists
    $checkAssoc = mysqli_query($conn, "SELECT id FROM collection_challenges WHERE collection_id = $collection_id AND challenge_id = $ch_id");
    if (mysqli_num_rows($checkAssoc) === 0) {
        $insertQuery = "INSERT INTO collection_challenges (collection_id, challenge_id) VALUES ($collection_id, $ch_id)";
        if (mysqli_query($conn, $insertQuery)) {
            $successCount++;
        }
    } else {
        $skippedCount++;
    }
}

echo json_encode([
    "success" => true,
    "message" => "Berhasil memproses tantangan. Ditambahkan: $successCount, Dilewati: $skippedCount."
]);
?>
