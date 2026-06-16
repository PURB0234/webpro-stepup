<?php
/**
 * Community Feed - CREATE Post
 * Endpoint untuk JavaFX desktop app.
 * Menerima: user_id, deskripsi, langkah, jarak, kalori (via POST form data)
 * Response: { success: true/false, message: "..." }
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diizinkan. Gunakan POST."
    ]);
    exit();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

// JavaFX mengirim user_id via POST (bukan session)
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$deskripsi = isset($_POST['deskripsi']) ? mysqli_real_escape_string($conn, $_POST['deskripsi']) : '';
$langkah   = isset($_POST['langkah']) ? mysqli_real_escape_string($conn, $_POST['langkah']) : '';
$jarak     = isset($_POST['jarak']) ? mysqli_real_escape_string($conn, $_POST['jarak']) : '';
$kalori    = isset($_POST['kalori']) ? mysqli_real_escape_string($conn, $_POST['kalori']) : '';

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "User ID tidak valid"
    ]);
    exit();
}

if (empty($deskripsi)) {
    echo json_encode([
        "success" => false,
        "message" => "Deskripsi tidak boleh kosong"
    ]);
    exit();
}

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

$query = mysqli_query($conn,
    "INSERT INTO community (deskripsi, gambar, langkah, jarak, kalori, user_id)
     VALUES ('$deskripsi', '$gambar', '$langkah', '$jarak', '$kalori', $user_id)"
);

if ($query) {
    echo json_encode([
        "success" => true,
        "message" => "Postingan berhasil dibuat",
        "data" => [
            "id" => mysqli_insert_id($conn)
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal membuat postingan: " . mysqli_error($conn)
    ]);
}
?>
