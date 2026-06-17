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

// Retrieve challenge_id
$challenge_id = isset($_POST['challenge_id']) ? intval($_POST['challenge_id']) : 0;

if ($challenge_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID challenge tidak valid"
    ]);
    exit();
}

// Check if challenge exists and is active
$challengeQuery = mysqli_query($conn, "SELECT * FROM challenges WHERE id = $challenge_id");
if (mysqli_num_rows($challengeQuery) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Challenge tidak ditemukan"
    ]);
    exit();
}

$challenge = mysqli_fetch_assoc($challengeQuery);
if ($challenge['status'] !== 'active') {
    echo json_encode([
        "success" => false,
        "message" => "Anda hanya dapat bergabung ke challenge yang berstatus Active"
    ]);
    exit();
}

// Check if user has already joined
$checkJoin = mysqli_query($conn, "SELECT id FROM challenge_participants WHERE challenge_id = $challenge_id AND user_id = $user_id");
if (mysqli_num_rows($checkJoin) > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Anda sudah bergabung dalam challenge ini"
    ]);
    exit();
}

// Join the challenge
$insertQuery = "INSERT INTO challenge_participants (challenge_id, user_id, current_progress, completion_status) 
                VALUES ($challenge_id, $user_id, 0, 'in_progress')";

if (mysqli_query($conn, $insertQuery)) {
    echo json_encode([
        "success" => true,
        "message" => "Berhasil bergabung dalam challenge \"" . $challenge['title'] . "\"!"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal bergabung dalam challenge: " . mysqli_error($conn)
    ]);
}
?>
