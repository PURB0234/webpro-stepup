<?php
session_start();
require_once "../services/koneksi.php";
/** @var mysqli $conn */

$email = $_POST['email'];

$result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
$data = mysqli_fetch_assoc($result);

if ($data) {
    $_SESSION['nama'] = $data['nama'];
    $_SESSION['email'] = $data['email'];
    $_SESSION['role'] = $data['role']; // <-- INI DI SINI

    header("Location: ../pages/dashboard.php");
    exit();
} else {
    echo "Login gagal";
}
?>