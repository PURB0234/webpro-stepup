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
        "message" => "Akses ditolak. Hanya admin yang dapat membuat badge."
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
$category = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : 'challenge';
$rarity = isset($_POST['rarity']) ? mysqli_real_escape_string($conn, $_POST['rarity']) : 'common';
$unlock_requirement = isset($_POST['unlock_requirement']) ? mysqli_real_escape_string($conn, trim($_POST['unlock_requirement'])) : '';
$related_challenge_id = isset($_POST['related_challenge_id']) && $_POST['related_challenge_id'] !== '' ? intval($_POST['related_challenge_id']) : 'NULL';
$related_collection_id = isset($_POST['related_collection_id']) && $_POST['related_collection_id'] !== '' ? intval($_POST['related_collection_id']) : 'NULL';
$status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'active';

// Validation
if (empty($name) || empty($unlock_requirement)) {
    echo json_encode([
        "success" => false,
        "message" => "Field wajib diisi: nama badge dan requirement unlock."
    ]);
    exit();
}

// Upload badge icon
$badge_icon = '';
if (isset($_FILES['badge_icon']) && $_FILES['badge_icon']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['badge_icon']['name']);
    $tmpFile = $_FILES['badge_icon']['tmp_name'];
    $badge_icon = time() . "-" . $fileName;

    $folder = __DIR__ . "/../../uploads/badge_icons/";
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
    move_uploaded_file($tmpFile, $folder . $badge_icon);
}

// Insert into database
$query = "INSERT INTO badges (name, description, badge_icon, category, rarity, unlock_requirement, related_challenge_id, related_collection_id, status)
          VALUES ('$name', '$description', '$badge_icon', '$category', '$rarity', '$unlock_requirement', $related_challenge_id, $related_collection_id, '$status')";

if (mysqli_query($conn, $query)) {
    $newId = mysqli_insert_id($conn);
    echo json_encode([
        "success" => true,
        "message" => "Badge berhasil dibuat",
        "data" => [
            "id" => $newId,
            "name" => $name,
            "description" => $description,
            "badge_icon" => $badge_icon,
            "category" => $category,
            "rarity" => $rarity,
            "unlock_requirement" => $unlock_requirement,
            "related_challenge_id" => $related_challenge_id === 'NULL' ? null : $related_challenge_id,
            "related_collection_id" => $related_collection_id === 'NULL' ? null : $related_collection_id,
            "status" => $status
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal membuat badge: " . mysqli_error($conn)
    ]);
}
?>
