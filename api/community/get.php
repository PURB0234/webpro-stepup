<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$query = mysqli_query($conn,
    "SELECT community.*, users.nama AS nama_user, users.foto_profile AS foto_user
     FROM community
     LEFT JOIN users ON community.user_id = users.id
     ORDER BY community.id DESC"
);

$data = [];

while($row = mysqli_fetch_assoc($query)){

    $data[] = $row;

}

echo json_encode($data);