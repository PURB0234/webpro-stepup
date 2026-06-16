<?php
/**
 * Community Feed - DELETE Post
 * Endpoint untuk JavaFX desktop app.
 * Menerima: id, user_id, is_admin (via POST form data)
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

$id       = isset($_POST['id']) ? intval($_POST['id']) : 0;
$user_id  = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$is_admin = isset($_POST['is_admin']) ? $_POST['is_admin'] === '1' : false;

if ($id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Post ID tidak valid"
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
            "message" => "Anda tidak memiliki izin untuk menghapus postingan ini"
        ]);
        exit();
    }
}

// Hapus gambar terkait
$get = mysqli_query($conn, "SELECT gambar FROM community WHERE id = $id");
$data = mysqli_fetch_assoc($get);

if ($data && !empty($data['gambar'])) {
    $path = __DIR__ . "/../../uploads/" . $data['gambar'];
    if (file_exists($path)) {
        unlink($path);
    }
}

// Delete dari database
$query = mysqli_query($conn, "DELETE FROM community WHERE id = $id");

if ($query) {
    echo json_encode([
        "success" => true,
        "message" => "Postingan berhasil dihapus"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menghapus postingan: " . mysqli_error($conn)
    ]);
}
?>
