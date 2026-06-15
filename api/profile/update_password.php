<?php

header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Anda harus login terlebih dahulu"
    ]);
    exit;
}

$password_lama = isset($_POST['password_lama']) ? $_POST['password_lama'] : '';
$password_baru = isset($_POST['password_baru']) ? $_POST['password_baru'] : '';
$konfirmasi = isset($_POST['konfirmasi_password']) ? $_POST['konfirmasi_password'] : '';

// Validasi
if (empty($password_lama) || empty($password_baru) || empty($konfirmasi)) {
    echo json_encode([
        "success" => false,
        "message" => "Semua field harus diisi"
    ]);
    exit;
}

if (strlen($password_baru) < 6) {
    echo json_encode([
        "success" => false,
        "message" => "Password baru minimal 6 karakter"
    ]);
    exit;
}

if ($password_baru !== $konfirmasi) {
    echo json_encode([
        "success" => false,
        "message" => "Konfirmasi password tidak cocok"
    ]);
    exit;
}

// Ambil password lama dari database
$query = mysqli_query($conn, "SELECT password FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($query);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "User tidak ditemukan"
    ]);
    exit;
}

// Verifikasi password lama
if (!password_verify($password_lama, $user['password'])) {
    echo json_encode([
        "success" => false,
        "message" => "Password lama salah"
    ]);
    exit;
}

// Hash password baru
$hashedPassword = password_hash($password_baru, PASSWORD_DEFAULT);
$hashedEscaped = mysqli_real_escape_string($conn, $hashedPassword);

$update = mysqli_query($conn,
    "UPDATE users SET password = '$hashedEscaped' WHERE id = $user_id"
);

if ($update) {
    $_SESSION['password'] = $hashedPassword;

    echo json_encode([
        "success" => true,
        "message" => "Password berhasil diubah"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengubah password: " . mysqli_error($conn)
    ]);
}
