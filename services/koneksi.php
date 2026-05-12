<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "db_stepup";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// mysqli_close($conn);

?>