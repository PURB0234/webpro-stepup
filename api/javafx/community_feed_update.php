<?php
/**
 * Community Feed - UPDATE Post
 * Endpoint untuk JavaFX desktop app.
 * Menerima: id, user_id, deskripsi, langkah, jarak, kalori (via POST form data)
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

$id        = isset($_POST['id']) ? intval($_POST['id']) : 0;
$user_id   = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$deskripsi = isset($_POST['deskripsi']) ? mysqli_real_escape_string($conn, $_POST['deskripsi']) : '';
$langkah   = isset($_POST['langkah']) ? mysqli_real_escape_string($conn, $_POST['langkah']) : '';
$jarak     = isset($_POST['jarak']) ? mysqli_real_escape_string($conn, $_POST['jarak']) : '';
$kalori    = isset($_POST['kalori']) ? mysqli_real_escape_string($conn, $_POST['kalori']) : '';
$is_admin  = isset($_POST['is_admin']) ? $_POST['is_admin'] === '1' : false;

if ($id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Post ID tidak valid"
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

// Cek kepemilikan post (kecuali admin)
if (!$is_admin) {
    $cek = mysqli_query($conn, "SELECT user_id FROM community WHERE id = $id");
    $post = mysqli_fetch_assoc($cek);

    if (!$post) {
        echo json_encode([
            "success" => false,
            "message" => "Postingan tidak ditemukan"
        ]);
        exit();
    }

    if ((int)$post['user_id'] !== $user_id) {
        echo json_encode([
            "success" => false,
            "message" => "Anda tidak memiliki izin untuk mengedit postingan ini"
        ]);
        exit();
    }
}

$query = mysqli_query($conn,
    "UPDATE community
     SET deskripsi = '$deskripsi',
         langkah = '$langkah',
         jarak = '$jarak',
         kalori = '$kalori'
     WHERE id = $id"
);

if ($query) {
    echo json_encode([
        "success" => true,
        "message" => "Postingan berhasil diperbarui"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal memperbarui postingan: " . mysqli_error($conn)
    ]);
}
?>
