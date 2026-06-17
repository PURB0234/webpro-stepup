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
        "message" => "Akses ditolak. Hanya admin yang dapat mengupdate challenge."
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
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = isset($_POST['title']) ? mysqli_real_escape_string($conn, trim($_POST['title'])) : '';
$description = isset($_POST['description']) ? mysqli_real_escape_string($conn, trim($_POST['description'])) : '';
$challenge_type = isset($_POST['challenge_type']) ? mysqli_real_escape_string($conn, $_POST['challenge_type']) : '';
$goal_type = isset($_POST['goal_type']) ? mysqli_real_escape_string($conn, $_POST['goal_type']) : '';
$goal_value = isset($_POST['goal_value']) ? intval($_POST['goal_value']) : 0;
$reward_points = isset($_POST['reward_points']) ? intval($_POST['reward_points']) : 0;
$badge_reward = isset($_POST['badge_reward']) ? mysqli_real_escape_string($conn, trim($_POST['badge_reward'])) : '';
$start_date = isset($_POST['start_date']) ? mysqli_real_escape_string($conn, $_POST['start_date']) : '';
$end_date = isset($_POST['end_date']) ? mysqli_real_escape_string($conn, $_POST['end_date']) : '';
$status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : '';

// Validation
if (empty($id)) {
    echo json_encode([
        "success" => false,
        "message" => "ID challenge wajib diisi"
    ]);
    exit();
}

if (empty($title) || empty($challenge_type) || empty($goal_type) || $goal_value <= 0 || empty($start_date) || empty($end_date) || empty($status)) {
    echo json_encode([
        "success" => false,
        "message" => "Semua field wajib diisi kecuali deskripsi, gambar banner, dan badge."
    ]);
    exit();
}

// Check if challenge exists
$checkQuery = mysqli_query($conn, "SELECT * FROM challenges WHERE id = $id");
if (mysqli_num_rows($checkQuery) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Challenge dengan ID $id tidak ditemukan"
    ]);
    exit();
}

$oldData = mysqli_fetch_assoc($checkQuery);

// Upload new banner if any
$bannerQuery = "";
$new_banner_image = $oldData['banner_image'];
if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['banner_image']['name']);
    $tmpFile = $_FILES['banner_image']['tmp_name'];
    $new_banner_image = time() . "-" . $fileName;

    $folder = __DIR__ . "/../../uploads/";
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    if (move_uploaded_file($tmpFile, $folder . $new_banner_image)) {
        // Delete old banner if it exists and is different
        if (!empty($oldData['banner_image']) && file_exists($folder . $oldData['banner_image'])) {
            unlink($folder . $oldData['banner_image']);
        }
        $bannerQuery = ", banner_image = '$new_banner_image'";
    }
}

// Update database
$query = "UPDATE challenges SET 
    title = '$title', 
    description = '$description', 
    challenge_type = '$challenge_type', 
    goal_type = '$goal_type', 
    goal_value = $goal_value, 
    reward_points = $reward_points, 
    badge_reward = '$badge_reward', 
    start_date = '$start_date', 
    end_date = '$end_date', 
    status = '$status'
    $bannerQuery
    WHERE id = $id";

if (mysqli_query($conn, $query)) {
    // Get latest updated data
    $updatedQuery = mysqli_query($conn, "SELECT c.*, COALESCE(p.p_count, 0) AS participant_count 
                                         FROM challenges c
                                         LEFT JOIN (
                                             SELECT challenge_id, COUNT(*) AS p_count 
                                             FROM challenge_participants 
                                             GROUP BY challenge_id
                                         ) p ON c.id = p.challenge_id
                                         WHERE c.id = $id");
    $updatedData = mysqli_fetch_assoc($updatedQuery);
    
    // Standardize data types
    $updatedData['id'] = intval($updatedData['id']);
    $updatedData['goal_value'] = intval($updatedData['goal_value']);
    $updatedData['reward_points'] = intval($updatedData['reward_points']);
    $updatedData['participant_count'] = intval($updatedData['participant_count']);

    echo json_encode([
        "success" => true,
        "message" => "Challenge berhasil diupdate",
        "data" => $updatedData
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengupdate challenge: " . mysqli_error($conn)
    ]);
}
?>
