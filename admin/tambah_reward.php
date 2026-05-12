<?php
session_start();

// Proteksi admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../pages/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Reward</title>
</head>

<body>

    <h2>Tambah Reward</h2>

    <form action="../rewards/create.php" method="POST" enctype="multipart/form-data">

        <label>Nama Reward:</label><br>
        <input type="text" name="name_reward" required><br><br>

        <label>Poin:</label><br>
        <input type="number" name="poin" required><br><br>
        
        <label>Description:</label><br>
        <textarea name="description" id="" required></textarea><br><br>

        <label>Image:</label><br>
        <input type="file" name="gambar"><br><br>

        <button type="submit">Tambah</button>

    </form>

</body>

</html>