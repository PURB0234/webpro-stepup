<?php

header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

if(!$conn){

    echo json_encode([
        "success" => false,
        "message" => "Koneksi gagal"
    ]);

    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID postingan tidak valid"
    ]);
    exit;
}

// CEK PEMILIK POST
$cek = mysqli_query($conn,
    "SELECT user_id FROM community WHERE id = $id"
);
$post = mysqli_fetch_assoc($cek);

if (!$post) {
    echo json_encode([
        "success" => false,
        "message" => "Postingan tidak ditemukan"
    ]);
    exit;
}

if ($post['user_id'] != $user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Anda tidak memiliki izin untuk menghapus postingan ini"
    ]);
    exit;
}

// AMBIL GAMBAR DULU

$get = mysqli_query($conn,
    "SELECT gambar FROM community
     WHERE id='$id'"
);

$data = mysqli_fetch_assoc($get);

if($data){

    $gambar = $data['gambar'];

    $path = __DIR__ . "/../../uploads/" . $gambar;

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