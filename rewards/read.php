<?php
require "../services/koneksi.php";

$query = "SELECT * FROM rewards";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

$rewards = [];

while ($row = mysqli_fetch_assoc($result)) {
    $rewards[] = $row;
}
?>