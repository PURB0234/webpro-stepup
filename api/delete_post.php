<?php

header("Content-Type: application/json");

require_once "../services/koneksi.php";
/** @var mysqli $conn */

if(!$conn){

    echo json_encode([
        "success" => false,
        "message" => "Koneksi gagal"
    ]);

    exit;
}

$id = $_POST['id'];

// AMBIL GAMBAR DULU

$get = mysqli_query($conn,
    "SELECT gambar FROM community
     WHERE id='$id'"
);

$data = mysqli_fetch_assoc($get);

if($data){

    $gambar = $data['gambar'];

    $path = "../uploads/" . $gambar;

    if(file_exists($path)){

        unlink($path);

    }

}


// DELETE DATABASE

$query = mysqli_query($conn,
    "DELETE FROM community
     WHERE id='$id'"
);

if($query){

    echo json_encode([
        "success" => true,
        "message" => "Postingan berhasil dihapus"
    ]);

}else{

    echo json_encode([
        "success" => false,
        "message" => mysqli_error($conn)
    ]);

}