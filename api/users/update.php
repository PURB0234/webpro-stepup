<?php
require_once __DIR__ . "/../../services/koneksi.php";
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
    $id = intval($_POST['id']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $query_update = "UPDATE users SET status = '$status', role = '$role' WHERE id = $id";

    if (mysqli_query($conn, $query_update)) {
        header("Location: ../../admin/users_data.php?success=update");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
