<?php
require_once "../services/koneksi.php";
/** @var mysqli $conn */

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM users WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        die("User tidak ditemukan.");
    }
}
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    $query_update = "UPDATE users SET status = '$status' WHERE id = $id";

    if (mysqli_query($conn, $query_update)) {
        echo "<script>
                alert('Status user berhasil diperbarui!');
                window.location.href = '../admin/users_data.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
