<?php

header("Content-Type: application/json");

// $conn = mysqli_connect(
//     "127.0.0.1",
//     "root",
//     "",
//     "db_stepup"
// );
require_once "../services/koneksi.php";
/** @var mysqli $conn */

$query = mysqli_query($conn,
    "SELECT * FROM community ORDER BY id DESC"
);

$data = [];

while($row = mysqli_fetch_assoc($query)){

    $data[] = $row;

}

echo json_encode($data);