<?php
session_start();
require_once "../services/koneksi.php";
/** @var mysqli $conn */

// Proteksi admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../pages/dashboard.php");
    exit();
}

// Ambil data form
$name_reward = $_POST['name_reward'];
$poin = $_POST['poin'];
$description = $_POST['description'];

// Ambil gambar
$gambar = $_FILES['gambar'];

// Validasi
if (empty($name_reward) || empty($poin) || empty($description)) {
    echo "Data tidak boleh kosong!";
    exit();
}

// Folder upload
$folder = "../uploads/";

// Ambil nama file
$namaFile = $gambar['name'];
$tmpFile = $gambar['tmp_name'];

// Biar nama unik
$namaBaru = time() . "-" . $namaFile;

// Upload file
move_uploaded_file($tmpFile, $folder . $namaBaru);

// Insert database
$query = "INSERT INTO rewards (name_reward, description, poin, gambar)
          VALUES ('$name_reward', '$description', '$poin', '$namaBaru')";

if (mysqli_query($conn, $query)) {

    header("Location: ../pages/reward_page.php?success=1");
    exit();

} else {

    echo "Gagal menambahkan reward";

}
?>