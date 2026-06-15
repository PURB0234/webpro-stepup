<?php
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

$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$nim = isset($_POST['nim']) ? trim($_POST['nim']) : '';

// Validasi
if (empty($nama) || empty($email) || empty($password) || empty($nim)) {
    echo json_encode([
        "success" => false,
        "message" => "Semua field wajib diisi (nama, email, password, nim)"
    ]);
    exit();
}

// Cek email sudah terdaftar
$email_escaped = mysqli_real_escape_string($conn, $email);
$cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$email_escaped'");
if (mysqli_num_rows($cek) > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email sudah terdaftar"
    ]);
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$nama_escaped = mysqli_real_escape_string($conn, $nama);
$nim_escaped = mysqli_real_escape_string($conn, $nim);

$query = "INSERT INTO users (nama, email, password, nim, role) 
          VALUES ('$nama_escaped', '$email_escaped', '$hashedPassword', '$nim_escaped', 'user')";

if (mysqli_query($conn, $query)) {
    $newId = mysqli_insert_id($conn);
    echo json_encode([
        "success" => true,
        "message" => "Registrasi berhasil",
        "data" => [
            "id" => $newId,
            "nama" => $nama,
            "email" => $email,
            "nim" => $nim,
            "role" => "user"
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Registrasi gagal: " . mysqli_error($conn)
    ]);
}
?>
