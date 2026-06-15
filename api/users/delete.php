<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

// Hanya jalankan delete jika ada parameter action=delete
if (isset($_GET['action']) && $_GET['action'] === 'delete') {

    // Cek role admin
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../pages/dashboard.php");
        exit();
    }

    // Ambil id dari URL
    $id = $_GET['id'] ?? '';

    // Validasi id
    if (!empty($id)) {

        // Query delete
        $query = "DELETE FROM users WHERE id = ?";

        // Prepare statement
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {

                header("Location: ../admin/users_data.php?success=delete");
                exit();

            } else {

                echo "Gagal menghapus user.";
            }

            mysqli_stmt_close($stmt);

        } else {

            echo "Query error.";
        }

    } else {

        echo "ID user tidak valid.";
    }
}
?>