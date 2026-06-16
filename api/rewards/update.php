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

// Pastikan method POST
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

// Proteksi admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        "success" => false,
        "message" => "Akses ditolak. Hanya admin yang dapat mengupdate reward."
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

// Ambil data dari form-data
$id_reward = isset($_POST['id_reward']) ? intval($_POST['id_reward']) : 0;
$name_reward = isset($_POST['name_reward']) ? mysqli_real_escape_string($conn, $_POST['name_reward']) : '';
$poin = isset($_POST['poin']) ? mysqli_real_escape_string($conn, $_POST['poin']) : '';
$description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
$stok = isset($_POST['stok']) ? intval($_POST['stok']) : 0;

// Validasi
if (empty($id_reward)) {
    echo json_encode([
        "success" => false,
        "message" => "ID reward wajib diisi"
    ]);
    exit();
}

if (empty($name_reward) || empty($poin) || empty($description)) {
    echo json_encode([
        "success" => false,
        "message" => "Field wajib: name_reward, poin, description"
    ]);
    exit();
}

// Cek apakah reward ada
$checkQuery = mysqli_query($conn, "SELECT * FROM rewards WHERE id_reward = $id_reward");
if (mysqli_num_rows($checkQuery) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Reward dengan ID $id_reward tidak ditemukan"
    ]);
    exit();
}

$oldData = mysqli_fetch_assoc($checkQuery);

// Upload gambar baru (opsional)
$gambarQuery = "";
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $namaFile = basename($_FILES['gambar']['name']);
    $tmpFile = $_FILES['gambar']['tmp_name'];
    $namaBaru = time() . "-" . $namaFile;

    $folder = __DIR__ . "/../../uploads/";
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    if (move_uploaded_file($tmpFile, $folder . $namaBaru)) {
        // Hapus gambar lama jika ada
        if (!empty($oldData['gambar']) && file_exists($folder . $oldData['gambar'])) {
            unlink($folder . $oldData['gambar']);
        }
        $gambarQuery = ", gambar = '$namaBaru'";
    }
}

// Update ke database
$query = "UPDATE rewards SET 
    name_reward = '$name_reward', 
    description = '$description', 
    poin = '$poin',
    stok = $stok
    $gambarQuery
    WHERE id_reward = $id_reward";

if (mysqli_query($conn, $query)) {
    // Ambil data terbaru
    $updatedQuery = mysqli_query($conn, "SELECT * FROM rewards WHERE id_reward = $id_reward");
    $updatedData = mysqli_fetch_assoc($updatedQuery);

    echo json_encode([
        "success" => true,
        "message" => "Reward berhasil diupdate",
        "data" => $updatedData
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengupdate reward: " . mysqli_error($conn)
    ]);
}
?>