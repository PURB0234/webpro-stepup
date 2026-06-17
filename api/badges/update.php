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
        "message" => "Akses ditolak. Hanya admin yang dapat mengupdate badge."
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
$category = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : 'challenge';
$rarity = isset($_POST['rarity']) ? mysqli_real_escape_string($conn, $_POST['rarity']) : 'common';
$unlock_requirement = isset($_POST['unlock_requirement']) ? mysqli_real_escape_string($conn, trim($_POST['unlock_requirement'])) : '';
$related_challenge_id = isset($_POST['related_challenge_id']) && $_POST['related_challenge_id'] !== '' ? intval($_POST['related_challenge_id']) : 'NULL';
$related_collection_id = isset($_POST['related_collection_id']) && $_POST['related_collection_id'] !== '' ? intval($_POST['related_collection_id']) : 'NULL';
$status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'active';

// Validation
if (empty($id)) {
    echo json_encode([
        "success" => false,
        "message" => "ID badge wajib diisi"
    ]);
    exit();
}

if (empty($name) || empty($unlock_requirement)) {
    echo json_encode([
        "success" => false,
        "message" => "Field wajib diisi: nama badge dan requirement unlock."
    ]);
    exit();
}

// Check if badge exists
$checkQuery = mysqli_query($conn, "SELECT * FROM badges WHERE id = $id");
if (mysqli_num_rows($checkQuery) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Badge dengan ID $id tidak ditemukan"
    ]);
    exit();
}

$oldData = mysqli_fetch_assoc($checkQuery);

// Upload new icon if any
$iconQuery = "";
$new_badge_icon = $oldData['badge_icon'];
if (isset($_FILES['badge_icon']) && $_FILES['badge_icon']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['badge_icon']['name']);
    $tmpFile = $_FILES['badge_icon']['tmp_name'];
    $new_badge_icon = time() . "-" . $fileName;

    $folder = __DIR__ . "/../../uploads/badge_icons/";
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    if (move_uploaded_file($tmpFile, $folder . $new_badge_icon)) {
        // Delete old icon if it exists
        if (!empty($oldData['badge_icon']) && file_exists($folder . $oldData['badge_icon'])) {
            unlink($folder . $oldData['badge_icon']);
        }
        $iconQuery = ", badge_icon = '$new_badge_icon'";
    }
}

// Update database
$query = "UPDATE badges SET 
    name = '$name', 
    description = '$description', 
    category = '$category', 
    rarity = '$rarity', 
    unlock_requirement = '$unlock_requirement', 
    related_challenge_id = $related_challenge_id, 
    related_collection_id = $related_collection_id, 
    status = '$status'
    $iconQuery
    WHERE id = $id";

if (mysqli_query($conn, $query)) {
    // Get latest updated data
    $updatedQuery = mysqli_query($conn, "SELECT * FROM badges WHERE id = $id");
    $updatedData = mysqli_fetch_assoc($updatedQuery);

    echo json_encode([
        "success" => true,
        "message" => "Badge berhasil diupdate",
        "data" => $updatedData
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengupdate badge: " . mysqli_error($conn)
    ]);
}
?>
