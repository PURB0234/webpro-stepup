<?php
session_start();

require_once "../services/koneksi.php";
/** @var mysqli $conn */

$nama = $_POST['nama'];
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama','$email', '$hashedPassword', 'user')";

if(mysqli_query($conn, $query)){

    $_SESSION['user_id'] = mysqli_insert_id($conn);
    $_SESSION['nama'] = $nama;
    $_SESSION['email'] = $email;
    $_SESSION['password'] = $hashedPassword;
    $_SESSION['role'] = 'user';
    $_SESSION['foto_profile'] = null;

    header("Location: ../pages/dashboard.php");
    exit();

}else{
    echo "Register gagal";
}

?>