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
        "message" => "Akses ditolak. Hanya admin yang dapat mengupdate koleksi."
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
$name = isset($_POST['name']) ? mysqli_real_escape_string($conn, trim($_POST['name'])) : '';
$description = isset($_POST['description']) ? mysqli_real_escape_string($conn, trim($_POST['description'])) : '';
$difficulty = isset($_POST['difficulty']) ? mysqli_real_escape_string($conn, $_POST['difficulty']) : 'easy';
$estimated_duration = isset($_POST['estimated_duration']) ? mysqli_real_escape_string($conn, trim($_POST['estimated_duration'])) : '';
$status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'active';

// Validation
if (empty($id)) {
    echo json_encode([
        "success" => false,
        "message" => "ID koleksi wajib diisi"
    ]);
    exit();
}

if (empty($name) || empty($estimated_duration) || empty($difficulty)) {
    echo json_encode([
        "success" => false,
        "message" => "Field wajib diisi: nama koleksi, durasi, dan tingkat kesulitan."
    ]);
    exit();
}

// Check if collection exists
$checkQuery = mysqli_query($conn, "SELECT * FROM collections WHERE id = $id");
if (mysqli_num_rows($checkQuery) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Koleksi dengan ID $id tidak ditemukan"
    ]);
    exit();
}

$oldData = mysqli_fetch_assoc($checkQuery);

// Upload new cover if any
$coverQuery = "";
$new_cover_image = $oldData['cover_image'];
if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['cover_image']['name']);
    $tmpFile = $_FILES['cover_image']['tmp_name'];
    $new_cover_image = time() . "-" . $fileName;

    $folder = __DIR__ . "/../../uploads/";
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    if (move_uploaded_file($tmpFile, $folder . $new_cover_image)) {
        // Delete old cover if it exists and is different
        if (!empty($oldData['cover_image']) && file_exists($folder . $oldData['cover_image'])) {
            unlink($folder . $oldData['cover_image']);
        }
        $coverQuery = ", cover_image = '$new_cover_image'";
    }
}

// Update database
$query = "UPDATE collections SET 
    name = '$name', 
    description = '$description', 
    difficulty = '$difficulty', 
    estimated_duration = '$estimated_duration', 
    status = '$status'
    $coverQuery
    WHERE id = $id";

if (mysqli_query($conn, $query)) {
    // Get latest updated data
    $updatedQuery = mysqli_query($conn, "SELECT * FROM collections WHERE id = $id");
    $updatedData = mysqli_fetch_assoc($updatedQuery);

    echo json_encode([
        "success" => true,
        "message" => "Koleksi berhasil diupdate",
        "data" => $updatedData
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengupdate koleksi: " . mysqli_error($conn)
    ]);
}
?>
