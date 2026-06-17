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
        "message" => "Akses ditolak. Hanya admin yang dapat membuat koleksi."
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
$name = isset($_POST['name']) ? mysqli_real_escape_string($conn, trim($_POST['name'])) : '';
$description = isset($_POST['description']) ? mysqli_real_escape_string($conn, trim($_POST['description'])) : '';
$difficulty = isset($_POST['difficulty']) ? mysqli_real_escape_string($conn, $_POST['difficulty']) : 'easy';
$estimated_duration = isset($_POST['estimated_duration']) ? mysqli_real_escape_string($conn, trim($_POST['estimated_duration'])) : '';
$status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'active';

// Validation
if (empty($name) || empty($estimated_duration) || empty($difficulty)) {
    echo json_encode([
        "success" => false,
        "message" => "Field wajib diisi: nama koleksi, durasi, dan tingkat kesulitan."
    ]);
    exit();
}

// Upload cover image
$cover_image = '';
if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['cover_image']['name']);
    $tmpFile = $_FILES['cover_image']['tmp_name'];
    $cover_image = time() . "-" . $fileName;

    $folder = __DIR__ . "/../../uploads/";
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
    move_uploaded_file($tmpFile, $folder . $cover_image);
}

// Insert into database
$query = "INSERT INTO collections (name, description, cover_image, difficulty, estimated_duration, status)
          VALUES ('$name', '$description', '$cover_image', '$difficulty', '$estimated_duration', '$status')";

if (mysqli_query($conn, $query)) {
    $newId = mysqli_insert_id($conn);
    echo json_encode([
        "success" => true,
        "message" => "Koleksi berhasil dibuat",
        "data" => [
            "id" => $newId,
            "name" => $name,
            "description" => $description,
            "cover_image" => $cover_image,
            "difficulty" => $difficulty,
            "estimated_duration" => $estimated_duration,
            "status" => $status
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal membuat koleksi: " . mysqli_error($conn)
    ]);
}
?>
