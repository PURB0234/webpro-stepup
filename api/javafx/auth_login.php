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

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Email dan password wajib diisi"
    ]);
    exit();
}

$email_escaped = mysqli_real_escape_string($conn, $email);
$result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email_escaped'");
$data = mysqli_fetch_assoc($result);

if ($data && password_verify($password, $data['password'])) {
    echo json_encode([
        "success" => true,
        "message" => "Login berhasil",
        "data" => [
            "id" => (int) $data['id'],
            "nama" => $data['nama'],
            "email" => $data['email'],
            "role" => $data['role'],
            "status" => isset($data['status']) ? $data['status'] : 'active',
            "foto_profile" => $data['foto_profile'],
            "poin" => isset($data['poin']) ? (int) $data['poin'] : 0
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Email atau password salah"
    ]);
}
?>
