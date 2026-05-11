<?php
session_start();

require_once "../services/koneksi.php";

$nama = $_POST['nama'];
$email = $_POST['email'];
$nim = $_POST['nim'];
$role = $_POST['role'];

$query = "INSERT INTO users (nama, email, nim, role) VALUES ('$nama','$email', '$nim', 'user')";

if(mysqli_query($conn,$query)){

    $_SESSION['nama'] = $nama;
    $_SESSION['email'] = $email;
    $_SESSION['nim'] = $nim;

    header("Location: ../pages/dashboard.php");
    exit();

}else{
    echo "Register gagal";
}

?>