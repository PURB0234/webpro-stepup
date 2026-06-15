<?php

header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

// Cek login
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Anda harus login terlebih dahulu"
    ]);
    exit;
}

//isset mengecek nilai dari variabel itu ada tidak null
$deskripsi = isset($_POST['deskripsi']) ? mysqli_real_escape_string($conn, $_POST['deskripsi']) : '';
$langkah   = isset($_POST['langkah']) ? mysqli_real_escape_string($conn, $_POST['langkah']) : '';
$jarak    = isset($_POST['jarak']) ? mysqli_real_escape_string($conn, $_POST['jarak']) : '';
$kalori   = isset($_POST['kalori']) ? mysqli_real_escape_string($conn, $_POST['kalori']) : '';

$gambar = '';
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $gambar = basename($_FILES['gambar']['name']);
    $tmp = $_FILES['gambar']['tmp_name'];
    $uploadDir = __DIR__ . "/../../uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    move_uploaded_file($tmp, $uploadDir . $gambar);
}

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$query = mysqli_query($conn,
    "INSERT INTO community
    (deskripsi, gambar, langkah, jarak, kalori, user_id)

    VALUES

    ('$deskripsi', '$gambar',
     '$langkah', '$jarak',
     '$kalori', $user_id)"
);

if ($query) {
    echo json_encode([
        "success" => true,
        "message" => "Postingan berhasil dibuat"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal membuat postingan: " . mysqli_error($conn)
    ]);
}