<?php
session_start();

require_once "../services/koneksi.php";
/** @var mysqli $conn */

$nama = $_POST['nama'];
$email = $_POST['email'];
$password = $_POST['password'];
$nim = $_POST['nim'];
$role = $_POST['role'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO users (nama, email, password, nim, role) VALUES ('$nama','$email', '$hashedPassword', '$nim', 'user')";

if(mysqli_query($conn, $query)){

    $_SESSION['nama'] = $nama;
    $_SESSION['email'] = $email;
    $_SESSION['nim'] = $nim;
    $_SESSION['password'] = $hashedPassword;
    $_SESSION['role'] = 'user';

    header("Location: ../pages/dashboard.php");
    exit();

}else{
    echo "Register gagal";
}

?>