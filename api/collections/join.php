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

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Anda harus login terlebih dahulu"
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

// Retrieve collection_id
$collection_id = isset($_POST['collection_id']) ? intval($_POST['collection_id']) : 0;

if ($collection_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID koleksi tidak valid"
    ]);
    exit();
}

// Check if collection exists and is active
$colQuery = mysqli_query($conn, "SELECT * FROM collections WHERE id = $collection_id");
if (mysqli_num_rows($colQuery) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Koleksi tidak ditemukan"
    ]);
    exit();
}

$collection = mysqli_fetch_assoc($colQuery);
if ($collection['status'] !== 'active') {
    echo json_encode([
        "success" => false,
        "message" => "Koleksi ini sedang tidak aktif"
    ]);
    exit();
}

// Check if user already joined this collection
$checkJoin = mysqli_query($conn, "SELECT id FROM user_collections WHERE collection_id = $collection_id AND user_id = $user_id");
if (mysqli_num_rows($checkJoin) > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Anda sudah bergabung dalam koleksi ini"
    ]);
    exit();
}

// Start Transaction
mysqli_begin_transaction($conn);

try {
    // 1. Join collection
    $joinQuery = "INSERT INTO user_collections (user_id, collection_id, progress_percentage, status) 
                  VALUES ($user_id, $collection_id, 0, 'joined')";
    if (!mysqli_query($conn, $joinQuery)) {
        throw new Exception("Gagal bergabung ke koleksi: " . mysqli_error($conn));
    }

    // 2. Fetch assigned challenges
    $challengesQuery = mysqli_query($conn, "SELECT challenge_id FROM collection_challenges WHERE collection_id = $collection_id");
    
    // 3. Auto-join user to all challenges in this collection if they haven't joined yet
    while ($row = mysqli_fetch_assoc($challengesQuery)) {
        $chId = intval($row['challenge_id']);
        
        $checkChJoin = mysqli_query($conn, "SELECT id FROM challenge_participants WHERE challenge_id = $chId AND user_id = $user_id");
        if (mysqli_num_rows($checkChJoin) === 0) {
            $enrollQuery = "INSERT INTO challenge_participants (challenge_id, user_id, current_progress, completion_status) 
                            VALUES ($chId, $user_id, 0, 'in_progress')";
            if (!mysqli_query($conn, $enrollQuery)) {
                throw new Exception("Gagal mendaftarkan challenge ID $chId: " . mysqli_error($conn));
            }
        }
    }

    mysqli_commit($conn);
    echo json_encode([
        "success" => true,
        "message" => "Berhasil bergabung dalam koleksi \"" . $collection['name'] . "\"!"
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
