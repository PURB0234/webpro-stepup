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
        "message" => "Akses ditolak. Hanya admin yang dapat membuat challenge."
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
$title = isset($_POST['title']) ? mysqli_real_escape_string($conn, trim($_POST['title'])) : '';
$description = isset($_POST['description']) ? mysqli_real_escape_string($conn, trim($_POST['description'])) : '';
$challenge_type = isset($_POST['challenge_type']) ? mysqli_real_escape_string($conn, $_POST['challenge_type']) : '';
$goal_type = isset($_POST['goal_type']) ? mysqli_real_escape_string($conn, $_POST['goal_type']) : '';
$goal_value = isset($_POST['goal_value']) ? intval($_POST['goal_value']) : 0;
$reward_points = isset($_POST['reward_points']) ? intval($_POST['reward_points']) : 0;
$badge_reward = isset($_POST['badge_reward']) ? mysqli_real_escape_string($conn, trim($_POST['badge_reward'])) : '';
$start_date = isset($_POST['start_date']) ? mysqli_real_escape_string($conn, $_POST['start_date']) : '';
$end_date = isset($_POST['end_date']) ? mysqli_real_escape_string($conn, $_POST['end_date']) : '';
$status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'draft';

// Validation
if (empty($title) || empty($challenge_type) || empty($goal_type) || $goal_value <= 0 || empty($start_date) || empty($end_date)) {
    echo json_encode([
        "success" => false,
        "message" => "Semua field wajib diisi kecuali deskripsi, gambar banner, dan badge."
    ]);
    exit();
}

// Upload banner image
$banner_image = '';
if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['banner_image']['name']);
    $tmpFile = $_FILES['banner_image']['tmp_name'];
    $banner_image = time() . "-" . $fileName;

    $folder = __DIR__ . "/../../uploads/";
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
    move_uploaded_file($tmpFile, $folder . $banner_image);
}

// Insert into database
$query = "INSERT INTO challenges (title, description, banner_image, challenge_type, goal_type, goal_value, reward_points, badge_reward, start_date, end_date, status)
          VALUES ('$title', '$description', '$banner_image', '$challenge_type', '$goal_type', $goal_value, $reward_points, '$badge_reward', '$start_date', '$end_date', '$status')";

if (mysqli_query($conn, $query)) {
    $newId = mysqli_insert_id($conn);
    echo json_encode([
        "success" => true,
        "message" => "Challenge berhasil dibuat",
        "data" => [
            "id" => $newId,
            "title" => $title,
            "description" => $description,
            "banner_image" => $banner_image,
            "challenge_type" => $challenge_type,
            "goal_type" => $goal_type,
            "goal_value" => $goal_value,
            "reward_points" => $reward_points,
            "badge_reward" => $badge_reward,
            "start_date" => $start_date,
            "end_date" => $end_date,
            "status" => $status,
            "participant_count" => 0
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal membuat challenge: " . mysqli_error($conn)
    ]);
}
?>
