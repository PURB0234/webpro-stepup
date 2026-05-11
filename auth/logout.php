<?php
session_start();

// Hapus semua session
session_unset();
session_destroy();

// Arahkan ke halaman login / index
header("Location: ../index.php?logout=success");
exit();
?>