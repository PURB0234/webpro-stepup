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

<form action="../rewards/create.php" method="POST">
    
    <label>Nama Reward:</label><br>
    <input type="text" name="name_reward" required><br><br>

    <label>Poin:</label><br>
    <input type="number" name="poin" required><br><br>

    <button type="submit">Tambah</button>

</form>

</body>
</html>