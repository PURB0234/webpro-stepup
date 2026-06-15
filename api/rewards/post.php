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

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal"
    ]);
    exit();
}

// Ambil data dari form-data atau JSON body
$name_reward = isset($_POST['name_reward']) ? mysqli_real_escape_string($conn, $_POST['name_reward']) : '';
$poin = isset($_POST['poin']) ? mysqli_real_escape_string($conn, $_POST['poin']) : '';
$description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';

// Validasi
if (empty($name_reward) || empty($poin) || empty($description)) {
    echo json_encode([
        "success" => false,
        "message" => "Field wajib: name_reward, poin, description"
    ]);
    exit();
}

// Upload gambar (opsional)
$namaBaru = '';
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $namaFile = basename($_FILES['gambar']['name']);
    $tmpFile = $_FILES['gambar']['tmp_name'];
    $namaBaru = time() . "-" . $namaFile;

    $folder = __DIR__ . "/../../uploads/";
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
    move_uploaded_file($tmpFile, $folder . $namaBaru);
}

// Insert ke database
$query = "INSERT INTO rewards (name_reward, description, poin, gambar)
          VALUES ('$name_reward', '$description', '$poin', '$namaBaru')";

if (mysqli_query($conn, $query)) {
    echo json_encode([
        "success" => true,
        "message" => "Reward berhasil ditambahkan",
        "data" => [
            "id" => mysqli_insert_id($conn),
            "name_reward" => $name_reward,
            "description" => $description,
            "poin" => $poin,
            "gambar" => $namaBaru
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menambahkan reward: " . mysqli_error($conn)
    ]);
}