<?php
session_start();

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

// Proteksi admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/dashboard.php");
    exit();
}

// Ambil ID
$id = $_POST['id_reward'] ?? $_GET['id'];

// Ambil data gambar dulu
$getData = mysqli_query($conn, "SELECT gambar FROM rewards WHERE id_reward = '$id'");
$data = mysqli_fetch_assoc($getData);

// Hapus file gambar dari folder uploads
if ($data && file_exists("../../uploads/" . $data['gambar'])) {
    unlink("../../uploads/" . $data['gambar']);
}

// Delete dari database
$query = "DELETE FROM rewards WHERE id_reward = '$id'";

if (mysqli_query($conn, $query)) {

    header("Location: ../../admin/kelola_reward.php?delete=success");
    exit();

} else {

    echo "Gagal menghapus reward";

}
?>