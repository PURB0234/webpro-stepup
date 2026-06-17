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

// Retrieve inputs
$challenge_id = isset($_POST['challenge_id']) ? intval($_POST['challenge_id']) : 0;
$progress_value = isset($_POST['progress_value']) ? intval($_POST['progress_value']) : 0;

if ($challenge_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID challenge tidak valid"
    ]);
    exit();
}

if ($progress_value <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Nilai progress harus lebih besar dari 0"
    ]);
    exit();
}

// Check participation status
$partQuery = mysqli_query($conn, "SELECT * FROM challenge_participants WHERE challenge_id = $challenge_id AND user_id = $user_id");
if (mysqli_num_rows($partQuery) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Anda belum bergabung dalam challenge ini"
    ]);
    exit();
}

$participation = mysqli_fetch_assoc($partQuery);

if ($participation['completion_status'] === 'completed') {
    echo json_encode([
        "success" => false,
        "message" => "Anda sudah menyelesaikan challenge ini"
    ]);
    exit();
}

// Get challenge goal and rewards details
$challengeQuery = mysqli_query($conn, "SELECT goal_value, reward_points, title FROM challenges WHERE id = $challenge_id");
if (mysqli_num_rows($challengeQuery) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Challenge tidak ditemukan"
    ]);
    exit();
}

$challenge = mysqli_fetch_assoc($challengeQuery);
$goal_value = intval($challenge['goal_value']);
$reward_points = intval($challenge['reward_points']);

// Calculate new progress
$new_progress = intval($participation['current_progress']) + $progress_value;
$completed = false;

if ($new_progress >= $goal_value) {
    $new_progress = $goal_value; // Cap progress at the goal value
    $completed = true;
}

// Update participation
if ($completed) {
    $updateQuery = "UPDATE challenge_participants 
                    SET current_progress = $new_progress, completion_status = 'completed' 
                    WHERE challenge_id = $challenge_id AND user_id = $user_id";
} else {
    $updateQuery = "UPDATE challenge_participants 
                    SET current_progress = $new_progress 
                    WHERE challenge_id = $challenge_id AND user_id = $user_id";
}

if (!mysqli_query($conn, $updateQuery)) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal memperbarui progress: " . mysqli_error($conn)
    ]);
    exit();
}

// Distribute reward points if completed
if ($completed && $reward_points > 0) {
    // Check if user has poin column, auto-create if not (safety fallback)
    $checkPoin = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'poin'");
    if (mysqli_num_rows($checkPoin) == 0) {
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN poin INT DEFAULT 0");
    }

    $updatePoints = mysqli_query($conn, "UPDATE users SET poin = poin + $reward_points WHERE id = $user_id");
    
    if ($updatePoints) {
        // Retrieve and update points session
        $userQuery = mysqli_query($conn, "SELECT poin FROM users WHERE id = $user_id");
        $userData = mysqli_fetch_assoc($userQuery);
        $_SESSION['poin'] = intval($userData['poin']);
    }
}

// Check and award badges on challenge completion
if ($completed) {
    require_once __DIR__ . "/../../services/badge_helper.php";
    checkAndAwardBadges($conn, $user_id);
}

if ($completed) {
    echo json_encode([
        "success" => true,
        "completed" => true,
        "message" => "Selamat! Anda berhasil menyelesaikan challenge \"" . $challenge['title'] . "\" dan mendapatkan " . $reward_points . " poin!",
        "data" => [
            "new_progress" => $new_progress,
            "goal_value" => $goal_value,
            "points_awarded" => $reward_points
        ]
    ]);
} else {
    echo json_encode([
        "success" => true,
        "completed" => false,
        "message" => "Progress berhasil ditambahkan!",
        "data" => [
            "new_progress" => $new_progress,
            "goal_value" => $goal_value
        ]
    ]);
}
?>
