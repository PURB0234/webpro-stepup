<?php
require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

$users = [];

while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>