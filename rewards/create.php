<?php
session_start();
include "../services/koneksi.php";

// Proteksi (biar gak bisa diakses sembarang)
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../pages/dashboard.php");
    exit();
}

// Ambil data dari form
$name_reward = $_POST['name_reward'];
$poin = $_POST['poin'];

// Validasi sederhana
if (empty($name_reward) || empty($poin)) {
    echo "Data tidak boleh kosong!";
    exit();
}

// Query insert
$query = "INSERT INTO rewards (name_reward, poin) VALUES ('$name_reward', '$poin')";

if (mysqli_query($conn, $query)) {
    header("Location: ../pages/reward_page.php?success=1");
    exit();
} else {
    echo "Gagal menambahkan reward";
}
?>